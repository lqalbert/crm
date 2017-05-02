<?php
namespace Home\Model;
use Think\Model;

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

	protected $tableName = 'department_basic';

	
    protected $_validate = array(
         array('name','','部门名称已存在',0,'unique'), // 
   );

	private $types = array(
		'销售部',
        '客服部',
        '风控部'
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

    public function delete($ids){
        return $this->where(array('id'=>array('in', $ids )))->save(array('status'=>-1, 'user_id'=>null));
    }


    public function getSalesDepartments($fields="id,name"){
        return $this->where(array('type'=>self::SALES_DEPARTMENT, 'status'=> array('NEQ', -1)))->field($fields)->select();
    }

    

}