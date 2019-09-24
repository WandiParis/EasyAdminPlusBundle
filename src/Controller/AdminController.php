<?php

namespace Lle\EasyAdminPlusBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Lle\EasyAdminPlusBundle\Exporter\Event\EasyAdminPlusExporterEvents;
use Lle\EasyAdminPlusBundle\Translator\Event\EasyAdminPlusTranslatorEvents;
use Lle\EasyAdminPlusBundle\Filter\FilterState;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;

class AdminController extends BaseAdminController
{

    /**
     * {@inheritdoc}
     */
    protected function redirectToBackendHomepage()
    {
        $homepageConfig = $this->config['homepage'];


        return parent::redirectToBackendHomepage();
    }

    /**
     * see PR #2150 (https://github.com/EasyCorp/EasyAdminBundle/pull/2150).
     *
     * {@inheritdoc}
     */
    protected function redirectToReferrer()
    {
        $refererUrl = $this->request->query->get('referer', '');
        $refererAction = $this->request->query->get('action');

        // 1. redirect to list if possible
        if ($this->isActionAllowed('list', true)) {
            if (!empty($refererUrl)) {
                return $this->redirect(urldecode($refererUrl));
            }

            return $this->redirectToRoute('easyadmin', [
                'action' => 'list',
                'entity' => $this->entity['name'],
                'menuIndex' => $this->request->query->get('menuIndex'),
                'submenuIndex' => $this->request->query->get('submenuIndex'),
            ]);
        }

        // 2. from new|edit action, redirect to edit if possible
        if (\in_array($refererAction, ['new', 'edit']) && $this->isActionAllowed('edit', true)) {
            return $this->redirectToRoute('easyadmin', [
                'action' => 'edit',
                'entity' => $this->entity['name'],
                'menuIndex' => $this->request->query->get('menuIndex'),
                'submenuIndex' => $this->request->query->get('submenuIndex'),
                'id' => ('new' === $refererAction)
                    ? PropertyAccess::createPropertyAccessor()->getValue($this->request->attributes->get('easyadmin')['item'], $this->entity['primary_key_field_name'])
                    : $this->request->query->get('id'),
            ]);
        }

        // 3. from new action, redirect to new if possible
        if ('new' === $refererAction && $this->isActionAllowed('new', true)) {
            return $this->redirectToRoute('easyadmin', [
                'action' => 'new',
                'entity' => $this->entity['name'],
                'menuIndex' => $this->request->query->get('menuIndex'),
                'submenuIndex' => $this->request->query->get('submenuIndex'),
            ]);
        }

        return $this->redirectToBackendHomepage();
    }

    /**
     * {@inheritdoc}
     *
     * @param bool $checkRole force to check role
     */
    protected function isActionAllowed($actionName, $checkRole = false)
    {
        if ($checkRole) {
            return false === in_array($actionName, $this->entity['disabled_actions'], true) &&
                $this->get('lle.easy_admin_plus.acl.security.admin_authorization_checker')->isEasyAdminGranted($this->entity, $actionName, null);
        }

        return parent::isActionAllowed($actionName);
    }


