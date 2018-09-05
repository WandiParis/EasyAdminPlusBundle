<?php
namespace Lle\EasyAdminPlusBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractDependantJsonType extends AbstractType {


    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault("json_field",null);
        $resolver->setRequired('json_schema_route');
    }


    public function buildView(FormView $view, FormInterface $form, array $options){
        parent::buildView($view, $form, $options);
        $view->vars['json_field'] = str_replace($view->vars['name'], $options['json_field'], $view->vars['id']);
        $view->vars['json_schema_route'] = $options['json_schema_route'];
    }

    public function getBlockPrefix(){
        return 'json_choice';
    }

}