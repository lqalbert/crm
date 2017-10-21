<?php
namespace Home\Model;

class QueueModel extends \Think\Model{

    protected $tableName = 'redis_job';

    public function setRunning($id){
        $data = array('status'=> \Resque\Job\Status::STATUS_RUNNING  );
        return $this->where(array('id'=>$id))->data($data)->save();
    }

    public function setComplete($id){
        $data = array('status'=> \Resque\Job\Status::STATUS_COMPLETE  );
        return $this->where(array('id'=>$id))->data($data)->save();
    }

    public function setFailed($id){
        $data = array('status'=> \Resque\Job\Status::STATUS_FAILED   );
        return $this->where(array('id'=>$id))->data($data)->save();
    }
    
}