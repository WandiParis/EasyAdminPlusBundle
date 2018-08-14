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

//use Symfony\Component\Workflow\Registry;

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
                $this->get('lle.easy_admin_plus.acl.security.admin_authorization_checker')->isEasyAdminGranted($this->entity, $actionName);
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


    protected function embeddedListAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_LIST);

        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->config['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'));

        $this->dispatch(EasyAdminEvents::POST_LIST, array('paginator' => $paginator));

        return $this->render('@EasyAdminExtension/default/embedded_list.html.twig', array(
            'paginator' => $paginator,
            'fields' => $fields,
            'masterRequest' => $this->get('request_stack')->getMasterRequest(),
        ));
    }

    /*    public function workflowAction( Registry $workflows)
        {
            $id = $this->request->query->get('id');
            $easyadmin = $this->request->attributes->get('easyadmin');
            $entity = $easyadmin['item'];

            $work = $this->request->query->get('work');
            $workflows = $this->get('workflow.registry');
            $workflow = $workflows->get($entity);

            try {
                $workflow->apply($entity, $work);
                $em->flush();

            } catch (LogicException $exception) {
            }

            //return $this->redirectToReferrer();
        }
    */

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
        
        if(array_key_exists($name, $batchs)) {
            $service = $this->get($batchs[$name]['service']);

            $service->execute($this->entity, $ids);
            
        }

        return $this->redirectToReferrer();

    }
}
