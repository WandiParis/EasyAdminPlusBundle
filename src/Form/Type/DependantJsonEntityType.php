<?php
namespace Lle\EasyAdminPlusBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class DependantJsonEntityType extends AbstractDependantJsonType {



    public function getParent()
    {
        return EntityType::class;
    }

    public function getName()
    {
        return 'lle.easyadminplus.dependant_json_entity';
    }
}