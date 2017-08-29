<?php
namespace Home\Controller;

class MakeOrderController extends CommonController {
    protected $pageSize = 15;
    protected $table  = 'CustomerBuy';
    private $error = "";

    private $dCondition = array(
            'customers_buy.risk_state' => 1,
            'customers_buy.callback_state' => 1,
            'customers_buy.status'=> 0
        );



    public function index(){

        $this->assign('seMaster', D('User')->getSupService());
        $this->display();
    }


    public function setQeuryCondition(){

        $this->dCondition['customers_buy.status'] = I('get.status');
        $this->M->join('user_info as ui on customers_buy.user_id=ui.user_id')
                ->join('customers_basic as cb on cb.id = customers_buy.cus_id','LEFT')
                ->join('department_basic as db on ui.department_id = db.id', 'left')
                ->field('customers_buy.* , ui.realname, db.name as department_name,cb.name as cus_name')
                ->where($this->dCondition);
    }


    public function getList(){
        $result = $this->_getList();
        // echo $this->M->getLastSql();
        if (IS_AJAX) {
            $result['list'] = $this->M->decoratorButtons($result['list']);

            $this->ajaxReturn($result);
            // $this->ajaxReturn($this->M->getLastSql());
        }  else {
            
            return $result;
        }
    }

    public function addOrder(){

        $rules = array(
             array('buy_id','','该用户已开单',0,'unique')
        );
        $data = M('customers_order')->validate($rules)->create();
        if (!$data) {
            $this->error(M('customers_order')->getError());
        }

        M('customers_order')->startTrans();

        $buyRow = $this->M->find($data['buy_id']);

        $saveRe = $this->M->where(array('id'=>$data['buy_id']))
                                    ->data(array('status'=>1, 'todo_list'=>$this->delToList( $buyRow['todo_list'], 'order') ))
                                    ->save();
        if ($saveRe === false) {
            M('customers_order')->rollback();
            $this->error(M('customers_buy')->getErro());
        }
        
        //试验性的 semaster_id
        // M()->execute('update customers_basic set semaster_id=0, gen_id=0 where (semaster_id!=0 or gen_id!=0) and id='.$data['cus_id']);


        $data['creater_id'] = session('uid');
        $re = M('customers_order')->data($data)->add();
        if ($re) {
            M('customers_order')->commit();
            $this->success('操作成功');
        } else {
            $this->error(M('customers_order')->getErro());
        }

    }

    // 客户姓名
    // 客户手机
    // 锁定人员
    // 跟踪人员
    public function getBuyDetail(){
        
        $id = I('get.cus_id');
        $customerRow = M('customers_basic')->find($id);
        $customerContacts = M('customers_contacts')->field('phone')->where(array('cus_id'=>$id))->select();
        //锁定人员
        $userRow = M("user_info")->where(array('user_info.user_id'=>$customerRow['user_id']))
                                 ->join('department_basic as  db on user_info.department_id = db.id', 'left')
                                 ->field('user_info.realname, user_info.user_id,db.name')
                                 ->find();
        //跟踪人员
        $saleRow = M("user_info")->where(array('user_info.user_id'=>$customerRow['salesman_id']))
                                 ->join('department_basic as  db on user_info.department_id = db.id', 'left')
                                 ->field('user_info.realname, db.name, user_info.user_id')
                                 ->find();

        $this->ajaxReturn(array(
            'name'=>$customerRow['name'],
            'phones'=>$customerContacts,
            'user'=>$userRow,
            'sale'=>$saleRow,
        ));

    }

    // 续费的话呢 可以 流程会不一样暂时不考虑 “原客服”
    public function setDistribute(){
        $id = I('post.cus_id');
        $semaster_id = I('post.semaster_id');

        $data = array('semaster_id'=>$semaster_id);
        $re   = D("Customer")->where(array('id'=>$id))->data($data)->save();

        $buyRow = $this->M->find(I("post.buy_id"));

        $saveRe = $this->M->where(array('id'=>$buyRow['id']))
                                    ->data(array('todo_list'=>$this->delToList( $buyRow['todo_list'], 'distribute') ))
                                    ->save();
        

        if ($re!== false) {
            $this->success();
        } else {
            $this->error(D("Customer")->getError());
        }
    }

    public function setAccount(){
        $_POST['open_id'] = session('uid');
        $_POST['user_id'] = I('user_id');
        $data = M('software_account')->create();
        if (!$data) {
            $this->error(M('software_account')->getError());
        } else {
            
            $buyRow = $this->M->find(I("post.buy_id"));
            $data['pdt_id'] = $buyRow['product_id'];
            $re = M('software_account')->data($data)->add();

            $saveRe = $this->M->where(array('id'=>$buyRow['id']))
                                    ->data(array('todo_list'=>$this->delToList( $buyRow['todo_list'], 'account') ))
                                    ->save();
            if ($re) {
                $this->success('操作成功');
            } else {
                $this->error(M('software_account')->getError());
            }
        }
    }

