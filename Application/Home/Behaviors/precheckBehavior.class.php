<?php
namespace Home\Behaviors;


class precheckBehavior extends \Think\Behavior {

    private $list = array();
    private $uid = 0;
    private $type ="";


    //行为执行入口
    public function run(&$param){


        

        $this->list = $param['list'];
        $this->uid = $param['uid'];
        $this->type = $param['type'];
        $this->value = $param['value'];

        $this->D = D('CustomerConflict');

        
        $this->deal();


    }




    public function deal(){
        foreach ($this->list as $value) {
            $funcname = "add". ucfirst($this->type);
            
            if (method_exists($this->D, $funcname)) {
                call_user_func(array($this->D, $funcname), $value, $this->uid, $this->value);
            }
        }
    }
}