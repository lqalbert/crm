<?php
namespace Cli\Controller;

use Think\Controller;
use Home\Model\RoleModel;

class ImportAccountController extends Controller{

    const SPREAD_TWO_ID = 24;

    private $departIds = array(67,68);
    private $mapDepartIds = array("67"=>24,"68"=>23);

    private $groupIds = array(
            24,13,14,15,16,18,20,22,23, // 67
            28,29,30,31,32,39,40,41   // 68
        );
    private $mapGroupIds = array(
        "24"=>"",
        "13"=>"",
        "14"=>"",
        "15"=>"",
        "16"=>"",
        "18"=>"",
        "20"=>"",
        "22"=>"",
        "23"=>"",
        "28"=>"",
        "29"=>"",
        "30"=>"",
        "31"=>"",
        "32"=>"",
        "39"=>"",
        "40"=>"",
        "41"=>"" 
    );


    private function initmapGroupIds(){
        foreach ($this->departIds as $value) {
            $groups = $this->sourceM->query("select * from group_basic where department_id=".$value);
            foreach ($groups as $group) {
                $ngroup = M('group_basic')->where(array("name"=>$group['name'], 'department_id'=>$this->mapDepartIds[$value]))
                                          ->field('id')
                                          ->find();
                $this->mapGroupIds[$group['id']] = $ngroup['id'];
            }
        }
    }

    private function setRoleId(){
        $this->sp_master_id = D("Home/Role")->getIdByEname(RoleModel::SP_MASTER);
        $this->sp_captain_id = D("Home/Role")->getIdByEname(RoleModel::SP_CAPTAIN);
        $this->sp_staff_id = D("Home/Role")->getIdByEname(RoleModel::SP_STAFF);


        $this->sa_master_id = D("Home/Role")->getIdByEname(RoleModel::DEPARTMENTMASTER);
        $this->sa_captain_id = D("Home/Role")->getIdByEname(RoleModel::CAPTAIN);
        $this->sa_staff_id = D("Home/Role")->getIdByEname(RoleModel::STAFF);
    }


    public function index(){
        $this->sourceM = M('', null, 'mysql://run_crm_0326:run2008run@139.224.40.238:3306/run_crm_0326#utf8');
        $this->targetM = M();

        $this->initmapGroupIds();
        
        $this->setRoleId();

        $this->deal();  
        $this->setRoleS();

        $this->setZN();
    }


    private function deal(){
        foreach ($this->departIds as $value) {

            $users = $this->sourceM->query("select * from user_info inner join rbac_user on user_info.user_id=rbac_user.id where user_info.department_id=".$value);
            
            foreach ($users as $user) {
                //要设置权限 rbac_role_user
                //如果失败了怎么办 同名的情况 添加部门名再次导入？
                //设置部门 小组 这个好说

                $this->setDepartGroup($user);

                $this->addUser($user);

                // $this->setRole();

            }
        }
    }

    private function setDepartGroup(&$user){
        $user['department_id'] = $this->mapDepartIds[$user['department_id']];
        if (isset($this->mapGroupIds[$user['group_id']])) {
            $user['group_id']      = $this->mapGroupIds[$user['group_id']];
        } else {
            $user['group_id'] = 0;
        }
        

    }


    private function addUser($user){

        $user['old_id'] = $user['id'];
        unset($user['id']);
        if ($this->isUseAccount($user['account'])) {
            $user['account']=mb_substr(M("department_basic")->where(array("id"=>$user['department_id']))->getField("name"),0,2).$user['account'];
        }
        echo $user['account'];
        $data = M('rbac_user')->create($user);
        if (!$data) {
            echo " fail";
            echo "\n";
            return;
        }
        M("rbac_user")->startTrans(); 
        $id = M("rbac_user")->add();
        if (!$id) {
            M("rbac_user")->rollback(); 
            echo " fail";
            echo "\n";
            return;
        }
        $user['user_id'] = $id;
        $info = M("user_info")->add($user);
        if (!$info) {
            M("rbac_user")->rollback(); 
            echo " fail";
            echo "\n";
            return;
        }
        M("rbac_user")->commit(); 
        echo " success ";
        echo "\n";
    }

    private function isUseAccount($account){
        if (M("rbac_user")->where(array('account'=>$account))->find()) {
            return true;
        }
        return false;
    }

    private function setRoleS(){
        $newDeparts = array_values($this->mapDepartIds);
        $insert_arr = array();
        foreach ($newDeparts as $value) {
                
                $users = M("user_info")->where(array('department_id'=>$value))->select();
                foreach ($users as $user) {
                    if ($value == self::SPREAD_TWO_ID) {
                        if ($user['role_id'] == $this->sa_master_id) {
                            $this->setRole($user['user_id'], $this->sp_master_id);
                            $user['role_id'] = $this->sp_master_id;
                        } else if($user['role_id'] == $this->sa_captain_id) {
                            $this->setRole($user['user_id'], $this->sp_captain_id);
                            $user['role_id'] = $this->sp_captain_id;
                        } else if($user['role_id'] == $this->sa_staff_id){
                            $this->setRole($user['user_id'], $this->sp_staff_id);
                            $user['role_id'] = $this->sp_staff_id;
                        }
                    }

                    $insert_arr[] = array('role_id'=>$user['role_id'], 'user_id'=>$user['user_id']);

                }       
        }

        $M = D('rbac_role_user');
        $insert_result = $M->addAll($insert_arr);
    }

    private function setRole($user_id, $role_id){
        return M('user_info')->data(array('user_id'=>$user_id, 'role_id'=>$role_id))->save();
    }

    private function setZN(){
        $departs = array_values($this->mapDepartIds);
        foreach ($departs as $departId) {
            $users = M("user_info")->where(array('department_id'=>$departId,"role_id"=>array("IN", array($this->sp_master_id, $this->sp_captain_id))))->select();
            foreach ($users as $user) {
                if ($user['role_id'] == $this->sp_master_id) {
                    M("department_basic")->data(array('user_id'=>$user['user_id']))->where(array("id"=>$user['department_id']))->save();
                } elseif($user['role_id'] == $this->sp_captain_id){
                    M("group_basic")->data(array('user_id'=>$user['user_id']))->where(array("id"=>$user['group_id']))->save();
                }
            }
        }

    }

}