<?php
namespace Home\Model;
use Think\Model;

class DepartmentDivisionModel extends Model {

    const STATUS_SHOW = 1;
    const STATUS_HIDDEN = -1;

    protected $tableName = 'department_division';

    
    protected $_validate = array(
         array('name','','名称已存在',0,'unique'), // 
    );

    
    public function getAll($field=null){
        if (!empty($field)) {
            $this->field($field);
        }
        return $this->where(array('status'=>self::STATUS_SHOW))->select();
    }

    

}