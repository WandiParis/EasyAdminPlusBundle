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

class FloatEipType extends AbstractEipType{

    public function getTemplate(): string{
        return '@EasyAdmin/edit_in_place/_float.html.twig';
    }

    public function getType(): string{
        return 'float';
    }

    public function getValueFromRequest(Request $request){
        return (float) str_replace(',','.',$request->request->get('value'));
    }
}
