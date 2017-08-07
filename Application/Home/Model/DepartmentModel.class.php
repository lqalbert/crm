<?php
namespace Home\Model;
use Think\Model;
use Home\Model\RoleModel ;

class DepartmentModel extends Model {

    /*const CAREER = 2;
    const GENERALIZE = 3;*/

    /**
    * 兼容以前的
    */
    const CAREER = 0;
    const GENERALIZE = 0; 

    //现在的
    // 部门类型 
    //销售部
    const SALES_DEPARTMENT = 0 ;
    //客服部
    const CUSTOMER_SERVICE = 1 ;
    //风控部
    const RISK_DEPARTMENT  = 2 ;
    const HR_DEPARTMENT    = 3;
    const SPREAD_DEPARTMENT = 4;

	protected $tableName = 'department_basic';

	
    protected $_validate = array(
         array('name','','部门名称已存在',0,'unique'), // 
   );

	private $types = array(
		0=>'销售部',
        1=>'客服部',
        2=>'风控部',
        3=>'人事部',
        4=>'推广部'
	);




	/**
    * 返回类型 或 所有的类型
    * 
    * @return string|array
    */
    public function getType($index=null){
    	if (is_int($index)) {
    		return $this->types[$index];
    	} else {
    		return $this->types;
    	}
    }


    /**
    * @param int|array  department_id 
    *
    * @return []
    */
    public function getAllDepartments($field=null){
        $this->where(array('status'=>1));

        if (!empty($field)) {
            $this->field($field);
        }
        
        return $this->select();
    }

    /**
    * 获得指定部门
    * @param int|array
    *
    * @return []
    */
    public function getTheDepartments($id,$field=null){
        $this->where(array('status'=>1));

        if (!empty($field)) {
            $this->field($field);
        }
        
        if (is_numeric($id) && $id!=0) {
          $this->where(array('id'=>$id));
        } else if(is_array($id)){
          $this->where(array('id'=>array('IN', $id)));
        }

        return $this->select();
    }


    public function delete($ids){
        $groups = D('Group')->where(array('department_id'=>array('in', $ids)))->getField('id', true);
        D('Group')->delete($groups);
        return $this->where(array('id'=>array('in', $ids )))->save(array('status'=>-1, 'user_id'=>null));
    }

    /**
    * 获得销售部门
    * @param int|array
    *
    * @return []
    */
    public function getSalesDepartments($fields="id,name", $status=-1){
        return $this->where(array('type'=>self::SALES_DEPARTMENT, 'status'=> array('NEQ', $status)))->field($fields)->select();
    }

    /**
    * 跟据不同的权限  index.html 显示不同的内容
    * 列表的操作列
    * 添加按钮
    * @parame string 
    *
    * @return array
    */

    public function decoratorView($roleEname){
      $funcName = $roleEname."GetView";
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

    private function goldGetView(){
      $oprateColumn = <<<'EOD'
<el-table-column  :context="_self"  align="center" width="250" fixed="right"  label="操作"  >
  <template scope="scope">
    <el-button type="success" @click="handleEdit(scope.$index, scope.row)"     size="small">编辑</el-button>
    <el-button type="danger"  @click="handleDelete(scope.$index, scope.row)"   size="small" >删除</el-button>
    <el-button type="info" @click="outPut(scope.row.id)" size="small"> 导出人员 </el-button>
  </template>
</el-table-column> 
EOD;
      $addButton = <<< 'BUTTON'
<el-tooltip content="添加新的组织单位！" placement="right">
    <el-button size="small"  @click="openDialog('add')" icon="plus" type="primary">添加</el-button>
</el-tooltip>

<el-tooltip content="分配人事专员" placement="right">
    <el-button size="small"  @click="openDialog('hr')"  type="primary">人事专员</el-button>
</el-tooltip>
BUTTON;
     
      return array(
            'oprate' => $oprateColumn,
            'button' => $addButton
        );
    }

    private function hrMasterGetView(){
        $oprateColumn = <<<'EOD'
<el-table-column  :context="_self"  align="center" width="80" fixed="right"  label="操作"  >
  <template scope="scope">
    <el-button type="success" @click="handleEdit(scope.$index, scope.row)"     size="small">编辑</el-button>
    
  </template>
</el-table-column> 
EOD;
      $addButton = <<< 'BUTTON'
<el-tooltip content="分配人事专员" placement="right">
    <el-button size="small"  @click="openDialog('hr')"  type="primary">人事专员</el-button>
</el-tooltip>
BUTTON;
      return array(
            'oprate' => $oprateColumn,
            'button' => $addButton
        );
    }

    public function getEmployeeRoles($type){
        switch ($type) {
            case 1:
                //客服部
                $re = D("Role")->where(array('ename'=>array(
                    array('eq',RoleModel::SUP_SERVICE),
                    array('eq',RoleModel::GEN_SERVICE), 
                    array('eq',RoleModel::RISK_ONE),
                    array('eq',RoleModel::CALL_BACK),
                    array('eq',RoleModel::DATASTAFF),
                    'or')))->select();
                break;
            case 2:
                //风控部
                $re = D("Role")->where(array('ename'=>array(
                    array('eq',RoleModel::RISK_ONE),array('eq',RoleModel::CALL_BACK),array('eq',RoleModel::DATASTAFF), 'or')))->select();
                break;
            case 3:
                //人事部
                $re = D("Role")->where(array('status'=>1))->select();
                break;
            
            default:
                //销售部
                $re = D("Role")->where(array('ename'=>array(
                    array('eq',RoleModel::CAPTAIN),array('eq',RoleModel::STAFF), 'or')))->select();
                break;
        }

        return $re;
    }

    


    

}