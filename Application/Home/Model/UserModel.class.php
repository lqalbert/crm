<?php
namespace Home\Model;

use Think\Model;
// use RbacUserModel;
use RoldeMode;

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

    public function getDepartmentEmployee($depart_id, $field="id,account,realname"){
        return $this->m->join('user_info on rbac_user.id = user_info.user_id')
             ->where(array('department_id'=>$depart_id, 'rbac_user.status'=>array('NEQ', RbacUserModel::DELETE_SATUS)))
             ->field($field)
             ->select();
    }

    public function getGeneralEmployee($depart_id){
        $roleModel = new RoleModel;


        return $this->m->join('user_info on rbac_user.id = user_info.user_id')
             ->where(array('department_id'=>$depart_id, 'rbac_user.status'=>array('NEQ', RbacUserModel::DELETE_SATUS), 'user_info.role_id'=>array('IN',  array($roleModel->getIdByEname(RoleModel::CAPTAIN), $roleModel->getIdByEname(RoleModel::STAFF)))))
             ->field('id,account,realname')
             ->select();
    }

    public function getGroupEmployee($group_id, $field="id,account,realname"){
        $roleModel = new RoleModel;

        return $this->m->join('user_info on rbac_user.id = user_info.user_id')
             ->where(array('group_id'=>$group_id, 'rbac_user.status'=>array('NEQ', RbacUserModel::DELETE_SATUS)))
             ->field($field)
             ->select();
    }

    /**
    * 未分配的hr
    */
    public function getUnSHr(){
        
        M('user_info')->where(array('department_id'=>0));
        $roleId = D('Role')->getIdByEname(RoleModel::HR);
        if ($roleId) {
            M('user_info')->where(array('role_id'=>$roleId));
        }
        return M('user_info')->select();

    }
    //分配的
    public function getSnHr($id, $field='user_id,realname'){
        $roleId = D('Role')->getIdByEname(RoleModel::HR);
        return M('user_info')->where(array('role_id'=>$roleId, 'department_id'=>$id))->field($field)->select();
    }

    public function getDM($id=0){ 
         $roleId = D('Role')->getIdByEname(RoleModel::DIVISIONMASTER);
        if ($id!=0) {
            if ($roleId) {

                $where['role_id']  = $roleId;
                $where['department_id']  = 0;
                
                $map['_complex'] = $where;
                $map['user_id']  = $id;
                $map['_logic'] = 'or';
                M('user_info')->where($map);
            }
        } else {
           
            if ($roleId) {
                M('user_info')->where(array('role_id'=>$roleId, 'department_id'=>0));
            }
        }


        return M('user_info')->select();
    }
 

    public function getSupService($field="id,realname"){
        $roleId = D('Role')->getIdByEname(RoleModel::SUP_SERVICE);
        if ($roleId) {

                $where['role_id']  = $roleId;
                $where['rbac_user.status']  = array('GT', RbacUserModel::DELETE_SATUS);
                return $this->m->join('user_info on rbac_user.id = user_info.user_id')
                        ->where($where)
                        ->field($field)
                        ->select();
        } else {
            return array();
        }
    }

    public function getGenService($field="id,realname"){
        $roleId = D('Role')->getIdByEname(RoleModel::GEN_SERVICE);
        if ($roleId) {

                $where['role_id']  = $roleId;
                $where['rbac_user.status']  = array('GT', RbacUserModel::DELETE_SATUS);
                return $this->m->join('user_info on rbac_user.id = user_info.user_id')
                        ->where($where)
                        ->field($field)
                        ->select();
        } else {
            return array();
        }
    }

    public function getRisk($field="id,realname"){
        $roleId = D('Role')->getIdByEname(RoleModel::RISK_ONE);
        if ($roleId) {

                $where['role_id']  = $roleId;
                $where['rbac_user.status']  = array('GT', RbacUserModel::DELETE_SATUS);
                return $this->cache('callback', 300)->m->join('user_info on rbac_user.id = user_info.user_id')
                        ->where($where)
                        ->field($field)
                        ->select();
        } else {
            return array();
        }
    }

    public function getCallback($field="id,realname"){
        $roleId = D('Role')->getIdByEname(RoleModel::CALL_BACK);
        if ($roleId) {

                $where['role_id']  = $roleId;
                $where['rbac_user.status']  = array('GT', RbacUserModel::DELETE_SATUS);
                return $this->cache('callback', 300)->m->join('user_info on rbac_user.id = user_info.user_id')
                        ->where($where)
                        ->field($field)
                        ->select();
        } else {
            return array();
        }
    }

}