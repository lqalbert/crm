<?php
namespace Home\Controller;


use Home\Model\DepartmentModel;
//todo   fix problem 没有客户的 没有统计 部门显示只显示一条
class SpreadCustomerSortController extends CommonController{

    protected $table = "Customer";
    protected $pageSize = 15;
  
    public function index(){

        $this->assign('searchGroup', array(array("name"=>"个人", "value"=>"user_id"), array("name"=>"小组", "value"=>"to_gid"), array("name"=>"部门", "value"=>"spread_id")));

        $this->assign("allDeparts", $this->getSpreadDepartment());
        $this->assign("role", $this->getRoleEname());
        $this->setRoleVar();
        $this->display();
    }

    private function setRoleVar(){
        // $role = $this->getRoleEname();
        $role= 'gold';
        switch ($role) {
            case 'gold':
                $this->assign("spread_id", "");
                $this->assign("to_gid", "");
                $this->assign('allgroups', array());
                break;
            case 'spreadMaster':
                $this->assign("spread_id", $this->getUserDepartmentId());
                $this->assign("to_gid", "");
                $this->assign('allgroups',D("Group")->getAllGoups($this->getUserDepartmentId()));
                break;
            case 'spreadCaptain':
                $this->assign("spread_id", $this->getUserDepartmentId());
                $this->assign("to_gid", $this->getUserGroupId());
                $this->assign('allgroups', array());
                break;
            default:
                $this->assign("spread_id", "");
                $this->assign("to_gid", "");
                $this->assign('allgroups', array());
                break;
        }
        
    }

    private function getSpreadDepartment(){
        return D("Department")->getSpreadDepartments();
    }

    public function setQeuryCondition(){

        $searchGroup = I("get.searchgroup");
        $spread_id = I("get.spread_id",0);
        $this->spread_id = $spread_id;
        $to_gid = I("get.to_gid",0);
        $this->group_id = $to_gid;
        $this->start = I("get.start");
        $this->end   = I("get.end");


        //1 获取所有的
        $all = $this->getDataAll($searchGroup);

        //2 统计录入数
        $this->getCAll($searchGroup);

        $numAll = $this->M->select();
        

        //3 统计成交数
        $vAll = $this->getDataVAll($searchGroup);

        //4 组合
        return  $this->getDataContact($all,$numAll,$vAll);


        //5 排序分页


        

        

       
       /* $this->M->group($searchgroup);

        $this->fields = "count(customers_basic.id) as c, customers_basic.$searchGroup as obj_id";
        // $this->M->field();

        $to_gid = I("get.to_gid",0);
        if (!empty($to_gid)) {
            $this->M->where(array("to_gid"=>$to_gid));
            return;
        }

        $spread_id = I("get.spread_id",0);
        if (empty($spread_id)) {
            $this->M->where(array("spread_id"=>array("NEQ", $spread_id)));
        } else {
            $this->M->where(array("spread_id"=>$spread_id));
        }

        // $roleName = $this->getRoleEname();
        $roleName = "gold";
        $funcName = $roleName."Condition";
        if (method_exists($this, $funcName)) {
            
            call_user_func(array($this, $funcName), $searchGroup);
        }*/

    }  
    //GOLD
    //SP_MASTER
    //SP_CAPTAIN

    private function goldCondition($searchgroup){

        switch ($searchgroup) {
            case 'user_id':
                $this->M->join("left join department_basic as db on customers_basic.spread_id=db.id")
                        ->join("left join user_info as ui on  customers_basic.user_id = ui.user_id")
                        ->field($this->fields.", concat(db.name,'-', ui.realname) as name");
                break;

            case 'to_gid':
                $this->M->join("left join department_basic as db on customers_basic.spread_id=db.id")
                        ->join("left join user_info as ui on  customers_basic.user_id = ui.user_id")
                        ->join("left join group_basic as gb on ui.group_id = gb.id")
                        ->field($this->fields.", concat(db.name,'-', gb.name) as name");
                break;

            case 'spread_id':
                 $this->M->join("left join department_basic as db on customers_basic.spread_id=db.id")
                        ->field($this->fields.", db.name");
                break;
            
            default:
                $this->M->join("left join department_basic as db on customers_basic.spread_id=db.id")
                        ->field($this->fields.", db.name");
                break;
        }
    }

