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

use Doctrine\ORM\EntityManagerInterface;
use Idk\LegoBundle\Action\EditInPlaceAction;
use Symfony\Component\HttpFoundation\Request;

class EntityEipType extends AbstractEipType{


    private $em;

    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }

    public function getTemplate():string {
        return '@EasyAdmin/edit_in_place/_entity.html.twig';
    }

    public function canToErase():bool 
    {
        return true;
    }

    public function formatValue($value):string{
        if(method_exists($value, '__toString')){
            return (string) $value;
        }elseif(method_exists($value, 'getId')){
            return $value->getId();
        }else{
            return '-';
        }
    }

    public function hasCallback():bool 
    {
        return true;
    }

    public function getValueFromRequest(Request $request)
    {
        return $this->em->getRepository(str_replace('/', '\\', $request->request->get('cls')))->find($request->request->get('value'));
    }
    
    public function getType(): string{
        return 'association';
    }
}
