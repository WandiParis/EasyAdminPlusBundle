<?php
/**
 *  This file is part of the Lego project.
 *
 *   (c) Joris Saenger <joris.saenger@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Lle\EasyAdminPlusBundle\Lib;

/* warning this class provide an working parts of Lego. Follow it*/
class QueryHelper{

    public static $iterationK = 0;
    protected $rootAlias;

    public function getPath(&$qb, $a, $col) 
    {
        $a = $a.'.';
        $this->rootAlias = $a;
        $baseA = \str_replace('.','',$this->rootAlias);
        if(\strstr($col,'.')){
            $childs = \explode('.',$col);
            $nb = \count($childs);
            foreach($childs as $k => $child){
                if($nb == $k+1){
                    $col = $child;
                    break; //la derniere iteration nest pas une jointure
                }
                $aliasJoin = $this->getAliasIfJoinExist($qb,$a.$child);
                if($aliasJoin){
                    $a = $aliasJoin.'.';
                } else {
                    $k += self::$iterationK;
                    $qb->innerJoin($a.$child,$baseA.$k);
                    $a = $baseA.$k.'.';
                    $col = $child;
                }
            }
            self::$iterationK = $k;
        }
        return array($a, $col);
    }

    protected function getAliasIfJoinExist($qb,$joiner){
        $dqp = $qb->getDQLParts();
        $baseA = \str_replace('.','',$this->getAlias());
        if(isset($dqp['join']) and isset($dqp['join'][$baseA])){
            foreach($dqp['join'][$baseA] as $join){
                if($join->getJoin() == $joiner){
                    return $join->getAlias();
                }
            }
        }
        return false;
    }

    public function getAlias(){
        return $this->rootAlias;
    }

    public function getPathInfo($configurator,$dataClass,$columnName){
        $return = false;
        $fields = \explode('.',$columnName);
        foreach($fields as $field){
            if($dataClass->hasField($field) or $dataClass->hasAssociation($field)){
                if($dataClass->hasAssociation($field)) {
                    $class = $dataClass->getAssociationTargetClass($field);
                    $dataClass = $configurator->getEntityManager()->getClassMetadata($class);
                    $return =  array('association'=>true,'type'=>$class);
                }else{
                    $return =  array('association'=>false,'type'=>$dataClass->getTypeOfField($field),'dbType'=>$dataClass->getTypeOfColumn($field));
                }
            }
        }
        return $return;
    }
}