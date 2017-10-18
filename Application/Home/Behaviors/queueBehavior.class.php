<?php
namespace Home\Behaviors;
/**
*  队列任务
*/
class queueBehavior extends \Think\Behavior {
    
    private $job = "";
    private $args = array();

    /**
    * $param => array(
    *   'job' => 'XxxxJob',
    *   'arg' => array()
    * )
    */
    public function run(&$param){

        vendor('php-resque.autoload');
        // $job = '\\Common\\Job\\ConflictJob'; // 定义任务类
        $this->init($param);

        $jobId = \Resque::enqueue(DEFALT_QUEUE, $this->job, $this->args, true);

        // echo "Queued job ".$jobId."\n\n";
        $this->save($jobId, $param);


    }

    private function init($param){
        $this->setJob($param);
        $this->setArgs($param);
    }

    private function setJob($param){
        $this->job = '\\Common\\Job\\'. $param['job'];
    }

    private function setArgs($param){
        $this->args = $param['arg'];
    }

    private function save($id, $param){
        M('redis_job')->add(array(
            'id'=>$id,
            'params'=>json_encode($param),
            'status'=> \Resque\Job\Status::STATUS_WAITING 
        ));
    }




    
}