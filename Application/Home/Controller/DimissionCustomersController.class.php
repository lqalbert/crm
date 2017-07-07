<?php
namespace Home\Controller;


use Home\Service\DimissionCustomersModel;

class DimissionCustomersController extends CommonController {

    protected $table = "";

    private $model = null;

    private $department_id = "";

    protected $pageSize = 10;
    
    public function _initialize(){
        parent::_initialize();
        $this->department_id = session('account')['userInfo']['department_id'];
        $this->model = new DimissionCustomersModel($this->department_id);
        
    }

    public function index(){

        $this->assign('users', $this->getGeneralEmployee());
        $this->assign('dUsers', $this->getDUser());
        $this->assign('groups', $this->getGroups());

        $this->display();
    }



    public function getList(){
        if (isset($_GET['user_id'])) {
           $this->model->addcriterion(array('salesman_id'=>I('get.user_id')));
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

    //存储层
    private function getGeneralEmployee(){
        return D('User')->getGeneralEmployee($this->department_id);
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