<?php
namespace Home\Model;

use Think\Model;

class DistributeRecordModel extends Model{


    public function getDetail(&$data){
        foreach ($data as $key => &$value) {
            $value['details'] = M("distribute_detail")->where(array("record_id"=>$value['id']))->select();
        }
    }

}