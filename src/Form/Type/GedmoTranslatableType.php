<?php
/**
 *  This file is part of the Lego project.
 *
 *   (c) Joris Saenger <joris.saenger@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */
namespace Lle\EasyAdminPlusBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\FormType as ParentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Lle\EasyAdminPlusBundle\Service\GedmoTranslatableFieldManager;


class GedmoTranslatableType extends AbstractType
{
    protected $translatablefieldmanager;
    private $locales;
    private $defaultLocale;
    private $currentLocale;


    //the 2eme argument is best if $locales
    public function __construct($defaultLocale, $locales, GedmoTranslatableFieldManager $translatableFieldManager, TranslatorInterface $translator)
    {
        $this->defaultLocale = $defaultLocale;
        $this->locales = (\count($locales) <= 1)? ['fr','en','de']:$locales;
        $this->translatablefieldmanager = $translatableFieldManager;
        $this->currentLocale = $translator->getLocale();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fieldName = $builder->getName();
        $locales = $this->locales;
        $defaultLocale = $this->defaultLocale;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($fieldName, $locales, $options) {
            $form = $event->getForm();
            foreach ($locales as $locale) {
                $form->add($locale, $options['fields_class'], ['label' => false]);
            }
        });
        // submit
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($fieldName, $locales, $defaultLocale) {
            $form = $event->getForm();
            $this->translatablefieldmanager->persistTranslations($form, $locales, $defaultLocale);
        });
    }
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $translatedFieldValues = $this->translatablefieldmanager->getTranslatedFields($form->getParent()->getData(), $form->getName(), $this->defaultLocale);
        // set form field data (translations)
        foreach ($this->locales as $locale) {
            if (!isset($translatedFieldValues[$locale])) {
                continue;
            }
            $form->get($locale)->setData($translatedFieldValues[$locale]);
        }
        // template vars
        $view->vars['locales'] = $this->locales;
        $view->vars['currentlocale'] = $this->currentLocale;
        $view->vars['tablabels'] = $this->getTabLabels();
    }

    public function getParent()
    {
        return ParentType::class;
    }

    public function getName()
    {
        return 'lego_gedmo_translatable';
    }

    public function getBlockPrefix()
    {
        return 'lego_gedmo_translatable';
    }

    private function getTabLabels()
    {
        $tabLabels = array();
        foreach ($this->locales as $locale) {
            $tabLabels[$locale] = ucfirst(\Locale::getDisplayLanguage($locale, $this->currentLocale));
        }
        return $tabLabels;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound' => true,
            'mapped' => false,
            'required' => false,
            'by_reference' => false,
            'fields_class' => TextType::class
        ]);
    }
}