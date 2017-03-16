<?php
namespace Common\Lib;

use Common\Lib\RoleCaptain;
use Common\Lib\RoleDepartmentMaster;
use Common\Lib\RoleStaff;
use Common\Lib\RoleGold;

class User {
    private $id = 0;

    private $info = "";

    private $roleObject = null;

    public function __construct($current=null){
        if ($current === null) {
            $this->id = session('uid');
            $this->info = session('account')['userInfo'];
        } else {
            $this->id = $current;
            $this->info = M('user_info')->find($this->id);
        }
    }

    public function __get($name){
        if (isset($this->$name)) {
            return $this->$name;
        }
    }



    public function getRole(){
        return M('rbac_role')->find($this->info['role_id']);
    }

    public function getRoleObject(){
        if (!$this->roleObject) {
            $row = $this->getRole();
            $className = __NAMESPACE__.'\\'.'Role'.ucfirst($row['ename']);
            $this->roleObject = new $className;
        }
        return $this;
    }

    public function getRoleGroupContacts($id){
        return $this->roleObject->getGroupContacts($this, $id);
    }

    public function getRoleGroupOrgs(){
        return $this->roleObject->getGroupUpsOrg($this);
    }

    /**
    * 获取备选的角色 、职能
    */ 
    public function getEmployeeRoleList(){
        return $this->roleObject->getEmployeeRoleList($this->info['role_id']);
    }

    /**
    * 
    */
    public function setEmployeeAddData(){
        $this->roleObject->setEmployeeAddData($this);
    }

    public function setEmployQueryCondition($m){
        $this->roleObject->setEmployQueryCondition($m, $this);
    }

    public function setGroupQueryCondition($m){
        $this->roleObject->setGroupQueryCondition($m, $this);
    }

    /**
    * group 待选的队员
    * (所有的队员)
    */
    public function getAllBenC(){
        return $this->roleObject->getAllBenC($this);
    }


    public function setMemberUserCondition($m){
        $this->roleObject->setMemberUserCondition($m);
    }

    public function getCustomerSearchGroup($arr){
        
        return $this->roleObject->getCustomerSearchGroup($arr);
    }


}