    private function spreadMasterCondition($searchgroup){
        switch ($searchgroup) {
            case 'user_id':
                $this->M->join("left join user_info as ui on  customers_basic.user_id = ui.user_id")
                        ->field($this->fields.", ui.realname as name");
                break;

            case 'to_gid':
                $this->M->join("left join user_info as ui on  customers_basic.user_id = ui.user_id")
                        ->join("left join group_basic as gb on ui.group_id = gb.id")
                        ->field($this->fields.", concat(gb.name,'-', ui.realname) as name");
                break;
            default:
                $this->M->join("left join user_info as ui on  customers_basic.user_id = ui.user_id")
                        ->field($this->fields.", ui.realname as name");
                break;
        }
    }

    private function spreadCaptainCondition($searchgroup){
        switch ($searchgroup) {
            case 'user_id':
                $this->M->join("left join user_info as ui on  customers_basic.user_id = ui.user_id")
                        ->field($this->fields.", ui.realname as name");
                break;
            default:
                $this->M->join("left join user_info as ui on  customers_basic.user_id = ui.user_id")
                        ->field($this->fields.", ui.realname as name");
                break;
        }
    }




    private function getAll(){
        $this->setQeuryCondition();
        $re = $this->M->select();
       
        return $re;
    }

    private function getVAll(){
        $this->setQeuryCondition();
        $this->M->where(array('customers_basic.type'=>array(array('EQ','V'),array('EQ','VX'),array('EQ','VT'), 'OR')));
        return $this->M->select();
    }

    //组合
    private function setContact($all, $vall){
        $all_map  = arr_to_map($all, 'obj_id');
        $vall_map = arr_to_map($vall, 'obj_id', 'c');
        foreach ($all_map as $key => &$value) {
            if (isset($vall_map[$key])) {
                $value['v'] = $vall_map[$key];
            } else {
                $value['v'] = 0;
            }
        }
        return array_values($all_map);
    }

    private function setOrder(&$all, $orderfield, $orderway){
        $array = array("asc"=> SORT_ASC, 'desc'=> SORT_DESC );
        $columen = array_column($all, $orderfield);
        array_multisort($columen, $array[$orderway] , SORT_NUMERIC, $all);
    }

    private function setPage($re){
        $page = I('get.p',0);
        $re = array_chunk($re, $this->pageSize);
        return $re[$page-1];
    }



    public function getList(){
       /* $all = $this->getAll();
        $vall = $this->getVAll();*/
        $re = $this->setQeuryCondition();

        // $re = $this->setContact($all, $vall);
        $this->setOrder($re, I("get.field", 'v'),I("get.order",'desc'));
        $list = $this->setPage($re);
        if ($re[0]['id'] == null) {
            $this->ajaxReturn(array('list'=>[], 'count'=>0));
        } else {
            $this->ajaxReturn(array('list'=>$list, 'count'=>count($all)));
        }
        
    }

    public function getGroups(){
        $spread_id = I("get.id");
        $groups = D("Group")->getAllGoups($spread_id);

        $this->ajaxReturn($groups);
    }

    //==============================================//


