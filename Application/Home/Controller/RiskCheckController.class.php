<?php
namespace Home\Controller;

use Home\Model\RoleModel;

class RiskCheckController extends CommonController{
    protected $table = "CustomerBuy";

    protected function getRoleState(){
        return 'risk_state';
    }

    protected function getTimeState(){
        return 'risk_time';
    }



    protected function getBUser(){
        return D("User")->getRisk('id,realname as name');
    }

    private function isBuser(){
        return $this->getRoleEname() == RoleModel::GOLD;
    }

    protected function setViewVar(){
        $this->assign('customerType', D('Customer')->getType());
        $this->assign('steps',        D('CustomerLog')->getSteps());
        $this->assign('logType',      D('CustomerLog')->getType());
        $this->assign('complainTypes',      D("CustomerComplain")->getType());

        $this->assign('state_text', $this->getRoleState());
        $this->assign('time_text', $this->getTimeState());
        // $this->assign('is_buser',   $this->isBuser());
        // $this->assign('buser', $this->getBUser());
    }

    public function index(){

        $ename = $this->getRoleEname();
        if ($ename == RoleModel::RISKGROUP) {
            redirect(U("RiskCheckGroup/index"));
        } else if($ename == RoleModel::CALLBACKCAPTAIN){
            redirect(U("CallBackCheckGroup/index"));
        }


        $this->setViewVar();
        $this->display('RiskCheck::index');
    }


    protected function _getList(){
        $this->setQeuryCondition();
        $count = (int)$this->M->count();
        $this->setQeuryCondition();
        $this->setField();
        $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->select();

        foreach ($list as &$value) {
            $user = M("user_info")->where(array("user_id"=>$value['salesman_id']))->field("department_id,group_id,realname,mphone")->find();
            $depart = D("Department")->where(array("id"=>$user['department_id']))->getField("name");
            $group = D("Group")->where(array("id"=>$user['group_id']))->getField("name");
            $value['user'] = $depart."-".$group."-".$user['realname'];
            $value['user_phone'] = $user['mphone'];
            $value['contact'] = M("customers_contacts")->where(array('cus_id'=>$value['cus_id']))->order("is_main desc")->select();
            $value['type_text'] = D('Customer')->getType($value['ctype']);
        }
        $result = array('list'=>$list, 'count'=>$count);
        return $result;
    }

    protected function setField(){
        $this->M->field("cb.name,cb.type as ctype, cb.id_card, cb.salesman_id, cb.address,customers_buy.*");
    }


    protected function setChPer(){
        $ch_id = I("get.ch_id");
        if ($ch_id != 0) {
            $this->M->where(array("risk_id"=>$ch_id));
        } else if($ch_id==0){
            //小组
            $group_id = $this->getUserGroupId();
            if ($group_id) {
                $users = D("User")->getGroupEmployee($group_id, 'id');
                $this->M->where(array('risk_id'=> array('IN', array_column($users, 'id'))));
            } else {
                $this->M->where(array('risk_id'=> -1 ));
            }
            
        }

        

        $state = I("get.state","");
        if ($state !=="") {
            $this->M->where(array('risk_state'=>$state));
        }
    }

    public function setQeuryCondition(){

        $this->M->join("customers_basic as cb on customers_buy.cus_id=cb.id");

        $this->setChPer();

        $name = I("get.name");
        if ($name) {
           $this->M->where(array("name"=>array('LIKE', $name.'%')));
        }

        $contact = I("get.contact");
        if ($contact) {
            $cus_ids = M("customers_contacts")->where(array("phone|qq|weixin"=>array('like', $contact.'%')))->getField("cus_id");
            if ($cus_ids) {
                $this->M->where( array("customers_buy.cus_id"=>array("IN", $cus_ids)));
            } else {
                $this->M->where( array("customers_buy.cus_id"=>-1));
            }
        }

        //时间区间
        $range = I("get.range");
        if ($range) {
            $dates = explode(" - ", $range);
            $this->M->where(array('buy_time'=>array(array('EGT', $dates[0]), array("ELT", $dates[1]))));
        }

        //销售部 团队 员工参数
        $user_id = I("get.user_id");
        if ($user_id) {
            $this->M->where(array('cb.salesman_id'=>$user_id));
            return;
        }

        $group_id = I("get.group_id");
        if ($group_id ) {
            $user_id = D("User")->getGroupEmployee($group_id, 'id');
            if ($user_id) {
                $user_id = array_column($user_id, 'id');
                $this->M->where(array('cb.salesman_id'=>array("IN", $user_id) ));
            }  else {
                $this->M->where(array('cb.salesman_id'=>-1 ));
            }
            return;
        }

        $depart_id = I("get.department_id");
        if ($depart_id) {
            $user_id = D("User")->getDepartmentEmployee($depart_id, 'id');
            if ($user_id) {
                $user_id = array_column($user_id, 'id');
                $this->M->where(array('cb.salesman_id'=>array("IN", $user_id) ));
            }  else {
                $this->M->where(array('cb.salesman_id'=>-1 ));
            }
        }

    }

