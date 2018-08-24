<?php

namespace Lle\EasyAdminPlusBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class UrlAutocompleteType extends AbstractType
{
    public function getParent()
    {
        return TextType::class;
    }

    public function getName()
    {
        return 'lle.easyadminplus.url_autocomplete';
    }

    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults([
            "url" => "#",
            "value_filter" => null,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options){
        $view->vars['url'] = $options['url'];
        $view->vars['value_filter'] = $options['value_filter'];
    }    
}
