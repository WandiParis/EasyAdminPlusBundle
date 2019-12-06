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

class DateTimeEipType extends AbstractEipType{


    public function __construct(){

    }

    public function getTemplate():string{
        return '@EasyAdmin/edit_in_place/_datetime.html.twig';
    }

    public function formatValue($value):string{
        if($value && $value instanceof \DateTime) {
            return $value->format('d/m/Y H:i');
        }
        return (string)$value;
    }

    public function getValueFromRequest(Request $request)
    {
        $value = $request->request->get('value');
        if($value != ''){
            $value = \DateTime::createFromFormat('d/m/Y H:i',$request->request->get('value'));
        } else {
            $value = null;
        }
        return ($value)? $value:null;
    }

    public function getType(): string{
        return 'datetime';
    }
}
