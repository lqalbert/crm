<?php
namespace Cli\Controller;

use Think\Controller;
use Home\Model\RoleModel;

class SaleToSpreadController extends Controller{

    private $ids = array();


    public function index(){

    }

    private function setRoleId(){
        $this->sp_master_id = D("Home/Role")->getIdByEname(RoleModel::SP_MASTER);
        $this->sp_captain_id = D("Home/Role")->getIdByEname(RoleModel::SP_CAPTAIN);
        $this->sp_staff_id = D("Home/Role")->getIdByEname(RoleModel::SP_STAFF);


        $this->sa_master_id = D("Home/Role")->getIdByEname(RoleModel::DEPARTMENTMASTER);
        $this->sa_captain_id = D("Home/Role")->getIdByEname(RoleModel::CAPTAIN);
        $this->sa_staff_id = D("Home/Role")->getIdByEname(RoleModel::STAFF);
    }

    public function departments($ids){
        $departments = D("Home/Department")->where(array("id"=>array("IN", $ids)))->select();
        $sql="update department_basic set type=4,config=null where id in (". implode(',', $ids) .")";
        M()->execute($sql);
        foreach ($departments as $key => $value) {
            // rbac_role_user 重新处理 
            // M('user_info')->data(array('user_id'=>$user_id, 'role_id'=>$role_ids))->save();
            $users = M('user_info')->where(array('department_id'=>$value['id']));
            foreach ($users as $user) {
                if ($user['role_id'] == $this->sa_master_id) {
                    $this->setRole($user['user_id'], $this->sp_master_id);
                } else if($user['role_id'] == $this->sa_captain_id) {
                    $this->setRole($user['user_id'], $this->sp_captain_id);
                } else if($user['role_id'] == $this->sa_staff_id){
                    $this->setRole($user['user_id'], $this->sa_staff_id);
                } 
            }           
        }

    }

    private function setRole($user_id, $role_id){
        $M = D('rbac_role_user');
        $insert_list = array();
        $insert_list[] = array('role_id'=>$role_id, 'user_id'=>$user_id);

        $M->startTrans(); 
        $result = $M->where(array('user_id'=>$user_id))->delete();

        $insert_result = $M->addAll($insert_list);
        $re = M('user_info')->data(array('user_id'=>$user_id, 'role_id'=>$role_id))->save();
        $M->commit();
    }
}