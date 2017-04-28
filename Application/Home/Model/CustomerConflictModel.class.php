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

    public function addPhone($cus_id, $userid, $value){
        
        $this->create(array('user_id'=>$userid, 'cus_id'=>$cus_id, 'type'=>0, 'value'=>$value));
        return $this->add();
    }

    public function addQq($cus_id, $userid, $value){
        $this->create(array('user_id'=>$userid, 'cus_id'=>$cus_id, 'type'=>1, 'value'=>$value));
        return $this->add();
    }


    public function addWeixin($cus_id,$userid, $value){
        $this->create(array('user_id'=>$userid, 'cus_id'=>$cus_id, 'type'=>2, 'value'=>$value));
        return $this->add();
    }
}