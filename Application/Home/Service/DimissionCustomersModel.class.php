<?php
namespace Home\Service;
/**
* 只有部门经理才能操作离职员工的客户
* 所以需要部门 id 就行
*/
use Home\Model\RbacUserModel;
use Home\Model\CustomerModel;


class DimissionCustomersModel extends \Think\Model{
    protected $autoCheckFields = false;

    /**
    * 部门id
    */
    private $depart_id = 0;

    /**
    * user 模型
    */
    private $userModel = "";

    /**
    * customer 模型 
    */
    private $customerModel = "";


    /**
    * 查询条件
    */
    private $criteria = array();

    private $joins = array();


    /**
    *   离职员工id
    *
    */
    private $dimissionsId = array();


    public function __construct($id){
        

        $this->depart_id = $id;
        $this->userModel =  new RbacUserModel();
        $this->customerModel =  new CustomerModel();

        $this->setDimissionsId();
        // $this->addcriterion(array($fields=>array('IN', $this->dimissionsId)));
    }


    public function setDimissionsId(){
        $users = $this->userModel->getDepartmentDimissionEmployee($this->depart_id);

        $this->dimissionsId = array_column($users, 'id');

    }

    public function addcriterion($item){
        $this->criteria = array_merge($this->criteria, $item);
    }

    public function applyCriteria(){
        return $this->customerModel->where($this->criteria);
    }

    public function addJoins($str){
        $this->joins[] = $str;
       
    }

    public function applyJoin(){
        
        foreach ($this->joins as $value) {
            $this->customerModel->join($value);
            
        }
    }

    public function getDimissionsId(){
        return $this->dimissionsId;
    }


    public function getList($page, $size = 10){
        $count = count($this->dimissionsId);
        if ($count != 0) {

            $recount = $this->applyCriteria()->count();
            
            $this->applyJoin();
            $list = $this->applyCriteria()->join('left join customers_contacts as cc on (customers_basic.id = cc.cus_id and cc.is_main=1)')
                                          ->field("customers_basic.*, user_info.realname, cc.phone,cc.qq,cc.weixin")
                                          ->page($page. ','. $size)
                                          ->order('id desc')
                                          ->select();   
            
            return array('list'=>$list, 'count'=>$recount);
        } else {
            return array('list'=>[], 'count'=>0);
        }
    }  
}