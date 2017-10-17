<?php
namespace Home\Controller;

use Home\Model\RoleModel;

class RiskCheckController extends CommonController{
    protected $table = "CustomerBuy";

    protected function getRoleState(){
        return 'risk_state';
    }

    protected function getBUser(){
        return D("User")->getRisk('id,realname as name');
    }

    private function isBuser(){
        return $this->getRoleEname() == RoleModel::GOLD;
    }

    public function index(){
        $this->assign('customerType', D('Customer')->getType());
        $this->assign('steps',        D('CustomerLog')->getSteps());
        $this->assign('logType',      D('CustomerLog')->getType());
        $this->assign('complainTypes',      D("CustomerComplain")->getType());

        $this->assign('state_text', $this->getRoleState());
        $this->assign('is_buser',   $this->isBuser());
        $this->assign('buser', $this->getBUser());

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
        }
        $result = array('list'=>$list, 'count'=>$count);
        return $result;
    }

    protected function setField(){
        $this->M->field("cb.name,cb.type as ctype, cb.id_card, cb.salesman_id, cb.address,customers_buy.*");
    }


    protected function setChPer(){
        $this->M->where(array("risk_id"=>I("get.ch_id")));

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
        $this->M->where(array('risk_id'=>$ch_id, 'risk_state'=>0))->data(array('risk_state'=>$state));
    }

    public function check(){
        $id  = I("post.id");
        $state = I("post.state");
        $ch_id = I('post.ch_id');

        $this->M->where(array('id'=>$id));
                // ->data(array('risk_state'=>$state))->save();
        $this->setCheckField($ch_id, $state);

        $re = $this->M->save();
        if ($re !== false) {
            if ($state==-1) {
                //跟踪纪录
                //跟踪纪录
                $cus_ids = $this->M->where(array('id'=>$id))->getField('cus_id', true);
                
                $pa = array('ids'=>$cus_ids,
                            'content'=>I("post.mark"),
                            'user_id'=>$ch_id);
                tag(HOOK_CHECK , $pa);
            }
            //是否要给材料专员一个弹窗消息
            // $buys = array();
            $stuffs = D("User")->getDataStaff("id");
            $row=$this->M->where(array("id"=>$id, 'risk_state'=>1, 'callback_state'=>1))->find();
            if ($row) {
                foreach ($stuffs as  $stuff) {
                    $this->setMsg($stuff['id'], $row['cus_id'], $row['product_name']);
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