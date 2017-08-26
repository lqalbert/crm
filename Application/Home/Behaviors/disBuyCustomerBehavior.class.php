<?php
namespace Home\Behaviors;

/**
* 分配给风控和回访专员
*
* 暂时轮 的办法 等正式上线了 再改成判断是不是陆录了的
*/

class disBuyCustomerBehavior extends \Think\Behavior {

    private $riskRoll = 0;
    private $callBackRoll = 0;

    //行为执行入口
    public function run(&$param){


       $this->initRoll();

       $this->deal($param);
    }


    private function getRisk(){
        return D('User')->getRisk();
    }

    private function getCallback(){
        return D('User')->getCallback();
    }

    private function setRoll($riskRoll=0, $callBackRoll=0 ){
        S('riskRoll', $riskRoll);
        S('callBackRoll', $callBackRoll);
    }

    private function initRoll(){
        $roll = S('riskRoll');
        if ($roll) {
            $this->riskRoll = $roll;
        } 

        $roll = S('callBackRoll');
        if ($roll) {
            $this->callBackRoll = $roll;
        } 
    }

    private function deal($param){
        $riskUsers = $this->getRisk();
        $callUsers = $this->getCallback();

        // echo "riskUsers";
        // var_dump($riskUsers);

        // echo "callUsers";
        // var_dump($callUsers);


        $risk_i = $this->riskRoll % count($riskUsers);
        $call_i = $this->callBackRoll % count($callUsers);

        // var_dump($risk_i);
        // var_dump($call_i);

        $data = array(
            'risk_id'=>$riskUsers[$risk_i]['id'],
            'callback_id'=>$callUsers[$call_i]['id']
        );
        

        $this->setRoll(++$risk_i, ++$call_i);
        
        $re = M('customers_buy')->data($data)->where(array('id'=>$param['id']))->save();

        
    }
}