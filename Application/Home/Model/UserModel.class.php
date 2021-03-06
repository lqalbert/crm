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
             ->order('dimission_at desc')
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
             ->where(array('department_id'=>$depart_id, 'rbac_user.status'=>array('NEQ', RbacUserModel::DELETE_SATUS), 'user_info.role_id'=>array('IN',  array($roleModel->getIdByEname(RoleModel::CAPTAIN), $roleModel->getIdByEname(RoleModel::STAFF),$roleModel->getIdByEname(RoleModel::DEPARTMENTMASTER)))))
             ->field('id,account,realname,group_id')
             ->select();
    }

    public function getGroupEmployee($group_id, $field="id,account,realname"){
        if ($group_id==0) {
            return array();
        }

        $roleModel = new RoleModel;

        return $this->m->join('user_info on rbac_user.id = user_info.user_id')
             ->where(array('group_id'=>$group_id, 'rbac_user.status'=>array('NEQ', RbacUserModel::DELETE_SATUS)))
             ->field($field)
             ->select();
    }

    public function getHasCustomerEmployee($field="id,account,realname"){
        $roleModel = new RoleModel;
        // 部门经理 主管 员工
        return $this->m->join('user_info on rbac_user.id = user_info.user_id')
             ->where(array('rbac_user.status'=>
                array('NEQ', RbacUserModel::DELETE_SATUS),'user_info.role_id'=>
                    array('IN', array($roleModel->getIdByEname(RoleModel::CAPTAIN), $roleModel->getIdByEname(RoleModel::STAFF), $roleModel->getIdByEname(RoleModel::DEPARTMENTMASTER)))))
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
        return M('user_info')->join('rbac_user on user_info.user_id=rbac_user.id')->where(array('status'=>array('GT', RbacUserModel::DELETE_SATUS) ))->select();

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
        $roleGId = D("Role")->getIdByEname(RoleModel::RISKGROUP);
        
        $where['role_id']  = array('IN', array($roleId, $roleGId) );//   $roleId;
        $where['rbac_user.status']  = array('GT', RbacUserModel::DELETE_SATUS);
        return $this->cache('risk', 300)->m->join('user_info on rbac_user.id = user_info.user_id')
                ->where($where)
                ->field($field)
                ->select();
    }

    public function getCallback($field="id,realname"){
        $roleId  = D('Role')->getIdByEname(RoleModel::CALL_BACK);
        $roleGId = D('Role')->getIdByEname(RoleModel::CALLBACKCAPTAIN);

        $where['role_id']  = array('IN', array($roleId, $roleGId) ); 
        $where['rbac_user.status']  = array('GT', RbacUserModel::DELETE_SATUS);
        return $this->cache('callback', 300)->m->join('user_info on rbac_user.id = user_info.user_id')
                ->where($where)
                ->field($field)
                ->select();
    }

    public function getDataStaff($field="id,realname"){
        // D("Role") ==> D("Home/Role") 队列job报错了
        $roleId = D('Home/Role')->getIdByEname(RoleModel::DATASTAFF);
        $roleGId = D('Home/Role')->getIdByEname(RoleModel::DATACAPTAIN);
        $where['role_id']  = array('IN', array($roleId, $roleGId) ); 
        $where['rbac_user.status']  = array('GT', RbacUserModel::DELETE_SATUS);
        return $this->cache('datastaff', 300)->m->join('user_info on rbac_user.id = user_info.user_id')
                ->where($where)
                ->field($field)
                ->select();
    }

    public function getSpreadCommEmployee($spread_id, $field="user_id, realname"){
        $roleModel = new RoleModel;


        return $this->m->join('user_info on rbac_user.id = user_info.user_id')
             ->where(array('department_id'=>$spread_id, 'rbac_user.status'=>array('NEQ', RbacUserModel::DELETE_SATUS), 'user_info.role_id'=>array('IN',  array($roleModel->getIdByEname(RoleModel::SP_MASTER), $roleModel->getIdByEname(RoleModel::SP_CAPTAIN), $roleModel->getIdByEname(RoleModel::SP_STAFF)))))
             ->field($field)
             ->select();
    }

    public function getMaster($field="id,account"){
        //有 父级为 master的 角色的员工
        $masterId = M('rbac_role')->where(array('ename'=>RoleModel::MASTER))->find();
        $roleIds = M('rbac_role')->where(array('pid'=>$masterId['id']))->getField('id', true);
        if ($roleIds) {
            $user_ids = M('rbac_role_user')->where(array('role_id'=>array("IN", $roleIds)))->getField("user_id",true);
            if ($user_ids) {
                return $this->m->join('user_info on rbac_user.id = user_info.user_id')
                    ->where(array('id'=>array('IN', $user_ids), 'rbac_user.status'=>array('NEQ', RbacUserModel::DELETE_SATUS)))
                    ->field($field)
                    ->select();
            }
            
        }
        return array();
    }

    public function getGrouper($departId, $field="id,realname as name"){
        //父级为 groupCaptian
        $captianId = M('rbac_role')->where(array('ename'=>RoleModel::GROUPCAPTIAN))->find();
        $roleIds = M('rbac_role')->where(array('pid'=>$captianId['id']))->getField('id', true);
        if ($roleIds) {
            $user_ids = M('rbac_role_user')->where(array('role_id'=>array("IN", $roleIds)))->getField("user_id",true);
            if ($user_ids) {
                return $this->m->join('user_info on rbac_user.id = user_info.user_id')
                    ->where(array('department_id'=>$departId,  'id'=>array('IN', $user_ids), 'rbac_user.status'=>array('NEQ', RbacUserModel::DELETE_SATUS)))
                    ->field($field)
                    ->select();
            }
            
        }
        return array();
    }

}