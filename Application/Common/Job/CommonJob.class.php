<?php
namespace Common\Job;

class CommonJob {


    public function setUp(){
        echo "setUp: ", D("Queue")->setRunning($this->id);
        echo "\n";
    }

    public function perform(){
        try{
            $this->deal();
        }catch(\Excption $e){
            D("Queue")->setFailed($this->id);
        }
    }

    protected function deal(){
        /*$args = $this->args;
        fwrite(STDOUT, json_encode($args). PHP_EOL);*/
    }


    public function tearDown(){
        echo "tearDown: ", D("Queue")->setComplete($this->id);
        echo "\n";
    }
}