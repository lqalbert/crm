<?php
namespace Home\Service;

use Home\Model\DepartmentModel;


class CustomersCountToday {

    private $entity = null;
    private $field = 'id';
    private $sort = SORT_ASC;

    // private $store  = false;

    public function __construct($entity){
        $this->entity = $entity;
    }

    public function setOrder($order){
        $fields  = explode(' ', $order);
        $this->field = $fields[0];
        $sortMap = array('asc'=>SORT_ASC, 'desc'=>SORT_DESC );
        $this->sort = $sortMap[$fields[1]];
    }


    public function index($date){
        
        $this->entity->setDate($date);

        $this->entity->setTypeCount();
        $this->entity->setConflictCount();
        $this->entity->setConflictedCount();
        $this->entity->setPullCount();
        $this->entity->setVCount();
        $this->entity->setCreateCount();
        $this->entity->setOwnCount();
        
        return $this->setRecord($date);
    }


    private function setRecord($date){
        /*$departM = new DepartmentModel();
        $departments = $departM->getSalesDepartments();*/
        $targets = $this->entity->getTargets();
        $fields  = $this->entity->getFields();
        $re = array();
        foreach ($targets as $value) {
            $tmp_row = array(
                'id'=>$value['id'], 
                'name'=>$value['name'],
                'date'=>$date
            );

            foreach ($fields as $v2) {
                $tmp_row[strtolower($v2)] = call_user_func(array($this->entity, 'get'.parse_name($v2, 1)), $value['id']);
            }
            $re[] = $tmp_row;
        }
        return  $this->reSort($re);
        
    }

    public function reSort($re){
        $columen = array_column($re, $this->field);
        array_multisort($columen, $this->sort , SORT_NUMERIC, $re);
        return $re;
    }
}