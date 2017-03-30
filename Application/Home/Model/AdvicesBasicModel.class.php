<?php
namespace Home\Model;
use Think\Model;

class AdvicesBasicModel extends Model {
    const DELETE_STATUS = -1;
    public function delete($ids){
        return $this->where(array('id'=>array('in', $ids )))->save(array('status'=>self::DELETE_STATUS));
    }
}