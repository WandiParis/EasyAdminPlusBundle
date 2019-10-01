<?php

namespace Lle\EasyAdminPlusBundle\Filter\FilterType;

use DateTime;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * DateFilterType
 */
class PeriodeFilterType extends AbstractFilterType
{

    private $choices;
    private $requestChoice;
    private $format;

    protected $flashBag;

    public function __construct(SessionInterface $session)
    {
        $this->flashBag = $session->getFlashBag();
    }

    public function configure(array $config = [])
    {
        parent::configure($config);
        $this->choices = $config['choices'] ?? null;
        $this->format = $config['format'] ?? 'd/m/Y';
    }

    /**
     * @param array  $data     The data
     * @param string $uniqueId The unique identifier
     */
    public function apply($queryBuilder)
    {
        if (isset($this->data['value'])) {
            $qb = $queryBuilder;
            $from = $to = null;
            $c = $this->alias . $this->columnName;
            if(isset($this->data['value']['from']) && $this->data['value']['from']) {
                $from = DateTime::createFromFormat($this->format, $this->data['value']['from']);
                if (!$from) {
                    $this->flashBag->add("error",'filter.periodeFilter.from.wrong_format');
                    return false;
                }
                $format = $format->format('Y-m-d');
                $qb->andWhere($c. ' >= :var_from_' . $this->uniqueId);
                $queryBuilder->setParameter('var_from_' . $this->uniqueId, $from);
            }
            if(isset($this->data['value']['to']) && $this->data['value']['to']) {
                $to = DateTime::createFromFormat($this->format, $this->data['value']['to']);
                if (!$to) {
                    $this->flashBag->add("error", 'filter.periodeFilter.to.wrong_format');
                    return false;
                }
                $to->modify('+1 day');
                $to = $to->format('Y-m-d');
                $qb->andWhere($c.' < :var_to_'.$this->uniqueId);
                $queryBuilder->setParameter('var_to_' . $this->uniqueId, $to);
            }
        }
    }

    public function getChoices(){
        $return = array();
        if(!$this->choices) return $return;
        foreach($this->choices as $choice){
            $from = (isset($choice['from']))? $choice['from']:'getFrom';
            $to = (isset($choice['to']))? $choice['to']:'getTo';
            $m = (isset($choice['method']))? $choice['method']:'findAll';
            if($from and $to and $m and isset($choice['table'])){
                list($bundle,$label) = explode(':',$choice['table']);
                $list = strtolower($label);
                $label = (isset($choice['label']))? $choice['label']:$label;
                $elms = $this->getRepository($choice['table'])->$m();
                $return[$label] = array('list'=>$list,'items'=>array());
                foreach($elms as $elm){
                    $f = $elm->$from();
                    $t = $elm->$to();
                    if($f and $t){
                        $return[$label]['items'][] = array(
                            'from'=>$elm->$from()->format($this->format),
                            'to'=>$elm->$to()->format($this->format),
                            'id'=>$elm->getId(),
                            'label'=>$elm->__toString());
                    }
                }
            }else{
                throw new \Exception('PeriodFilter: Element dans choices invalide choices[elm[from,to,table,method,label]]');
            }
        }
        return $return;

    }

    public function isSelected($list,$elm){
        if(is_array($this->requestChoice) and isset($this->requestChoice[$list])){
            return ($elm['id'] == $this->requestChoice[$list]);
        }else{
            return false;
        }
    }

    public function getStateTemplate(){
        return '@LleEasyAdminPlus/filter/state/periode_filter.html.twig';
    }

    public function getTemplate(){
        return '@LleEasyAdminPlus/filter/type/periode_filter.html.twig';
    }

}
