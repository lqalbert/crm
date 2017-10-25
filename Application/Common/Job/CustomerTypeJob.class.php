<?php
namespace Common\Job;
//有两个任务 一个是 分配给某个人 一个是 发消息
            //两个任务合在一个job里 
class CustomerTypeJob extends CommonJob {

    private $roll = 0;

    protected function deal(){
        // var_dump($this);
        $args = $this->args;
        // $id = $this->id;
        fwrite(STDOUT, json_encode($args). PHP_EOL);
        $this->setType($args['id'], $args['type']);
        
    }

    private function setType($id, $type){
        $re = D("Home/Customer")->where(array('id'=>$id))->data(array('type'=>$type))->save();
    }


    
}