    public function getDepartms(){
        $re = D("Department")->getGoodSalesDepartments("id,name");
        if ($re) {
            $this->ajaxReturn($re);
        } else {
            $this->ajaxReturn(array());
        }
    }

    public function getGroups(){
        $depart_id = I("get.id");
        $re = D("Group")->getAllGoups($depart_id, 'id,name');
        if ($re) {
            $this->ajaxReturn($re);
        } else {
            $this->ajaxReturn(array());
        }
    }

    public function getUsers(){
        $group_id = I("get.id");
        $re = D("User")->getGroupEmployee($group_id, 'id,realname as name');
        if ($re) {
            $this->ajaxReturn($re);
        } else {
            $this->ajaxReturn(array());
        }
    }

    protected function setCheckField($ch_id, $state){
        $this->M->where(array('risk_id'=>$ch_id))->data(array('risk_state'=>$state,'risk_time'=>Date("Y-m-d H:i:s")));
    }

    protected function stateCheck($ch_id, $id){
        return $this->M->where(array('id'=>$id, 'risk_id'=>$ch_id, 'state'=>1))->find();
    }

    public function check(){
        $id  = I("post.id");
        $state = I("post.state");
        $ch_id = I('post.ch_id');

        //同时 如果是已 审核通过了 就不能再 改为不通过
        if ($state == -1) {
            if ($this->stateCheck($ch_id, $id)) {
                $this->error("已审核通过");
            }
        }


        $this->M->where(array('id'=>$id));
        $this->setCheckField($ch_id, $state);
        $re = $this->M->save();
        if ($re !== false) {
            $cus_ids = $this->M->where(array('id'=>$id))->getField('cus_id', true);
            

            $pa = array('ids'=>$cus_ids,
                        'content'=>I("post.mark"),
                        'user_id'=>$ch_id);

            if ($state == -1) {
                $pa['track_text'] = "审核未通过";
            } else {
                $pa['track_text'] = "审核通过";
            }
            
            tag(HOOK_CHECK , $pa);
            //是否要给材料专员一个弹窗消息
            // $buys = array();
            //有两个任务 一个是 分配给某个人 一个是 发消息
            //两个任务合在一个job里 
            

            // $stuffs = D("User")->getDataStaff("id");
            $row=$this->M->where(array("id"=>$id, 'risk_state'=>1, 'callback_state'=>1))->find();
            if ($row) {
                $cusRow = D("Customer")->where(array('id'=>$row['cus_id']))->field('name,type')->find();
                // $param = array('job'=>'DisDataStaffJob', 'arg'=>array('id'=>$id, 'product_name'=>$row['product_name'], 'cus_name'=>$cusRow['name']));
                // tag(HOOK_QUEUE , $param);
                if ($cusRow['type'] == 'VX') {
                    $param2 = array('job'=>'CustomerTypeJob', 'arg'=>array('id'=>$row['cus_id'], 'type'=>'V'));
                    tag(HOOK_QUEUE , $param2);
                }
            }
            
          $this->success('成功');
        } else {
          $this->error($this->M->getError());
        }
    }

    //给材料的弹窗消息
    private function setMsg($id, $cus_id, $product){
        $re = M("msg_alert")->create(array(
                'title'=>"您有一个新的成交客户待审核",
                'content'=>"客户：".$this->getCusName($cus_id)." 商品：".$product,
                'to_id'=>$id));
        if ($re) {
            M("msg_alert")->add();
        }
    }

    private function getCusName($id){
        return D("Customer")->where(array("id"=>$id))->getField('name');
    }


}