<?php
namespace Home\Controller;

use Home\Model\DistributeModel;
use Home\Model\DistributeCustomerModel;

class DistributeController extends CommonController{

    protected $table = "Distribute";
    private $nameMap = array(
        'gold' => '部门',
        'departmentMaster' => '小组',
        'captain' => '员工'
    );


    public function index(){

        $this->assign("types", DistributeModel::$type);
        $this->display();   
    }

    public function edit(){
        // $data = $_POST;
        $data       = array();
        $data['id'] = I("post.id");
        $config     = array();
        $config['limina'] = I("post.limina");
        $config['type']   = I("post.type");
        $config['list']   = I("post.percent");
        foreach ($config['list'] as $key => &$value) {
            unset($value['key']);
        }
        $data['config'] = json_encode($config);

        if ($this->M->create($data, 2) && ($this->M->save() !== false) )  {
            $this->success(L('EDIT_SUCCESS'));
        } else {
            $this->error($this->M->getError());
        }
    }

    public function view(){
        $this->setRole();

        $this->assign("benefits", DistributeCustomerModel::$shareBenefit);
        $this->assign("pageSize", 0);
        $this->display("_view");
    }

    private function setRole(){
        $roleName = $this->getRoleEname();
        $this->assign('name', $this->nameMap[$roleName]);
        $funcName = $roleName."Condition";
        if (method_exists($this, $funcName)) {
           call_user_func(array($this, $funcName));
        } else {
          $this->commonCondition();
        }
    }

    private function commonCondition(){
        $this->assign("optionList", "[]");
        $this->assign("id",     0);
        $this->assign("limina", 0);
        $this->assign("type",   "1");
        $this->assign("percent", "[]");
    }

    private function setRow($row){
        $config = json_decode($row['config'], true);
        if ($row) {
            
            foreach ($config['list'] as $key => &$value) {
                $value['value'] = intval($value['value']);
            }
            $this->assign("id",     $row['id']);
            $this->assign("limina", $config['limina']);
            $this->assign("type",   $config['type']);
            $this->assign("percent", json_encode($config['list']));
        } else {
            $this->assign("id",     0);
            $this->assign("limina", 0);
            $this->assign("type",   "1");
            $this->assign("percent", "[]");
        }
    }


    private function goldCondition(){
        $row = $this->M->where(array('obj_id'=>0))->find();
        $this->assign("optionList", D("Department")->getGoodSalesDepartments("id,name"));
        $this->setRow($row );

        $re = F(DistributeCustomerModel::BENEFIT);
        $this->assign('benefit', $re['benefit']);
        $this->assign('gold', true);
    }

    private function departmentMasterCondition(){
        $depart_id = session('account')['userInfo']['department_id'];
       
        $row = $this->M->where(array('obj_id'=>$depart_id, "type"=>1))->find();
        $this->assign("optionList", D("Group")->getAllGoups($depart_id,"id,name"));
        $this->setRow($row);
        $this->assign('gold', false);
    }

    private function captainCondition(){
        $group_id = session('account')['userInfo']['group_id'];
       
        $row = $this->M->where(array('obj_id'=>$group_id, "type"=>2))->find();
        $this->assign("optionList", D("User")->getGroupEmployee($group_id,"id,realname as name"));
        $this->setRow($row);
        $this->assign('gold', false);
    }

    public function manually(){

        $this->setManullyConfig();
        $this->assign("pageSize", 0);
        $this->display();
    }



    private function setManullyConfig(){
        $roleName = $this->getRoleEname();
        $this->assign('name', $this->nameMap[$roleName]);
        $funcName = $roleName."ManuallyCondition";
        if (method_exists($this, $funcName)) {
           call_user_func(array($this, $funcName));
        } else {
          $this->commonManuallyCondition();
        }
    }

    private function commonManuallyCondition(){
        $this->assign("total", 0);
    }

    private function departmentMasterManuallyCondition(){
        $depart_id = session('account')['userInfo']['department_id'];
        $this->assign("optionList", D("Group")->getAllGoups($depart_id,"id,name"));
        $count = D("Customer")->where(array("depart_id"=>$depart_id,  'to_gid'=>0))->count();
        $this->assign("total", $count);
    }

    private function captainManuallyCondition(){
        $group_id = session('account')['userInfo']['group_id'];
        $this->assign("optionList", D("User")->getGroupEmployee($group_id,"id,realname as name"));
        $count = D("Customer")->where(array("to_gid"=>$group_id, 'salesman_id'=>0))->count();
        $this->assign("total", $count);
    }

    public function manuallyDistribute(){
        // $list   = I("post.percent");
        $re =  $this->dealDistribute(I("post.percent"));
        $this->ajaxReturn($re);
    }

    private function dealDistribute($list){
        $roleName = $this->getRoleEname();
        $funcName = $roleName."ManuallyDeal";
        if (method_exists($this, $funcName)) {
           return call_user_func(array($this, $funcName),$list);
        } 
        return array();
    }

    private function departmentMasterManuallyDeal($list){
        $depart_id = session('account')['userInfo']['department_id'];
        $allids = D("Customer")->where(array('depart_id'=>$depart_id, 'to_gid'=>0))->getField("id", true);

        $record_id = M('distribute_record')->add(array(
                    'type' => 1,
                    'obj_id' => $depart_id,
                    'num' => I("post.total")
                ));

        $re = array();
        foreach ($list as $key => &$value) {
            $tmp = array();
            $tmp['id'] = $value['id'];
            $value['ids'] = array_slice($allids, 0 , $value['value']);

            $allids = array_diff($allids, $value['ids']);
            if ($value['ids']) {
                $sql = "update customers_basic set to_gid=".$value['id'].",dis_time='".Date('Y-m-d H:i:s')."' where id in (".implode(",", $value['ids']).")";
                $tmp['num'] = M()->execute($sql);
            } else {
                $tmp['num'] = 0;
            }
            

            $re[] = $tmp;


            M('distribute_detail')->add(array(
                'record_id' => $record_id,
                'name'      => D("Group")->where(array('id'=>$value['id']))->getField("name"),
                'value'     => count($value['ids'])
            ));
        }
        return $re;


    }

    private function captainManuallyDeal($list){
        $g_id = session('account')['userInfo']['group_id'];
        $allids = D("Customer")->where(array('to_gid'=>$g_id, 'salesman_id'=>0))->getField("id", true);


        $record_id = M('distribute_record')->add(array(
                    'type' => 2,
                    'obj_id' => $g_id,
                    'num' => I("post.total")
                ));

        $re = array();
        foreach ($list as $key => &$value) {
            $tmp = array();
            $tmp['id'] = $value['id'];
            $value['ids'] = array_slice($allids, 0 , $value['value']);

            $allids = array_diff($allids, $value['ids']);
            if ($value['ids']) {
                $sql = "update customers_basic set salesman_id=".$value['id'].",dis_time='".Date('Y-m-d H:i:s')."' where id in (".implode(",", $value['ids']).")";
                $tmp['num'] = M()->execute($sql);
            } else {
                $tmp['num'] = 0;
            }
            

            $re[] = $tmp;


            M('distribute_detail')->add(array(
                'record_id' => $record_id,
                'name'      => M("user_info")->where(array('user_id'=>$value['id']))->getField("realname"),
                'value'     => count($value['ids'])
            ));
        }
        return $re;

    }

    public function saveBenefit(){
        $re = F(DistributeCustomerModel::BENEFIT, $_POST);
        $this->success("保存成功");
        /*if ($re) {
           
        } else {
            $this->error("失败");
        }*/
        //$Data = F('data');
    }


}