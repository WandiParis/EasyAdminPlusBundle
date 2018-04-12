<?php

namespace Wandi\EasyAdminPlusBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use EasyCorp\Bundle\EasyAdminBundle\Search\Paginator;
use EasyCorp\Bundle\EasyAdminBundle\Twig\EasyAdminTwigExtension;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Wandi\EasyAdminPlusBundle\Exporter\Event\EasyAdminPlusExporterEvents;
use Wandi\EasyAdminPlusBundle\Translator\Event\EasyAdminPlusTranslatorEvents;

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
        if (!$this->get('wandi.easy_admin_plus.acl.security.admin_authorization_checker')->isEasyAdminGranted($this->config['entities'][$homepageConfig['params']['entity']], 'list')) {
            foreach ($this->config['entities'] as $entityName => $entityInfo) {
                if ($this->get('wandi.easy_admin_plus.acl.security.admin_authorization_checker')->isEasyAdminGranted($entityInfo, 'list') &&
                    !in_array('list', $entityInfo['disabled_actions'])) {
                    $this->config['homepage']['params']['entity'] = $entityName;
                    break;
                }
            }
        }

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
                $this->get('wandi.easy_admin_plus.acl.security.admin_authorization_checker')->isEasyAdminGranted($this->entity, $actionName);
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
        $this->config = $this->get('wandi.easy_admin_plus.exporter.configuration.normalizer_config_pass')->process($this->config);
        $this->config = $this->get('wandi.easy_admin_plus.exporter.configuration.property_config_pass')->process($this->config);
        $this->config = $this->get('wandi.easy_admin_plus.exporter.configuration.template_config_pass')->process($this->config);

        // get paginator from search
        $this->dispatch(EasyAdminEvents::PRE_LIST);
        $searchableFields = $this->entity['search']['fields'];
        $paginator = $this->findBy($this->entity['class'],
            $this->request->query->get('query'), $searchableFields, 1, PHP_INT_MAX,
            $this->request->query->get('sortField'),
            $this->request->query->get('sortDirection'),
            $this->entity['search']['dql_filter']);
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

        return $this->getExportFile($paginator, $this->config['entities'][$entityName]['export']['fields']);
    }

    /**
     * Format CSV file
     *
     * @param Paginator $paginator recordsets to export
     * @param array $fields fields to display
     * @return Response
     */
    public function getExportFile($paginator, $fields)
    {
        $handle = fopen('php://memory', 'r+');

        // first legend line
        $keys = array_keys($fields);
        for($i=0, $count=count($keys) ; $i<$count ; $i++){
            $keys[$i] = $fields[$keys[$i]]['label'] ?? $keys[$i];
        }
        fputcsv($handle, $keys, ';', '"');

        $twig = $this->get('twig');
        $ea_twig = $twig->getExtension(EasyAdminTwigExtension::class);

        foreach ($paginator as $entity) {
            $row = [];
            foreach ($fields as $field) {
                $value = $ea_twig->renderEntityField($twig, 'list', $this->entity['name'], $entity, $field);
                $row[] = trim($value);
            }
            fputcsv($handle, $row, ';', '"');
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return new Response("\xEF\xBB\xBF".$content, 200, array(
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="' . sprintf('export-%s-%s.csv', strtolower($this->entity['name']), date('Ymd_His')) . '"'
        ));
    }

    /**
     * Login action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            '@WandiEasyAdminPlus/Admin/login.html.twig',
            [
                'error' => $error,
                'lastUsername' => $lastUsername,
                'config' => $this->getParameter('easyadmin.config'),
            ]
        );
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
        $translator = $this->get('wandi.easy_admin_plus.translator');
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
            foreach(array_keys($request->request->get('dictionaries')[$domain]) as $fileName){
                if (!preg_match('/^(.*)\/([^\.]+)\.([^\.]+)$/', $fileName, $match)){
                    continue;
                }
                foreach($locales as $locale){
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
            $this->redirectToRoute('wandi_easy_admin_plus_translations', ['domain' => $domain]);
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

        return $this->render('@WandiEasyAdminPlus/Admin/translations.html.twig', [
                'domains' => $domains,
                'domain' => $domain,
                'dictionaries' => $dictionaries,
                'locales' => $locales,
                'locale' => $locale,
                'config' => $this->getParameter('easyadmin.config'),
            ]
        );
    }
}
