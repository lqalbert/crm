<?php
namespace Home\Controller;

class CallBackCheckController extends RiskCheckController{

    protected function getRoleState(){
        return 'callback_state';
    }

    protected function getBUser(){
        return D("User")->getCallback('id,realname as name');
    }

    protected function setChPer(){
        $this->M->where(array("callback_id"=>I("get.ch_id")));

        $state = I("get.state");
        if ($state !="") {
            $this->M->where(array('risk_state'=>$state));
        }
    }


    protected function setCheckField($ch_id, $state){
        $this->M->where(array('callback_id'=>$ch_id, 'callback_state'=>0))->data(array('callback_state'=>$state));
    }


}