<?php
namespace Home\Controller;



class SpreadCustomerSortController extends CommonController{

    protected $table = "Customer";
    protected $pageSize = 11;
  
    public function index(){

        $this->assign('searchGroup', array(array("name"=>"个人", "value"=>"user_id"), array("name"=>"小组", "value"=>"to_gid"), array("name"=>"部门", "value"=>"spread_id")));

        $this->assign("allDeparts", $this->getSpreadDepartment());
        $this->display();
    }

    private function getSpreadDepartment(){
        return D("Department")->getSpreadDepartments();
    }

    public function setQeuryCondition(){

        $start = I("get.start");
        $end   = I("get.end");

        $this->M->where(array('created_at'=>array(array('EGT', $start), array('ELT', $end." 23:59:59"))));

        $searchGroup = I("get.searchgroup");
        $this->M->group($searchgroup);

        $this->M->field("count(id) as c, $searchGroup as obj_id");

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
    }  

    private function getAll(){
        $this->setQeuryCondition();
        return $this->M->select();
    }

    private function getVAll(){
        $this->setQeuryCondition();
        $this->M->where(array('type'=>array(array('EQ','V'),array('EQ','VX'),array('EQ','VT'), 'OR')));
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
        $all = $this->getAll();
        $vall = $this->getVAll();
        $re = $this->setContact($all, $vall);
        $this->setOrder($re, I("get.field", 'v'),I("get.order",'desc'));
        $list = $this->setPage($re);
        if ($all[0]['obj_id'] == null) {
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
}