<?php
namespace Home\Controller;

class CallBackCheckController extends RiskCheckController{

    protected function getRoleState(){
        return 'callback_state';
    }

    protected function getTimeState(){
        return 'call_time';
    }

    protected function getBUser(){
        return D("User")->getCallback('id,realname as name');
    }

   

    protected function setChPer(){

        $ch_id = I("get.ch_id");
        if ($ch_id != 0) {
            $this->M->where(array("callback_id"=>$ch_id));
        } else if($ch_id==0){
            //小组
            $group_id = $this->getUserGroupId();
            if ($group_id) {
                $users = D("User")->getGroupEmployee($group_id, 'id');
                $this->M->where(array('callback_id'=> array('IN', array_column($users, 'id'))));
            } else {
                $this->M->where(array('callback_id'=> -1 ));
            }
            
        }

        

        $state = I("get.state","");
        if ($state !=="") {
            $this->M->where(array('callback_state'=>$state));
        }
    }


    protected function setCheckField($ch_id, $state){
        $this->M->where(array('callback_id'=>$ch_id))->data(array('callback_state'=>$state,'call_time'=>Date("Y-m-d H:i:s")));
    }

    protected function stateCheck($ch_id, $state, $id){
        return $this->M->where(array('id'=>$id, 'callback_id'=>$ch_id, 'state'=>1))->find();
    }


}