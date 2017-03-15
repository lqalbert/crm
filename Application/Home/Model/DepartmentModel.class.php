<?php
namespace Home\Model;
use Think\Model;

class DepartmentModel extends Model {

    const CAREER = 2;
    const GENERALIZE = 3;

	protected $tableName = 'department_basic';

	
    protected $_validate = array(
         array('name','','部门名称已存在',0,'unique'), // 
   );

	private $types = array(
		'事业部',
		'推广部',
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

    

}