    private function delToList($arr, $field){
        $tmp = json_decode($arr);
        $i = array_search($field,$tmp);
        
        if ($i !== false) {
            unset($tmp[$i]);
        }

        return json_encode(array_values($tmp));
    }

    public function getOrderInfo($buy_id){
        $arr = M('customers_order as co')
              ->join("user_info as ui on ui.user_id = co.creater_id")->where(array('buy_id'=>$buy_id))
              ->field('co.sale_money,co.receivable,co.paid_in,co.customer_name,co.phone,
                co.user_name,co.sale_name,co.creater_id,co.created_at,ui.realname as creater')
              ->find();
        //var_dump($arr);die();
        $this->ajaxReturn($arr);


    }

    private function _addOrder(){
        $rules = array(
             array('buy_id','','该用户已开单',0,'unique')
        );
        $data = M('customers_order')->validate($rules)->create($_POST['order']);
        if (!$data) {
            $this->error = M('customers_order')->getError();
            return false;
        }

        $buyRow = $this->M->find($data['buy_id']);

        $saveRe = $this->M->where(array('id'=>$data['buy_id']))
                                    ->data(array('status'=>1, 'todo_list'=>$this->delToList( $buyRow['todo_list'], 'order') ))
                                    ->save();
        if ($saveRe === false) {
            $this->error = M('customers_buy')->getError();
            return false;
        }
        
        $data['creater_id'] = session('uid');
        $re = M('customers_order')->data($data)->add();
        if ($re) {
            return true;
        } else {
            $this->error = M('customers_order')->getError();
            return false; 
        }
    }

    private function _addAccount(){
        $_POST['account']['open_id'] = session('uid');
        // $_POST['account']['user_id'] = I('post.account.user_id');

        $data = M('software_account')->create($_POST['account']);
        if (!$data) {
            // $this->error(M('software_account')->getError());
            $this->error = M('software_account')->getError();
            return false;
        } else {
            
            $buyRow = $this->M->find($_POST['account']['buy_id']);
            // $data['pdt_id'] = $buyRow['product_id'];
            $re = M('software_account')->data($data)->add();

            $saveRe = $this->M->where(array('id'=>$buyRow['id']))
                                    ->data(array('todo_list'=>$this->delToList( $buyRow['todo_list'], 'account') ))
                                    ->save();
            if ($re) {
                // $this->success('操作成功');
                return true;
            } else {
                // $this->error(M('software_account')->getError());
                $this->error = M('software_account')->getError();
                return false;
            }
        }
    }

    private function _setDis(){
        $id = $_POST['distribute']['cus_id']; //I('post.distribute.cus_id');
        $semaster_id = $_POST['distribute']['semaster_id'];// I('post.distribute.semaster_id');
       
        $data = array('semaster_id'=>$semaster_id);
        
        $re   = D("Customer")->where(array('id'=>$id))->data($data)->save();

        $buyRow = $this->M->find($_POST['distribute']['buy_id']);

        $saveRe = $this->M->where(array('id'=>$buyRow['id']))
                                    ->data(array('todo_list'=>$this->delToList( $buyRow['todo_list'], 'distribute') ))
                                    ->save();
        

        if ($saveRe !== false) {
            return true;
        } else {
            $this->error=D("Customer")->getError();
            return false;
        }
    }



    public function setOneStep(){
        $m  = M();
        $m->startTrans();
        $orderRe = $this->_addOrder();
        if (!$orderRe) {
            $m->rollback();
            $this->error($this->error);
        }
        $accountRe = $this->_addAccount();
        if (!$accountRe) {
            $m->rollback();
            $this->error($this->error);
        }
        $disRe = $this->_setDis();
        if (!$disRe) {
            $m->rollback();
            $this->error($this->error);
        }
        
        $m->commit();
        $this->success("操作成功");
    }

    //获取已分配的
    public function getDisId(){
        $cus_id = I("get.cus_id");
        $semaster_id = D("Customer")->where(array("id"=>$cus_id))->getField('semaster_id');
        if ($semaster_id) {
            $this->ajaxReturn(array("id"=>$semaster_id));
        } else {
            $this->error('');
        }
    }

    //获取已有的账号
    public function getAccount(){
        $cus_id = I("get.cus_id");
        $account = M('software_account')->where(array('cus_id'=>$cus_id))->find();
        if ($account) {
            $this->ajaxReturn(array("account_id"=>$account['account_id']));
        } else {
            $this->error();
        }
    }











}