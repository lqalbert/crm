<?php
namespace Home\Model;

use Think\Model;
// use RbacUserModel;
// use RoldeMode;

class UserModel extends  Model{
    protected $autoCheckFields = false;

    private $m = null;

    public function __construct(){
        $this->m = new RbacUserModel;

    }


    /**
    * @param int $depart_id
    *
    * @return array
    */
    public function getDepartmentDimissionEmployee($depart_id){
        return $this->m->join('user_info on rbac_user.id = user_info.user_id')
             ->where(array('department_id'=>$depart_id, 'rbac_user.status'=>array('EQ', RbacUserModel::DELETE_SATUS)))
             ->field('id,account,realname')
             ->select();
    }

    public function getGeneralEmployee($depart_id){
        $roleModel = new RoleModel;


        return $this->m->join('user_info on rbac_user.id = user_info.user_id')
             ->where(array('department_id'=>$depart_id, 'rbac_user.status'=>array('NEQ', RbacUserModel::DELETE_SATUS), 'user_info.role_id'=>array('IN',  array($roleModel->getIdByEname(RoleModel::CAPTAIN), $roleModel->getIdByEname(RoleModel::STAFF)))))
             ->field('id,account,realname')
             ->select();
    }



}