<?php
namespace Cli\Controller;

use Think\Controller;
use Home\Model\RoleModel;

class SaleToSpreadController extends Controller{

    private $ids = array(6,9,11,13,14,15,16,17);


    public function index(){
        // echo "is_has 清了吗？"
        // die();
        $this->mark = Date("Y-m-d");
        $this->setRoleId();
        $this->departments($this->ids);
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
            $users = M('user_info')->where(array('department_id'=>$value['id']))->select();
            
            foreach ($users as $user) {
                if ($user['role_id'] == $this->sa_master_id) {
                    $this->setRole($user['user_id'], $this->sp_master_id);
                } else if($user['role_id'] == $this->sa_captain_id) {
                    $this->setRole($user['user_id'], $this->sp_captain_id);
                } else if($user['role_id'] == $this->sa_staff_id){
                    $this->setRole($user['user_id'], $this->sp_staff_id);
                }
                $this->setCustomers($user['user_id'], $value['id'], $user['group_id']);
                $this->setUserOn($user['user_id']);

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
    //只假设一种情况 这些客户都在一个部门内转让
    private function setCustomers($user_id, $depart_id ,$gid){
        // $sql = "update customers_basic set salesman_id=0 ,spread_id=$depart_id where user_id=$user_id ";

        $sql = "update customers_basic set spread_id=$depart_id,depart_id=$depart_id, to_gid=$gid, olde_mark='".$this->mark."' where user_id=$user_id ";
        M()->execute($sql);

    }

    private function setUserOn($user_id){
        $sql = "update rbac_user set status=1 where id=$user_id ";
        M()->execute($sql);
    }
}