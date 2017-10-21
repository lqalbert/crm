<?php
namespace Common\Job;

class CommonJob {
    protected $id = "";

    public function setUp(){
        $this->id = $this->job->payload['id'];
        echo "setUp: ", D("Home/Queue")->setRunning($this->id);
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
        echo "tearDown: ", D("Home/Queue")->setComplete($this->id);
        echo "\n";
    }
}