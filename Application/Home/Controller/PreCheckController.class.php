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

        $this->M->field('customers_basic.id,customers_basic.name,customers_basic.type, customers_basic.created_at,(customers_basic.service_time-'.$sixMonthAgo.') as s, ui2.realname as user_name, db.name as db_name, ui1.realname as spread_name, db2.name as spread_departname')
                
                ->join('left join user_info as ui2 on customers_basic.salesman_id = ui2.user_id')
                ->join('left join department_basic as db on ui2.department_id = db.id')

                ->join('left join user_info as ui1 on customers_basic.user_id = ui1.user_id')
                ->join('left join department_basic as db2 on ui1.department_id = db2.id');

        $where = array();
        if (!empty($queryName)) {
            $where['customers_basic.name'] = $queryName;
        } 

        $queryAgu = array('qq', 'phone', 'weixin');
        $cus_ids = array();
        foreach ($queryAgu as $value) {
            $arg = trim(I('get.'.$value));
            if (!empty($arg)) {
                $re = M('customers_contacts')->where(array($value=>$arg))->getField('cus_id', true);
                if ($re) {
                    $cus_ids = array_merge($re, $cus_ids);
                    
                    if ( !session('?'.$value."_".$arg)) {
                        $pa = array('list'=>$re, 'uid'=>session('uid'), 'type'=>$value, 'value'=> $arg);
                        tag(HOOK_PRECHECK , $pa);
                        session($value."_".$arg, true);
                    }
                }
                
            }
        }
        $cus_ids = array_keys(array_flip($cus_ids));

        if (count($cus_ids) !=0) {
            $where['customers_basic.id'] = array('IN', $cus_ids);
        } else if(empty($queryName)) {
            $where['customers_basic.name'] = '00000000000000000000000';
        }
        $where['_logic'] = 'OR';
        
        $this->M->where($where);
        
        
    }


    public function getUser(){
        $this->error("关闭");
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
