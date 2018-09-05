<?php
namespace Lle\EasyAdminPlusBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JsonType extends AbstractType {


    public function getParent()
    {
        return TextareaType::class;
    }

    public function getName()
    {
        return 'lle.easyadminplus.json';
    }

    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults([
            "schema"=> [
                "type" => "object"
            ],
            "disable_edit_json" => true,
            "no_additional_properties" => false,
            "display_required_only" => false,
            "required_by_default" => true,
            "theme" => "bootstrap2",
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options){
        $view->vars['schema'] = json_encode($options['schema']);
        $view->vars['disable_edit_json'] = ($options['disable_edit_json'])? 'true':'false';
        $view->vars['no_additional_properties'] = ($options['no_additional_properties'])? 'true':'false';
        $view->vars['required_by_default'] = ($options['required_by_default'])? 'true':'false';
        $view->vars['display_required_only'] = ($options['display_required_only'])? 'true':'false';
        $view->vars['theme']= ($options['theme'])? $options['theme']:'bootstrap2';
    }


}
