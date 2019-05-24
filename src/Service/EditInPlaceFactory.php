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
use Lle\EasyAdminPlusBundle\EditInPlaceType\EntityEipType;
use Lle\EasyAdminPlusBundle\EditInPlaceType\StringEipType;
use Lle\EasyAdminPlusBundle\EditInPlaceType\TimeEipType;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

class EditInPlaceFactory
{

    public function __construct() {
    }

    public function getEditInPlaceType(?string $type){
        $class = new StringEipType();
        if($type == 'datetime'){
            $class = new DateTimeEipType();
        }else if($type == 'date') {
            $class = new DateEipType();
        }else if($type == 'time') {
            $class =  new TimeEipType();
        } elseif($type != null) {
            $class=  new StringEipType();
        } elseif($type == 'entity') {
            $class = new EntityEipType();
        }
        return $class;
    }


}
