<?php
namespace Home\Controller;
/**
* 重写风控和回访的审核功能
* 两个合成一个 用角色区别对应的参数
*/

class BuyCheckController extends CommonController{

    protected $table = "CustomerBuy";


    private function getRoleState(){
        $map = array(
                'riskOne'=>'risk_state',
                'callBack'=>'callback_state'
            );
        if (isset($map[$this->getRoleEname()])) {
            return $map[$this->getRoleEname()];
        } else {
            return 'risk_state';
        }
        
    }

    public function index(){
        $this->assign('state_text', $this->getRoleState());
        $this->display();
    }

    protected function _getList(){
        $this->setQeuryCondition();
        $count = (int)$this->M->count();
        $this->setQeuryCondition();
        $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->select();
        $result = array('list'=>$list, 'count'=>$count);
        return $result;
    }


    public function setQeuryCondition(){
        $this->setRoleCondition();



        $this->M->join('customers_basic as cb on customers_buy.cus_id = cb.id')
                ->join('user_info as  ui on customers_buy.user_id = ui.user_id')
                ->join('department_basic as db on ui.department_id = db.id', 'left')
                ->field('customers_buy.id,customers_buy.user_id,customers_buy.cus_id,customers_buy.risk_state,customers_buy.callback_state,product_id,product_name,product_money,product_t,customers_buy.type,ui.realname,db.name as department_name, cb.name as cb_name')
                ->order('FIELD('.$this->getRoleState().', 0,1,-1), customers_buy.id desc');
    }



    private function setRoleCondition(){
        $role = $this->getRoleEname();
        $funName = $role."Condition";
        if (method_exists($this, $funName)) {
             call_user_func(array($this,$funName));
        }         
    }


    private function riskOneCondition(){
        $this->M->where(array('risk_id'=>session('uid')));
        if (isset($_GET['status']) && $_GET['status'] != "" ) {
            $this->M->where(array('risk_state'=>I('get.status')));
        }
    }

    private function callBackCondition(){
        $this->M->where(array('callback_id'=>session('uid')));
        if (isset($_GET['status']) && $_GET['status'] != "" ) {
            $this->M->where(array('callback_state'=>I('get.status')));
        }
    }

    public function check(){
        $ids  = I("post.ids");
        $state = I("post.state");

        $this->M->where(array('id'=>array('in', $ids)));
                // ->data(array('risk_state'=>$state))->save();
        $this->checkRoleCondition();
        $re = $this->M->save();
        if ($re) {
            if ($state==-1) {
                //跟踪纪录
                //跟踪纪录
                $cus_ids = $this->M->where(array('id'=>array('in', $ids)))->getField('cus_id', true);
                
                $pa = array('ids'=>$cus_ids,
                            'content'=>I("post.mark"),
                            'user_id'=>session("uid"));
                tag(HOOK_CHECK , $pa);
            }
            //是否要给材料专员一个弹窗消息
            // $buys = array();
            $stuffs = D("User")->getDataStaff("id");
            foreach ($ids as $id) {
                $row=$this->M->where(array("id"=>$id, 'risk_state'=>1, 'callback_state'=>1))->find();
                // $buys[] = $row;
                foreach ($stuffs as  $stuff) {
                    $this->setMsg($stuff['id'], $row['cus_id'], $row['product_name']);
                }
            }
          $this->success('成功');
        } else {
          $this->error($this->M->getError());
        }
    }





    //给风控和回访的弹窗消息
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

    private function checkRoleCondition(){
        $role = $this->getRoleEname();
        $funName = $role."CheckCondition";
        if (method_exists($this, $funName)) {
             call_user_func(array($this,$funName));
        }
    }

    private function riskOneCheckCondition(){
        $this->M->where(array('risk_id'=>session('uid'), 'risk_state'=>0))->data(array('risk_state'=>I("post.state")));
    }

    private function callBackCheckCondition(){
        $this->M->where(array('callback_id'=>session('uid'), 'callback_state'=>0))->data(array('callback_state'=>I("post.state")));
    }

    
}

