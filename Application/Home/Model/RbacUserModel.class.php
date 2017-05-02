<?php
namespace Home\Model;
use Think\Model\RelationModel;

class RbacUserModel extends RelationModel {

    const  DELETE_SATUS = -1;

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
        $sql     = "update ".$this->tableName. " set `status`=-1, `account` = CONCAT(`account`, '_$date') where id=%d";
        $sql2    = "update user_info set  `realname` = CONCAT(`realname`, '_$date_删除') where user_id=%d";
        $this->startTrans();
        
        
        foreach ($id_arr as $key => $value) {
            $re = M()->execute($sql, $value);
            $re2= M()->execute($sql2, $value);
            if ($re === false || $re2 === false) {
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }


    /**
    * @param int $depart_id
    *
    * @return array
    */
    public function getDepartmentDimissionEmployee($depart_id){
        return $this->join('user_info on rbac_user.id = user_info.user_id')
             ->where(array('department_id'=>$depart_id, 'rbac_user.status'=>array('EQ', self::DELETE_SATUS)))
             ->field('id,account,realname')
             ->select();
    }
}