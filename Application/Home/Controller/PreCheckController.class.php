<?php
namespace Home\Controller;

use Home\Model\RoleModel;

class PreCheckController extends CommonController {
    protected $table = "customer";

    public function index(){
        $this->assign('customerType', $this->M->getType());
        $this->assign('s_time', 6*30*86400); //6个月
        $this->display();
    }


    public function serach(){
        $result = $this->_getList();

        if (IS_AJAX) {
            $this->ajaxReturn($result); 
        }  else {
            return $result;
        }
    }


    public function setQeuryCondition(){
        $queryName = I('get.name', null);

        $sixMonthAgo = time()-15552000;

        $this->M->field('customers_basic.id,customers_basic.name,customers_basic.type, customers_basic.created_at,(customers_basic.service_time-'.$sixMonthAgo.') as s, ui.realname as user_name, db.name as db_name')
                ->join('inner join customers_contacts as cc1 on customers_basic.id = cc1.cus_id')
                ->join('left join user_info as ui on customers_basic.salesman_id = ui.user_id')
                ->join('left join department_basic as db on ui.department_id = db.id');

        if (!empty($queryName)) {
            $this->M->where(array('customers_basic.name'=> $queryName));
        } 

        $queryAgu = array('qq', 'phone', 'weixin');
        $cus_ids = array();
        foreach ($queryAgu as $value) {
            $arg = I('get.'.$value);
            if (!empty($arg)) {
                $re = M('customers_contacts')->where(array($value=>$arg))->getField('cus_id', true);
                $cus_ids = array_merge($re, $cus_ids);
                if (!session('?'.$value."_".$arg)) {
                    $pa = array('list'=>$re, 'uid'=>session('uid'), 'type'=>$value, 'value'=> $arg);
                    tag('precheck_que' , $pa);
                    session($value."_".$arg, true);
                }
            }
        }

        if (count($cus_ids) !=0) {
            $this->M->where(array('customers_basic.id'=>array('IN', $cus_ids)));
        } else if(empty($queryName)) {
            $this->M->where(array('customers_basic.name'=> '00000000000000000000000'));
        }

        
        
    }


    public function getUser(){
        $id = I("post.id");

        //如果是 部门经理 就暂时不能索取
        if (D('Role')->getEnameById(session('account')['userInfo']['role_id']) == RoleModel::DEPARTMENTMASTER) {
            $this->error('部门经理暂进还不能索取哦');
        }

        $re = $this->M->changeSalesman($id, session('uid'));
        if ($re) {
            $this->success('索取成功');
        } else {
            $this->error('索取失败'. $this->M->getError());
        }
    }


}
