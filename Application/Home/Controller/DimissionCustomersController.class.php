<?php
namespace Home\Controller;

use Home\Model\DepartmentModel;
use Home\Service\DimissionCustomersModel;

class DimissionCustomersController extends CommonController {

    protected $table = "";

    private $model = null;

    private $department_id = "";

    protected $pageSize = 7;
    
    public function _initialize(){
        parent::_initialize();
        $this->department_id = session('account')['userInfo']['department_id'];
        $this->model = new DimissionCustomersModel($this->department_id);

        $departRow = $this->getDepartRow($this->department_id);
        
        if ($this->isSpread($departRow)) {
            $this->model->addcriterion(array('customers_basic.user_id'=>array('IN', $this->model->getDimissionsId())));
        } else {
            $this->model->addcriterion(array('customers_basic.salesman_id'=>array('IN',  $this->model->getDimissionsId())));
        }
    }

    private function getDepartRow($id){
        return D("Department")->find($id);
    }

    private function isSpread($row){
        return DepartmentModel::SPREAD_DEPARTMENT == $row['type'];
    }

    

    public function index(){

        $this->assign('users', $this->getGeneralEmployee());
        $this->assign('dUsers', $this->getDUser());
        $this->assign('groups', $this->getGroups());

        $this->display();
    }

    public function index2(){

        $this->assign('users', $this->getSpreadGeneralEmployee());
        $this->assign('dUsers', $this->getDUser());
        $this->assign('groups', $this->getGroups());
        $this->_before_index();

        $this->display();
    }



    public function getList(){

        if (isset($_GET['spread'])) {
            $this->model->addJoins("left join user_info on customers_basic.user_id=user_info.user_id");
        } else {
            $this->model->addJoins("left join user_info on customers_basic.salesman_id=user_info.user_id");
        }

        if (isset($_GET['user_id'])) {
            if (isset($_GET['spread'])) {
                $this->model->addcriterion(array('customers_basic.user_id'=>I("get.user_id")));
            } else {
                $this->model->addcriterion(array('customers_basic.salesman_id'=>I('get.user_id'))); 
            }
        }
        if (isset($_GET['size'])) {
            $this->pageSize = I('get.size');
        }
        
        $this->ajaxReturn($this->model->getList(I('get.p',0), $this->pageSize));
    }

    


    public function update(){
        $cus_ids      = I('post.cus_id');
        $salesman_id  = I('post.salesman_id');
        
        //转移到  服务层 里面去
        $re = D('Customer')->where(array('id'=>array('IN', $cus_ids)))->data(array('salesman_id'=>$salesman_id, 'service_time'=>time()))->save();

        if ($re) {
            $this->success();
        } else {
            $this->error(D('Customer')->getLastsql());
        }

    }


    public function update2(){
        $cus_ids      = I('post.cus_id');
        $salesman_id  = I('post.salesman_id');
        
        
        //生成纪录
        $newMan = M("user_info")->field("realname")->where(array("user_id"=>$salesman_id))->find();
        foreach ($cus_ids as $value) {
            $row = M("customers_basic")->field("user_id")->where(array("id"=>$value))->find();
            $oldMan = M("user_info")->field("realname")->where(array("user_id"=>$row['user_id']))->find();
            M('customers_log')->add(array(
                'cus_id'=>$value,
                'user_id'=>session('uid'),
                'track_text'=>"锁定人转让",
                'content'=>"原锁定人员：".$oldMan['realname']."改为：".$newMan['realname']));
        }

        //转移到  服务层 里面去
        $re = D('Customer')->where(array('id'=>array('IN', $cus_ids)))->data(array('user_id'=>$salesman_id, 'service_time'=>time()))->save();

        if ($re) {
            $this->success();
        } else {
            $this->error(D('Customer')->getLastsql());
        }

    }

    //存储层
    private function getGeneralEmployee(){
        return D('User')->getGeneralEmployee($this->department_id);
    }

    //存储层
    private function getSpreadGeneralEmployee(){
        return D('User')->getSpreadCommEmployee($this->department_id, 'id,account,realname,group_id');
    }



    

    private function getDUser(){
        $re =  D('User')->getDepartmentDimissionEmployee($this->department_id);
        return $re ? $re : array();
    }

    private function getGroups(){
        $re =  D('Group')->getAllGoups($this->department_id, 'id,name');
        return $re ? $re : array();
    }
}