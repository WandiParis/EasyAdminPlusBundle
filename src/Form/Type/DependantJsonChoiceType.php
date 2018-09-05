<?php
namespace Lle\EasyAdminPlusBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DependantJsonChoiceType extends AbstractDependantJsonType {

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function getName()
    {
        return 'lle.easyadminplus.dependant_json_choice';
    }

}