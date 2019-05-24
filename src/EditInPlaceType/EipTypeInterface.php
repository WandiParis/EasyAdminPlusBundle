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

interface EipTypeInterface{


    public function getTemplate():string;
    public function getValueFromRequest(Request $request);
    public function getWithoutEipLayout():bool;
    public function formatValue($value):string;
    public function canToErase():bool;
    public function hasCallback():bool;
    public function getType():string;
}
