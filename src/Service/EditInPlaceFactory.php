<?php
/**
 *  This file is part of the Lego project.
 *
 *   (c) Joris Saenger <joris.saenger@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Lle\EasyAdminPlusBundle\Service;


use Lle\EasyAdminPlusBundle\EditInPlaceType\DateEipType;
use Lle\EasyAdminPlusBundle\EditInPlaceType\DateTimeEipType;
use Lle\EasyAdminPlusBundle\EditInPlaceType\EipTypeInterface;
use Lle\EasyAdminPlusBundle\EditInPlaceType\EntityEipType;
use Lle\EasyAdminPlusBundle\EditInPlaceType\StringEipType;
use Lle\EasyAdminPlusBundle\EditInPlaceType\TimeEipType;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

class EditInPlaceFactory
{

    private $types = [];

    public function __construct(iterable $eipTypes)
    {
        foreach($eipTypes as $eipType){
            if($eipType instanceof EipTypeInterface) {
                if (array_key_exists($eipType->getType(), $this->types)) {
                    throw new \Exception('The type edit in place ' . $eipType->getType() . ' already exist');
                }
                $this->types[$eipType->getType()] = $eipType;
                $this->types[get_class($eipType)] = $eipType;
            }
        }
    }

    public function getEditInPlaceType(?string $type)
    {
        return $this->types[$type] ?? $this->types['string']  ?? new StringEipType();
    }


}
