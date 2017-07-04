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


    public function delete($ids=array()){
        // return $this->where(array('id'=>array('in', $ids )))->save(array('status'=>-1));
        $id_arr = explode(",", $ids);
        $date   = Date('Y-m-d');
        $sql     = "update ".$this->tableName. " set `status`=-1, `account` = CONCAT(`account`, '_$date') where id=%d";
        $sql2    = "update user_info set  `realname` = CONCAT(`realname`, '_".$date."_离职') ,dimission_at='".Date('Y-m-d H:i:s')."' where user_id=%d";
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

    public function decoratorView($roleEname){
        $funcName = $roleEname."GetView";
        // var_dump($this);
        
        if (method_exists($this, $roleEname."GetView")) {
            return call_user_func(array($this, $funcName));
        } else {
            return $this->getView();
        }  
    }

    private function getView(){
      return array(
            'oprate' => '',
            'button' => ''
        );
    }

    //人事专员
    private function humanResourceGetView(){
        return $this->goldGetView();
    }
    //风控经理
   private function riskMasterGetView(){
        return $this->departmentMasterGetView();
    }


    private function serviceMasterGetView(){
      return $this->departmentMasterGetView();
    }

    //部门经理
     private function departmentMasterGetView(){
       $oprateColumn = <<<'EOD'
<el-table-column inline-template :context="_self"  fixed="right"  label="操作" width="220" align="center">
  <span>
    <el-button @click="handleEdit($index, row)"  size="small">编辑</el-button>
    <el-button @click="handleSetRoles($index, row)" type="info" size="small">职能</el-button>

  </span>
</el-table-column>
EOD;
      $addButton ='<el-button type="primary" size="small" @click="openDialog(\'editPassword\')">修改账号密码</el-button>';
 
        return array(
            'oprate' => $oprateColumn,
            'button' => $addButton
        );
    }

    private function hrMasterGetView(){
        return $this->humanResourceGetView();
    }


    private function goldGetView(){
      $oprateColumn = <<<'EOD'
<el-table-column inline-template :context="_self"  fixed="right"  label="操作" width="220" align="center">
  <span>
    <el-button @click="handleEdit($index, row)"  size="small">编辑</el-button>
    <el-button @click="handleSetRoles($index, row)" type="info" size="small">职能</el-button>
    <el-button @click="handleQuit($index, row)"  type="danger" size="small">离职</el-button>
  </span>
</el-table-column>
EOD;
      $addButton = <<< 'BUTTON'
<el-button type="primary" size="small" @click="openDialog('add')">添加员工</el-button>
<el-button type="primary" size="small" @click="openDialog('editPassword')">修改账号密码</el-button>
BUTTON;
      return array(
            'oprate' => $oprateColumn,
            'button' => $addButton
        );
    }


}