<?php
namespace Home\Controller;

class CallBackCheckController extends RiskCheckController{

    protected function getRoleState(){
        return 'callback_state';
    }

    protected function getTimeState(){
        return 'callback_time';
    }

    protected function getBUser(){
        return D("User")->getCallback('id,realname as name');
    }

    protected function setChPer(){
        $this->M->where(array("callback_id"=>I("get.ch_id")));

        $state = I("get.state");
        if ($state !="") {
            $this->M->where(array('callback_state'=>$state));
        }
    }


    protected function setCheckField($ch_id, $state){
        $this->M->where(array('callback_id'=>$ch_id))->data(array('callback_state'=>$state,'call_time'=>Date("Y-m-d H:i:s")));
    }


}