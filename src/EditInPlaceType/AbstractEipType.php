<?php
/**
 *  This file is part of the Lego project.
 *
 *   (c) Joris Saenger <joris.saenger@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Lle\EasyAdminPlusBundle\EditInPlaceType;


use Symfony\Component\HttpFoundation\Request;

abstract class AbstractEipType implements EipTypeInterface {


    public function __construct(){

    }

    public function getValueFromRequest(Request $request){
        return $request->request->get('value');
    }

    public function getWithoutEipLayout(){
        return false;
    }

    public function formatValue($value){
        return nl2br((string)$value);
    }

    public function canToErase(){
        return false;
    }

    public function hasCallback(){
        return false;
    }
}
