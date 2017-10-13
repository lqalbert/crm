<?php
namespace Home\Controller;

use Home\Model\RoleModel;

class PerformanceForSpreadController extends CommonController {

    protected $table = 'statistics_spread_achievement';
    protected $pageSize = 30;

    protected $searchGroup = array(
                                array('value'=>'user','key'=>"显示队员" ),
                                array('value'=>'group','key'=>"显示团组" ),
                                array('value'=>'department','key'=>'显示部门')
                            );

    protected function getSearchGroup(){
        return $this->searchGroup;
    }

    //获取推广部
    protected function getDepart(){
        return D("Department")->getSpreadDepartments();
    }



    public function index(){

        $role = $this->getRoleEname();
        if ($role == RoleModel::SP_MASTER ) {
            $controllerName = "PerformanceForSpreadDepart";
            redirect(U($controllerName."/index"));
        } else if( $role == RoleModel::SP_CAPTAIN ){
            $controllerName = "PerformanceForSpreadGroup";
            redirect(U($controllerName."/index"));
        }


        $this->assign('Alldeps',  $this->getDepart());
        $this->assign('searchGroup', $this->getSearchGroup());
        $this->assign('initDep', 0);

        $this->display();
    }

    public function setQeuryCondition(){

        $type = I('get.type');
        switch ($type) {
            case 'user':
               $this->M->group('user_id')->field('user_id, sum(order_num) as order_num, sum(sale_amount) as sale_amount, group_name,department_name');
                break;
            case 'group':
              $this->M->group('group_id')->field('group_id, sum(order_num) as order_num, sum(sale_amount) as sale_amount, group_name,department_name');
                break;
            case 'department':
              $this->M->group('department_id')->field('department_id, sum(order_num) as order_num, sum(sale_amount) as sale_amount, department_name');
                break;
            default:
             $this->M->group('department_id')->field('department_id, sum(order_num) as order_num, sum(sale_amount) as sale_amount, department_name');
                break;
        }

        $start = I('get.start', date("Y-m-d", time()-86400));
        $end   = I('get.end',   date("Y-m-d", time()-86400));

        $this->M->where(array('date'=>array(array('EGT', $start), array('ELT', $end.' 23:59:59'))));

        $departId = I('get.department_id');
        if ($departId) {
            $this->M->where(array('department_id'=>$departId));
        }

        $groupId = I('get.group_id');
        if ($groupId) {
            $this->M->where(array('group_id'=>$groupId));
        }
    }

    protected function _getList(){

        // $this->setQeuryCondition();
        $this->setQeuryCondition();

        $list = $this->M->order(I('get.sort_field','order_num'), I('get.sort_order', 'desc'))->select();
        foreach ($list as &$value) {
            $value['name'] = $value['department_name'];
            if (isset($value['group_name'])) {
                 $value['name'] = $value['name']. "-". $value['group_name'];
            }
            if (isset($value['user_id'])) {
                $value['name'] = $value['name'] . "-". M("user_info")->where(array("user_id"=>$value['user_id']))->getField('realname');
            }
        }
        $result = array('list'=>$list, 'count'=>count($list));
        
        return $result;
    }

    public function getGroups(){
        $id = I("get.department_id");
        $this->ajaxReturn(D("Group")->getAllGoups($id, 'id,name'));
    }

    private function setDetailCondition(){

        $id  = I("get.id");
        $type     = I("get.type");
        
        $start   = I("get.start");
        $end     = I("get.end");

        if ($type == 'user') {
            $users = array($id);
        } else if($type == 'group'){
            $users = array_column(D("User")->getGroupEmployee($id, 'id'), 'id') ;
        } else if($type == 'department'){
            $users = array_column(D("User")->getDepartmentEmployee($id, 'id'), 'id');
        }

        
            
        

        M("customers_order")->where(array(
            'customers_order.user_id'=> array('IN', $users), 
            'customers_order.created_at'=>array(array('EGT', $start), array('ELT', $end." 23:59:59"))))->join("customers_buy on customers_order.buy_id = customers_buy.id")
                            ->field("customers_order.* ,customers_buy.product_name, customers_buy.buy_time");
    }


    public function getOrderInfo(){
        
        $pageSize = 10;
        // $this->M->page(I('get.p',0). ','. $this->pageSize)->order('id desc')->select();
        $this->setDetailCondition();
        $count = M("customers_order")->count();
        $this->setDetailCondition();
        $re    = M("customers_order")->page(I('get.p',0). ','. $pageSize)->select();

        foreach ($re as &$value) {
            $user = M("user_info")->where(array('user_id'=>$value['salesman_id']))->field('group_id,mphone')->find();
            $value['mphone']  = $user['mphone'];
            if (!empty($value['sale_name'])) {
                $tmp = explode(" ", $value['sale_name']);
                $groupName = D("Group")->where(array("id"=>$user['group_id']))->getField('name');
                $value['sale_name'] = $tmp[0]."-".$groupName."-".$tmp[1];
            }
            
        }
        $this->ajaxReturn(array('list'=>$re, 'count'=>$count));

    }



}