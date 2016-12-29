<?php
namespace Home\Model;
use Think\Model;
class RbacUserModel extends Model {
    protected $tableName = 'rbac_user';


    public function cryptPawssword($p){
    	return md5($p);
    }

    /**
    * 重载add
    *
    *
    */
    public function add(){
    	$this->password = $this->cryptPawssword($this->password);
    	return parent::add();
    }
}