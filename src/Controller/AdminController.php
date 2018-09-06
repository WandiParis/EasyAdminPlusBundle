<?php

namespace Lle\EasyAdminPlusBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Lle\EasyAdminPlusBundle\Exporter\Event\EasyAdminPlusExporterEvents;
use Lle\EasyAdminPlusBundle\Translator\Event\EasyAdminPlusTranslatorEvents;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminController extends BaseAdminController
{
    /**
     * {@inheritdoc}
     */
    protected function redirectToBackendHomepage()
    {
        $homepageConfig = $this->config['homepage'];

        // when Javier will merge #2151 (https://github.com/EasyCorp/EasyAdminBundle/pull/2151)
        // it'll be ok and redirect on the correct action instead of raw "list"

        // if the first entity have a higher role, take the first one which matchs
        /*if (!$this->get('lle.easy_admin_plus.acl.security.admin_authorization_checker')->isEasyAdminGranted($this->config['entities'][$homepageConfig['params']['entity']], 'list')) {
            foreach ($this->config['entities'] as $entityName => $entityInfo) {
                if ($this->get('lle.easy_admin_plus.acl.security.admin_authorization_checker')->isEasyAdminGranted($entityInfo, 'list') &&
                    !in_array('list', $entityInfo['disabled_actions'])) {
                    $this->config['homepage']['params']['entity'] = $entityName;
                    break;
                }
            }
        }*/

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

        $parameters = array(
            'paginator' => $paginator,
            'fields' => $fields,
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
        );

        return $this->executeDynamicMethod('render<EntityName>Template', array('list', $this->entity['templates']['list'], $parameters));
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
    protected function findFiltered($entity, $entityClass, $page = 1, $maxPerPage = 15, $sortField = null, $sortDirection = null, $dqlFilter = null)
    {
        if (empty($sortDirection) || !in_array(strtoupper($sortDirection), array('ASC', 'DESC'))) {
            $sortDirection = 'DESC';
        }

        $queryBuilder = $this->executeDynamicMethod('create<EntityName>ListQueryBuilder', array($entityClass, $sortDirection, $sortField, $dqlFilter));

        if ($entity['tree'] ?? false) {
            $queryBuilder->orderBy($queryBuilder->getRootAlias().'.root');
            $queryBuilder->addOrderBy($queryBuilder->getRootAlias().'.lft');
        }

        if (isset($entity['filter'])) {
            foreach ($entity['filter']['fields'] as $filterType) {
                $ftype = $filterType['filtertype'];
                $ftype->setQueryBuilder($queryBuilder);
                $ftype->setRequest($this->request);
                $ftype->setEm($this->em);

                $data = [];
                if ($ftype->bindRequest($data, str_replace('.', '_', $filterType['property']))) {
                    $ftype->setData($data);
                    $donnes = $ftype->init();
                    $ftype->apply($data, str_replace('.', '_',$filterType['property']), $donnes['alias'], $donnes['column']);
                }
            }
        }
        $this->dispatch(EasyAdminEvents::POST_LIST_QUERY_BUILDER, array(
            'query_builder' => $queryBuilder,
            'sort_field' => $sortField,
            'sort_direction' => $sortDirection,
        ));
        $page = ($this->request->request->has('filter'))? 1:$page;
        return $this->get('easyadmin.paginator')->createOrmPaginator($queryBuilder, $page, $maxPerPage);
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
        return $this->get('lle.easy_admin_plus.query_builder')->createListQueryBuilder($this->entity, $sortField, $sortDirection, $dqlFilter);
    }

    public function embeddedListAction($request, $entity, $items)
    {
        $this->initialize($request);
        $this->entity = $this->get('easyadmin.config.manager')->getEntityConfiguration($entity);

        $fields = $this->entity['list']['fields'];
        return $this->render('@LleEasyAdminPlus/default/embedded_list.html.twig', array(
            'fields'=>$fields,
            'items'=>$items,
            'entity'=>$entity
        ));
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
                foreach($log->getData() as $k => $entry){
                    if($entry instanceof \DateTime){
                        $retour = $entry->format('d/m/Y H:m');
                    }else if(is_object($entry)){
                        $retour = (method_exists($entry,'__toString'))? $entry->toString():$entry->getId();
                    }else if(is_array($entry)){
                        $retour = implode('-',$entry);
                    }else{
                        $retour = $entry;
                    }
                    $data[$k] = $retour;
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
            print $filter->getName();
            $this->em->getFilters()->disable($filter->getName());
        }
        die();
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

        return $this->render('@LleEasyAdminPlus/Admin/translations.html.twig', [
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

        $batchs = $this->entity['list']['batchs'];
        
        if(array_key_exists($name, $batchs) && $ids) {
            $service = $this->get($batchs[$name]['service']);

            $service->execute($this->request, $this->entity, $ids);
            
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
}
