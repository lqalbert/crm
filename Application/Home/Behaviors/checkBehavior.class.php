<?php
namespace Home\Behaviors;
/**
* 审核不通过
*/
class checkBehavior extends \Think\Behavior {
    


    //行为执行入口
    public function run(&$param){

        $ids= $param['ids'];
        foreach ($ids as $key => $value) {
            D('CustomerLog')->data(array('cus_id'=>$value,'user_id'=>$param['user_id'], 'track_text'=>$param['track_text'], 'content'=>$param['content']))->add();
        }


    }




    
}