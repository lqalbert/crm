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


    public function delete($ids){
        // return $this->where(array('id'=>array('in', $ids )))->save(array('status'=>-1));
        $id_arr = explode(",", $ids);
        $date   = Date('Y-m-d');
        $sql    = "update ".$this->tableName. " set `status`=-1, `account` = CONCAT(`account`, '_$date') where id=%d";
        $this->startTrans();
        
        
        foreach ($id_arr as $key => $value) {
            $re = M()->execute($sql, $value);
            if ($re=== false) {
                $this->rollback();
                return false;
            }
        }

        $this->commit();
        return true;
    }
}