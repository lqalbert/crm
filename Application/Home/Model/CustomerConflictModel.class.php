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
        
        // $this->addMsgBox('手机号预查冲突:'.$value, $userid, $this->getCusSaleId($cus_id));

        $this->create(array('user_id'=>$userid, 'cus_id'=>$cus_id, 'type'=>0, 'value'=>$value));
        return $this->add();
    }

    public function addQq($cus_id, $userid, $value){
        // $this->addMsgBox('QQ号预查冲突:'.$value, $userid, $this->getCusSaleId($cus_id));
        $this->create(array('user_id'=>$userid, 'cus_id'=>$cus_id, 'type'=>1, 'value'=>$value));
        return $this->add();
    }


    public function addWeixin($cus_id,$userid, $value){
        // $this->addMsgBox('微信号预查冲突:'.$value, $userid, $this->getCusSaleId($cus_id));
        $this->create(array('user_id'=>$userid, 'cus_id'=>$cus_id, 'type'=>2, 'value'=>$value));
        return $this->add();
    }

    private function getCusSaleId($cus_id){
        return M('customers_basic')->where(array('id'=>$cus_id))->getField('salesman_id');
    }

    private function addMsgBox($content, $from , $to){
        $re = D("MsgBox")->add(array(
            'title'=>'预查冲突',
            'content'=>$content ,
            'from_id' => $from,
            'to_id' => $to
        ));
       
    }
}