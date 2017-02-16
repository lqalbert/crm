<?php
namespace Home\Model;
use Think\Model\RelationModel;

class RbacUserModel extends RelationModel {
    protected $tableName = 'rbac_user';

    protected $_validate = array(
     array('account','require','账号必须！', self::MUST_VALIDATE, '' ,self::MODEL_INSERT), //默认情况下用正则进行验证
     array('account','','账号已存在！',        self::MUST_VALIDATE, 'unique'  ,self::MODEL_INSERT), //默认情况下用正则进行验证
     array('password','require','密码必须！', self::MUST_VALIDATE, '' ,self::MODEL_INSERT), 
   );

    protected $_link = array(
        'userInfo'=> array(
        	'mapping_type'      => self::HAS_ONE,
        	'class_name'        => 'userInfo',
        	'foreign_key'       => 'user_id'
        )
     );

     protected $_auto = array ( 
         array('password','cryptPawssword',3,'callback') , // 对password字段在新增和编辑的时候使md5函数处理
     );


    public function cryptPawssword($p){
        return md5($p);
    }

    /**
    * 重载add
    *
    *
    */
    /*public function add($data='',$options=array(),$replace=false){
    	// $this->password = $this->cryptPawssword($this->password);
    	return parent::add($data, $options,$replace);
    }*/
}