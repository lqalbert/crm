<?php
namespace Home\Controller;

class IndexEchartsController extends \Think\Controller{

    public function index(){

        $this->ajaxReturn(preg_replace('/\s/','',$this->gold()));
    }


    public function gold(){
        $dd=<<< EOF

EOF;
        return $dd;
    }
}