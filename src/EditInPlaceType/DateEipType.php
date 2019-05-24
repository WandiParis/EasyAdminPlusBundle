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

class DateEipType extends AbstractEipType{


    public function __construct(){

    }

    public function getTemplate(){
        return '@EasyAdmin/edit_in_place/_date.html.twig';
    }

    public function formatValue($value){
        return $value->format('d/m/Y');
    }

    public function getValueFromRequest(Request $request)
    {
        $value = $request->request->get('value');
        if($value != ''){
            $value = \DateTime::createFromFormat('d/m/Y',$request->request->get('value'));
        } else {
            $value = null;
        }
        return $value;
    }
}
