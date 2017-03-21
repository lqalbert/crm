<?php
namespace Home\Model;
use Think\Model;

class CustomerConflictModel extends Model {
    protected $tableName = 'customers_conflict';

   
    private $type = array(
        "手机",
        "qq号",
        "微信号"
        );

    public function addPhone($cus_id, $value){
        $this->create(array('user_id'=>session('uid'), 'cus_id'=>$cus_id, 'type'=>0, 'value'=>$value));
        return $this->add();
    }

    public function addQQ($cus_id, $value){
        $this->create(array('user_id'=>session('uid'), 'cus_id'=>$cus_id, 'type'=>1, 'value'=>$value));
        return $this->add();
    }


    public function addWx($cus_id, $value){
        $this->create(array('user_id'=>session('uid'), 'cus_id'=>$cus_id, 'type'=>2, 'value'=>$value));
        return $this->add();
    }
}