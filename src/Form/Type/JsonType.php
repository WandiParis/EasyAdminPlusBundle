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
                "type" => "object",
                "properties" => [
                    "name" => ["type"=>"string"]
                ]
            ],
            "disableEditJson" => false,
            "noAdditionalProperties" => false
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options){
        $view->vars['schema'] = json_encode($options['schema']);
        $view->vars['disableEditJson'] = ($options['disableEditJson'])? 'true':'false';
        $view->vars['noAdditionalProperties'] = ($options['noAdditionalProperties'])? 'true':'false';
    }


}