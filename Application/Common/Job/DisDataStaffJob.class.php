<?php
namespace Common\Job;
//有两个任务 一个是 分配给某个人 一个是 发消息
            //两个任务合在一个job里 
class DisDataStaffJob extends CommonJob {


    

    protected function deal(){
        // var_dump($this);
        $args = $this->args;
        fwrite(STDOUT, json_encode($args). PHP_EOL);
    }

    
}