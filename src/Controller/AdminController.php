<?php

namespace Wandi\EasyAdminPlusBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends BaseAdminController
{
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
     * Manage translations
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function translationsAction(Request $request)
    {
        $translator = $this->get('wandi.easy_admin_plus.translator');
        $domain = $request->request->get('domain') ?? $request->query->get('domain');
        $locale = $this->container->getParameter('locale') ?? $this->container->getParameter('kernel.default_locale');

        // submit
        if ($request->request->get('submit') == "save"){

            // save files
            $nbWrittenFiles = $translator->writeDictionaries($request->request->get('dictionaries') ?? [], $locale);

            // put flash
            $this->addFlash('success', $this->get('translator')->transChoice('translator.flash.success', $nbWrittenFiles, ['%nbFiles%' => $nbWrittenFiles], 'EasyAdminPlusBundle'));

            // clear cache
            $translator->clearTranslationsCache();

            // forward on GET
            $this->redirectToRoute("wandi_easy_admin_plus_translations", ['domain' => $domain]);
        }

        // get locales
        $locales = $translator->getLocales();
        if (empty($locales)){
            throw new \Exception("No locale to manage.");
        }

        // get files
        $files = $translator->getFiles();
        if (empty($files)){
            throw new \Exception("No translation files found.");
        }

        // get all translations in files
        $translations = $translator->getTranslations($files);

        // extract different domains & choose the domain to manage
        $domains = array_keys($translations);
        $domain = ($domain == null && !empty($domains)) ? $domains[0] : $domain;
        if (!$domain){
            throw new \Exception("No domain found.");
        }

        // prepare translations (add missing files in other locale and clone missing translation keys)
        $dictionaries = [];
        $translations = $translator->prepareTranslations($translations, $dictionaries);

        // format dictionaries for front-end
        $dictionaries = $translator->formatDictionaries($translations, $dictionaries);

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