    private function getDataAll($searchGroup){
        
        switch ($searchGroup) {
            case 'user_id': //获取推广部所有的员工

                if (!empty($this->group_id)) {
                    return $this->getGroupUser($this->group_id);
                } 


                if (!empty($this->spread_id)) {
                    return $this->getSpreadUser($this->spread_id);
                }


                $spreadDepartments = D("Department")->getSpreadDepartments("id,name");
                // $ids = array_column($spreadDepartments, 'id');
                $departStaff = array();
                
                foreach ($spreadDepartments as $key => $value) {
                    $ar = D("User")->getSpreadCommEmployee($value['id'], 'id, realname');
                    foreach ($ar as &$staff) {
                        $staff['name'] = $value['name']." - ".$staff['realname'];
                    }
                    $departStaff = array_merge($departStaff, $ar);
                }
                $re = $departStaff;
                break;
            case 'to_gid':
                if (!empty($this->spread_id)) {
                    // $groups = D("Group")->getAllGoups($this->spread_id, "id,name");
                    $re = D("Group")->join("left join department_basic as db on group_basic.department_id=db.id")
                              ->where(array('db.id'=>$this->spread_id,"group_basic.status"=>1))
                              ->field("group_basic.id, concat(db.name, ' - ',group_basic.name) as name")
                              ->select();
                } else {
                    $re = D("Group")->join("left join department_basic as db on group_basic.department_id=db.id")
                              ->where(array('db.type'=>DepartmentModel::SPREAD_DEPARTMENT,"db.status"=>1,"group_basic.status"=>1))
                              ->field("group_basic.id, concat(db.name, group_basic.name) as name")
                              ->select();
                }
                return $re;
                break;
            case 'spread_id':
                $re = D("Department")->getSpreadDepartments("id, name");
                break;
            default:
                $re = array();
                break;
        }
        return $re;
    }


    private function getGroupUser($g_id){
        return D("User")->getGroupEmployee($g_id, 'id, realname as name');
    }

    private function getSpreadUser($s_id){
        return D("User")->getDepartmentEmployee($s_id, 'id, realname as name');
    }

    private function getCAll($searchgroup){
        $this->M->where(array('created_at'=>array(array('EGT', $this->start), array('ELT', $this->end." 23:59:59"))));
        switch ($searchgroup) {
            case 'user_id':
                $this->M->field("count(customers_basic.id) as c, customers_basic.$searchgroup as obj_id")
                        ->group("customers_basic.$searchgroup");
                if (empty($this->group_id)) {
                    if (empty($this->spread_id)) {
                    $this->M->where(array("spread_id"=>array("NEQ", $this->spread_id)));
                    } else {
                        $this->M->where(array("spread_id"=>$this->spread_id));
                    }
                } else {
                    $this->M->join("left join user_info as ui on  customers_basic.user_id = ui.user_id")
                            ->where(array("ui.group_id"=>$this->group_id));
                }

                
                break;
            case 'to_gid':
                $this->M->join("left join user_info as ui on  customers_basic.user_id = ui.user_id")
                        ->field("count(customers_basic.id) as c, ui.group_id as obj_id")
                        ->group('ui.group_id');

                if (empty($this->spread_id)) {
                    $this->M->where(array("spread_id"=>array("NEQ", $this->spread_id)));
                } else {
                    $this->M->where(array("spread_id"=>$this->spread_id));
                }

                break;

            case 'spread_id':
                 $this->M->group($searchgroup)
                        ->field("count(customers_basic.id) as c, customers_basic.$searchgroup as obj_id")
                        ->where(array("spread_id"=>array("NEQ", 0)));
                break;
            
            default:
                $this->M->group($searchgroup)
                        ->field("count(customers_basic.id) as c, customers_basic.$searchgroup as obj_id")
                        ->where(array("spread_id"=>$this->spread_id));
                break;
        }
    }


    private function getDataVAll($searchgroup){
        $this->getCAll($searchgroup);
        $this->M->where(array('customers_basic.type'=>array(array('EQ','V'),array('EQ','VX'),array('EQ','VT'), 'OR')));
        return $this->M->select();
    }


    private function getDataContact($all,$numAll,$vAll){

        $cAll = arr_to_map($numAll, 'obj_id', 'c');
        $vAll = arr_to_map($vAll, 'obj_id', 'c');
       
        foreach ($all as $key => &$value) {
            if (isset($cAll[$value['id']])) {
                $value['c'] = $cAll[$value['id']];
            } else {
                $value['c'] = 0;
            }

            if (isset($vAll[$value['id']])) {
                $value['v'] = $vAll[$value['id']];
            } else {
                $value['v'] = 0;
            }
        }

        return $all;
    }
}