<?php
namespace Home\Model;

use Think\Model;

class DistributeModel extends Model{

    const DEPARTMENT = 1;
    const GROUP = 2;
    const GOLD = 0;


    protected $tableName = 'distribute_basic';


    public static $type  = array(
        0=>"总经办",
        1=>"部门",
        3=>"小组"
    );



    public function setOne($type, $obj_id){
        $data = $this->create(array("type"=>$type, 'obj_id'=>$obj_id));
        if (!$data) {
            return false;
        }

        $data['config'] = '{"limina":0,"type":1,"list":[]}';
        return $this->add($data);
    }
}