    /**
     * export action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportAction()
    {
        $entityName = $this->entity['name'];
        $user = $this->getUser();

        $this->dispatch(EasyAdminPlusExporterEvents::PRE_EXPORT, [
            'user' => [
                'username' => $user ? $user->getUsername() : null,
                'roles' => $user ? $user->getRoles() : [],
            ],
        ]);

        // no export configuration? > take all the entity fields
        if (!array_key_exists('export', $this->config['entities'][$entityName]) ||
            empty($this->config['entities'][$entityName]['export']) ||
            !array_key_exists('fields', $this->config['entities'][$entityName]['export']) ||
            empty($this->config['entities'][$entityName]['export']['fields'])) {
            $this->config['entities'][$entityName]['export']['fields'] = $this->config['entities'][$entityName]['properties'];
        }

        // property/normalize/template config pass on all export fields
        $this->config = $this->get('lle.easy_admin_plus.exporter.configuration.normalizer_config_pass')->process($this->config);
        $this->config = $this->get('lle.easy_admin_plus.exporter.configuration.property_config_pass')->process($this->config);
        $this->config = $this->get('lle.easy_admin_plus.exporter.configuration.template_config_pass')->process($this->config);

        $this->dispatch(EasyAdminEvents::PRE_LIST);
        $paginator = $this->findFiltered(
            $this->entity, $this->entity['class'],
            1,
            PHP_INT_MAX, $this->request->query->get('sortField'),
            $this->request->query->get('sortDirection'),
            $this->entity['list']['dql_filter']);

        $fields = $this->entity['list']['fields'];
        $this->dispatch(EasyAdminEvents::POST_LIST, [
            'fields' => $fields,
            'paginator' => $paginator,
        ]);

        $this->dispatch(EasyAdminPlusExporterEvents::POST_EXPORT, [
            'user' => [
                'username' => $user ? $user->getUsername() : null,
                'roles' => $user ? $user->getRoles() : [],
            ],
        ]);

        $exportManager = $this->get('lle.service.export_manager');
        $filename = sprintf('export-%s-%s', strtolower($this->entity['name']), date('Ymd_His'));
        return $exportManager->generateResponse($paginator, $this->config['entities'][$entityName]['export']['fields'], $filename, $this->request->get('format'));
    }


    /**
     * The method that is executed when the user performs a 'list' action on an entity.
     *
     * @return Response
     */
    protected function listAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_LIST);

        $fields = $this->entity['list']['fields'];
        $paginator = $this->findFiltered($this->entity, $this->entity['class'], $this->request->query->get('page', 1), $this->entity['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'), $this->entity['list']['dql_filter']);
        $this->dispatch(EasyAdminEvents::POST_LIST, array('paginator' => $paginator));

        // batch actions
        $form_index = 0;
        $batch_forms = [];
        if (array_key_exists('batchs', $this->entity['list'] )) {
            $formBuilder = $this->get('form.factory');

            foreach ($this->entity['list']['batchs'] as $i => $actionConfig) {
                // fields that don't define the 'property' name are special form design elements
                $actionName = isset($actionConfig['name']) ? $actionConfig['name'] : '_easyadmin_action_batch_'.$form_index;

                if(isset($actionConfig['form'])){
                    $form = $formBuilder->create($actionConfig['form']);
                    $form_view = $form->createView();
                    $batch_forms[$actionName] = $form_view;
                }
                ++$form_index;
            }
        }
        $filterState= $this->get('lle.easy_admin_plus.filter_state');
        $parameters = array(
            'paginator' => $paginator,
            'batch_forms' => $batch_forms,
            'fields' => $fields,
            'filters' => $this->filters($filterState->getFilters($this->entity['name'])),
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
        );

        return $this->executeDynamicMethod('render<EntityName>Template', array('list', $this->entity['templates']['list'], $parameters));
    }

    protected function filters($filters){
        return $filters;
    }

    /**
     * Performs a database query to get all the records related to the given
     * entity. It supports pagination and field sorting.
     *
     * @param string $entityConfig
     * @param string $entityClass
     * @param int $page
     * @param int $maxPerPage
     * @param string|null $sortField
     * @param string|null $sortDirection
     * @param string|null $dqlFilter
     *
     * @return Pagerfanta The paginated query results
     */
    protected function findFiltered($entity, $entityClass, $page = 1, $maxPerPage = 50, $sortField = null, $sortDirection = null, $dqlFilter = null)
    {
        if (empty($sortDirection) || !in_array(strtoupper($sortDirection), array('ASC', 'DESC'))) {
            $sortDirection = 'DESC';
        }

        $queryBuilder = $this->executeDynamicMethod('create<EntityName>ListQueryBuilder', array($entityClass, $sortDirection, $sortField, $dqlFilter));

        if ($entity['tree'] ?? false) {
            $queryBuilder->orderBy($queryBuilder->getRootAlias().'.root');
            $queryBuilder->addOrderBy($queryBuilder->getRootAlias().'.lft');
        }


        $this->dispatch(EasyAdminEvents::POST_LIST_QUERY_BUILDER, array(
            'query_builder' => $queryBuilder,
            'sort_field' => $sortField,
            'sort_direction' => $sortDirection,
        ));
        $page = ($this->request->request->has('filter'))? 1:$page;
        try {
            return $this->get('easyadmin.paginator')->createOrmPaginator($queryBuilder, $page, $maxPerPage);
        }catch(OutOfRangeCurrentPageException $e){
            return $this->get('easyadmin.paginator')->createOrmPaginator($queryBuilder, 1, $maxPerPage);
        }
    }


    /**
     * Performs a database query to get all the records related to the given
     * entity. It supports pagination and field sorting.
     *
     * @param string $entityConfig
     * @param string $entityClass
     * @param string|null $sortField
     * @param string|null $sortDirection
     * @param string|null $dqlFilter
     *
     * @return query results
     */
    protected function findSelection($entity, $entityClass, $sortField = null, $sortDirection = null, $dqlFilter = null)
    {

        $queryBuilder = $this->executeDynamicMethod('create<EntityName>ListQueryBuilder', array($entityClass, $sortDirection, $sortField, $dqlFilter));
        $queryBuilder->select($queryBuilder->getRootAlias().'.id');
        $result =  $queryBuilder->getQuery()->getResult();
        $ids = array_column($result, "id");
        return $ids;
    }


    /**
     * Creates Query Builder instance for all the records.
     *
     * @param string      $entityClass
     * @param string      $sortDirection
     * @param string|null $sortField
     * @param string|null $dqlFilter
     *
     * @return QueryBuilder The Query Builder instance
     */
    protected function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        return $this->get('lle.easy_admin_plus.query_builder')->createListQueryBuilder($this->entity, $sortField, $sortDirection, $dqlFilter, $this->request);
    }

    public function embeddedListAction($request, $entity, $items, $metadata)
    {
        $this->initialize($request);
        $this->master_entity = $this->entity;
        $vars = explode('\\',$this->master_entity['class']);
        $master_entity_class =  ucfirst(strtolower(end($vars)));
        $this->entity = $this->get('easyadmin.config.manager')->getEntityConfiguration($entity);

        // retrieve data with given query builder for given repository
        if (!$items && isset($metadata['qb']) && isset($metadata['repository_entity']) && $entity != '') {
            $repository = $this->em->getRepository($metadata['repository_entity']);

            //pass arguments array to method if it exist
            $itemsQb = call_user_func_array([$repository, $metadata['qb']], isset($metadata['qb_parameters']) ? $metadata['qb_parameters'] : []);
            $items = $itemsQb->getQuery()->execute();
        }

        $fields = $this->entity['list']['fields'];
        foreach($metadata['ignore_fields']??[] as $field_to_del) {
            unset($fields[$field_to_del]);
        }

        if ($metadata['with_add'] ?? false) {
            if ($metadata['add_form'] ?? false) {
                $classmetadata = $this->em->getClassMetadata($this->entity['class']);
                $instance = $classmetadata->newInstance();
                $parent = $this->em->getRepository($this->master_entity['class'])->find($request->query->get('id'));
                $associations = $classmetadata->getAssociationsByTargetClass($this->master_entity['class']);
                if(\count($associations) === 1){
                    $assoc = array_shift($associations);
                    $fieldName = $assoc['fieldName'];
                    if($classmetadata->isSingleValuedAssociation($fieldName)){
                        $method = $classmetadata->getReflectionClass()->getMethod('set' . $fieldName);
                        $method->invoke($instance, $parent);
                        $data = $instance;
                    }
                }
                $options = [
                    'action' => $this->generateUrl($metadata['add_route'], ['id' => $request->query->get('id')]),
                    'method' => 'POST',
                    'attr' => ['class'=>'form-inline']
                ];

                if(isset($metadata['add_form_options'])){
                    foreach($metadata['add_form_options'] as $k => $v){
                        if($v === '{parent}'){
                            $metadata['add_form_options'][$k] = $parent;
                        }
                    }
                    $options = array_merge($options, $metadata['add_form_options']);
                }
                $add_form = $this->createForm($metadata['add_form'], $data ?? null, $options)
                    ->createView();
            } else {
                $add_form = $this->createFormBuilder(null, array(
                    'action' => $this->generateUrl('lle_easy_admin_plus_add_sublist', [
                        'parent_id'=> $request->query->get('id') ,
                        'parent_entity'=> $this->master_entity['class'],
                        'entity'=>  $this->entity['class'] ]),
                    'method' => 'POST',
                ))
                    ->add('item_id', EasyAdminAutocompleteType::class, array(
                        'class' => $this->entity['class'],
                        'label' => false,
                        'attr' => [
                            'data-easyadmin-autocomplete-url'  => $this->generateUrl('easyadmin',
                                [ 'action' => 'autocomplete', 'entity'=> $entity ]
                            )
                        ]
                    ))
                    ->getForm()->createView();
            }
        } else {
            $add_form = null;
        }
        $referer = $this->generateUrl('easyadmin',
                                [ 'action' => 'show', 'entity'=> $master_entity_class, 'id' =>$request->query->get('id') ]
                            );
        return $this->render('@LleEasyAdminPlus/default/embedded_list.html.twig', array(
            'fields'=>$fields,
            'items'=>$items,
            'parent' => $this->em->getRepository($this->master_entity['class'])->find($request->query->get('id')),
            'main_id'=>$request->query->get('id'),
            'entity'=>$entity,
            'add_form'=>$add_form,
            'referer'=>$referer,
            'add_delete' => $metadata['with_delete'] ?? false,
            'delete_route' => $metadata['delete_route'] ?? false,
            'template_form' => $metadata['template_form'] ?? '@LleEasyAdminPlus/default/includes/_sub_form.html.twig'
        ));
    }

    /**
     * The method that is executed when the user performs a 'delete' action to
     * remove any entity.
     *
     * @return RedirectResponse
     */
    protected function embeddedDeleteAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_DELETE);
        /*if ('DELETE' !== $this->request->getMethod()) {
            return $this->redirect($this->generateUrl('easyadmin', array('action' => 'list', 'entity' => $this->entity['name'])));
        }*/
        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];
        try {
            $this->dispatch(EasyAdminEvents::PRE_REMOVE, array('entity' => $entity));
            $this->executeDynamicMethod('remove<EntityName>Entity', array($entity));
            $this->dispatch(EasyAdminEvents::POST_REMOVE, array('entity' => $entity));
        } catch (ForeignKeyConstraintViolationException $e) {
            throw new EntityRemoveException(array('entity_name' => $this->entity['name'], 'message' => $e->getMessage()));
        }
        if ($this->request->server->get('HTTP_REFERER')) {
            return new RedirectResponse($this->request->server->get('HTTP_REFERER'));
        } else {
            return new RedirectResponse('/');
        }
    }    


    public function historyAction(Request $request, $item)
    {
        $this->em = $this->getDoctrine()->getManager();

        $repo = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry'); // we use default log entry class
        $logs = $repo->getLogEntries($item);

        $result = [];

        foreach ($logs as $log) {
            $data = array();
            if ($log->getData()) {
                $metaData = $this->em->getClassMetadata(get_class($item));
                foreach($log->getData() as $k => $entry){
                    $type = $metaData->getTypeOfField($k);
                    $retour = $entry;
                    if($metaData->hasAssociation($k)){
                        if ($entry) {
                            $type = $metaData->isSingleValuedAssociation($k)? 'single_assoc':'multi_assoc';
                            $assoc = $metaData->getAssociationMapping($k);
                            $obj = $this->em->getRepository($assoc['targetEntity'])->find($entry);
                            if($obj) {
                                $id = $this->em->getClassMetadata($assoc['targetEntity'])->getIdentifierValues($obj);
                                $retour = (string) $obj; //(method_exists($obj, '__toString')) ? implode(',', $id) . ' ' . $obj->__toString() : $id;
                            } else {
                                $retour = "";
                            }
                        }
                    } else if($type === 'boolean'){
                        $retour = ($entry)? 'label.true':'label.false';
                    } else if($type === 'date'){
                        $retour = ($entry)? $entry->format('d/m/Y'):'';
                    } else if($type === 'datetime') {
                        $retour = ($entry)? $entry->format('d/m/Y H:i'):'';
                    } else if(is_array($entry)){
                        $retour = implode('-',$entry);
                    }
                    $data[$k] = ['value' => $retour, 'type' => $type, 'raw' => $entry];
                }
            }
            $result[] = array('log'=>$log,'data'=>$data);
        }
        return $this->render('@LleEasyAdminPlus/default/history.html.twig', array(
            'logs'=>$result
        ));
    }


    /**
     * The method that is executed when the user performs a 'new' action on an entity.
     *
     * @return Response|RedirectResponse
     */
    protected function newAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_NEW);


        $entity = $this->executeDynamicMethod('createNew<EntityName>Entity');

        $easyadmin = $this->request->attributes->get('easyadmin');
        $easyadmin['item'] = $entity;
        $this->request->attributes->set('easyadmin', $easyadmin);

        $fields = $this->entity['new']['fields'];
        $newForm = $this->executeDynamicMethod('create<EntityName>NewForm', array($entity, $fields));

        $newForm->handleRequest($this->request);
        if ($newForm->isSubmitted() && $newForm->isValid()) {
            // disable all filters for update cmd ( gedmo tree don't work if filter limit update sql)
            foreach($this->em->getFilters()->getEnabledFilters() as $filter => $obj) {
                $this->em->getFilters()->disable($filter);
            }

            $this->dispatch(EasyAdminEvents::PRE_PERSIST, array('entity' => $entity));

            $this->executeDynamicMethod('prePersist<EntityName>Entity', array($entity, true));
            $this->executeDynamicMethod('persist<EntityName>Entity', array($entity));

            $this->dispatch(EasyAdminEvents::POST_PERSIST, array('entity' => $entity));

            return $this->redirectToReferrer();
        }

        $this->dispatch(EasyAdminEvents::POST_NEW, array(
            'entity_fields' => $fields,
            'form' => $newForm,
            'entity' => $entity,
        ));

        $parameters = array(
            'form' => $newForm->createView(),
            'entity_fields' => $fields,
            'entity' => $entity,
        );

        return $this->executeDynamicMethod('render<EntityName>Template', array('new', $this->entity['templates']['new'], $parameters));
    }


    /**
     * The method that is executed when the user performs a 'edit' action on an entity.
     *
     * @return Response|RedirectResponse
     */
    protected function editAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_EDIT);


        // disable all filters for update cmd ( gedmo tree don't work if filter limit update sql)
        foreach($this->em->getFilters() as $filter) {
            $this->em->getFilters()->disable($filter->getName());
        }
        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];

        if ($this->request->isXmlHttpRequest() && $property = $this->request->query->get('property')) {
            $newValue = 'true' === mb_strtolower($this->request->query->get('newValue'));
            $fieldsMetadata = $this->entity['list']['fields'];

            if (!isset($fieldsMetadata[$property]) || 'toggle' !== $fieldsMetadata[$property]['dataType']) {
                throw new \RuntimeException(sprintf('The type of the "%s" property is not "toggle".', $property));
            }

            $this->updateEntityProperty($entity, $property, $newValue);

            // cast to integer instead of string to avoid sending empty responses for 'false'
            return new Response((int) $newValue);
        }

        $fields = $this->entity['edit']['fields'];

        $editForm = $this->executeDynamicMethod('create<EntityName>EditForm', array($entity, $fields));
        $deleteForm = $this->createDeleteForm($this->entity['name'], $id);

        $editForm->handleRequest($this->request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->dispatch(EasyAdminEvents::PRE_UPDATE, array('entity' => $entity));

            $this->executeDynamicMethod('preUpdate<EntityName>Entity', array($entity, true));
            $this->executeDynamicMethod('update<EntityName>Entity', array($entity));

            $this->dispatch(EasyAdminEvents::POST_UPDATE, array('entity' => $entity));

            return $this->redirectToReferrer();
        }

        $this->dispatch(EasyAdminEvents::POST_EDIT);

        $parameters = array(
            'form' => $editForm->createView(),
            'entity_fields' => $fields,
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );

        return $this->executeDynamicMethod('render<EntityName>Template', array('edit', $this->entity['templates']['edit'], $parameters));
    }



    /**
     * Manage translations.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function translationsAction(Request $request)
    {
        $translator = $this->get('lle.easy_admin_plus.translator');
        $domain = $request->request->get('domain') ?? $request->query->get('domain');
        $locale = $this->container->getParameter('locale') ?? $this->container->getParameter('kernel.default_locale');
        $user = $this->getUser();

        // submit
        if ('save' == $request->request->get('submit')) {
            // save files
            $nbWrittenFiles = $translator->writeDictionaries($request->request->get('dictionaries') ?? [], $locale);

            // put flash
            $this->addFlash('success', $this->get('translator')->transChoice('translator.flash.success', $nbWrittenFiles, ['%nbFiles%' => $nbWrittenFiles], 'EasyAdminPlusBundle'));

            // clear cache
            $translator->clearTranslationsCache();

            // dispatch event
            $fileNames = [];
            $locales = $translator->getLocales();
            foreach (array_keys($request->request->get('dictionaries')[$domain]) as $fileName) {
                if (!preg_match('/^(.*)\/([^\.]+)\.([^\.]+)$/', $fileName, $match)) {
                    continue;
                }
                foreach ($locales as $locale) {
                    $fileNames[] = $match[1] . '/' . $match[2] . '.' . $locale . '.' . $match[3];
                }
            }
            $this->get('event_dispatcher')->dispatch(EasyAdminPlusTranslatorEvents::POST_TRANSLATE,
                new GenericEvent($domain, [
                    'domain' => $domain,
                    'files' => $fileNames,
                    'user' => [
                        'username' => $user ? $user->getUsername() : null,
                        'roles' => $user ? $user->getRoles() : [],
                    ],
                ])
            );

            // forward on GET
            $this->redirectToRoute('lle_easy_admin_plus_translations', ['domain' => $domain]);
        }

        // get locales
        $locales = $translator->getLocales();
        if (empty($locales)) {
            throw new \Exception('No locale to manage.');
        }

        // get files
        $files = $translator->getFiles();
        if (empty($files)) {
            throw new \Exception('No translation files found.');
        }

        // get all translations in files
        $translations = $translator->getTranslations($files);

        // extract different domains & choose the domain to manage
        $domains = array_keys($translations);
        $domain = (null == $domain && !empty($domains)) ? $domains[0] : $domain;
        if (!$domain) {
            throw new \Exception('No domain found.');
        }

        // prepare translations (add missing files in other locale and clone missing translation keys)
        $dictionaries = [];
        $translations = $translator->prepareTranslations($translations, $dictionaries);

        // format dictionaries for front-end
        $dictionaries = $translator->formatDictionaries($translations, $dictionaries);

        // dispatch event
        $this->get('event_dispatcher')->dispatch(EasyAdminPlusTranslatorEvents::PRE_TRANSLATE,
            new GenericEvent($domain, [
                'domain' => $domain,
                'user' => [
                    'username' => $user ? $user->getUsername() : null,
                    'roles' => $user ? $user->getRoles() : [],
                ],
            ])
        );

        return $this->render('@LleEasyAdminPlus/admin/translations.html.twig', [
                'domains' => $domains,
                'domain' => $domain,
                'dictionaries' => $dictionaries,
                'locales' => $locales,
                'locale' => $locale,
                'config' => $this->getParameter('easyadmin.config'),
            ]
        );
    }


    /**
     * batch action.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function batchAction()
    {
        $name = $this->request->request->get('name') ?? $this->request->query->get('name');
        $ids = $this->request->request->get('ids') ?? $this->request->query->get('ids');
        $allSelection = $this->request->request->get('all-selection');
        if($allSelection) {
            $ids = $this->findSelection($this->entity, $this->entity['class'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'), $this->entity['list']['dql_filter']);
        }
        $batchs = $this->entity['list']['batchs'];
        $ret = null;
        if(array_key_exists($name, $batchs) && $ids) {
            $service = $this->get('lle.service.batch_manager')->getBatch($batchs[$name]['service']);
            $data = [];
            if($batchs[$name]['form']){
                $form = $this->createForm($batchs[$name]['form']);
                $form->handleRequest($this->request);
                if ($form->isSubmitted() && $form->isValid()) {
                    // data is an array with "name", "email", and "message" keys
                    $data = $form->getData();
                }
            }
            $ret = $service->execute($this->request, $this->entity, $ids, $data);
            $nb = \count($ids);
            if(method_exists($service, 'countSuccess') && $service->countSuccess() !== null){
                $countOk = $service->countSuccess();
            }else{
                $countOk = $nb;
            }
            $this->addFlash('success nt', $this->get('translator')->transChoice('flash.batch_success', $countOk, ['%action%' => $this->get('translator')->trans($batchs[$name]['label'] ?? $batchs[$name]['name']), '%nb%' => $nb], 'EasyAdminPlusBundle'));

        }
        if($ret) {
            return $ret;
        }
        return $this->redirectToReferrer();

    }

    /**
     * The method that returns the values displayed by an autocomplete field
     * based on the user's input.
     *
     * @return JsonResponse
     */
    protected function autocompleteAction()
    {
        $results = $this->get('lle.easy_admin_plus.autocomplete')->find(
            $this->request->query->get('entity'),
            $this->request->query->get('query') ?? '_', // %_% = anything
            $this->request->query->get('page', 1)
        );

        return new JsonResponse($results);
    }

    protected function eipAction(){
        /* @var \Lle\EasyAdminPlusBundle\Service\EditInPlaceFactory $eipFactory */
        $eipFactory = $this->get('lle.easy_admin_plus_edit_in_place.factory');
        $entity = $this->em->getRepository($this->entity['class'])->findOneById($this->request->request->get('id'));
        $field = $this->request->request->get('fieldName');
        $view = $this->request->request->get('view');
        if($this->entity[$view]['fields'][$field]['edit_in_place'] ?? false) {
            $method = 'set' . $field;
            $eipType = $eipFactory->getEditInPlaceType($this->request->request->get('type'));
            $value = $eipType->getValueFromRequest($this->request);
            $entity->$method($value);
            $this->dispatch(EasyAdminEvents::PRE_UPDATE, array('entity' => $entity));
            $this->em->persist($entity);
            $this->em->flush();
            $this->dispatch(EasyAdminEvents::POST_UPDATE, array('entity' => $entity));
            $template = $this->get('twig')->createTemplate('{{ easyadmin_render_field_for_list_view(name,item,metadata) }}');
            $html = $template->render(['item' => $entity, 'metadata' => $this->entity[$view]['fields'][$field], 'name' => $this->entity['name']]);
            return new JsonResponse(['code' => 'OK', 'html' => (string)html_entity_decode($html), 'val' => $eipType->formatValue($value)]);
        }
        return new JsonResponse(['code'=>'NOK', 'err'=> 'access denied']);
    }

    protected function eipEntityChoiceAction(){
        $request = $this->request;
        $field = $this->request->request->get('fieldName');
        $view = $this->request->request->get('view');
        if($this->entity[$view]['fields'][$field]['edit_in_place'] ?? false) {
            $entitySource = str_replace('/', '\\', $request->get('entity_source'));
            $entitySourceId = $request->get('entity_source_id');
            $entityTarget = str_replace('/', '\\', $request->get('entity_target'));
            $item = $this->em->getRepository($entitySource)->find($entitySourceId);
            $list = $this->em->getRepository($entityTarget)->findAll();
            $return = array();
            if ($list) {
                foreach ($list as $entity) {
                    $return[$entity->getId()] = (string)$entity;
                }
            }
            return new JsonResponse($return);
        }else{
            return new JsonResponse([]);
        }
    }
}
