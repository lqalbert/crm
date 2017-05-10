<?php
namespace Home\Model;
use Think\Model;

class GroupModel extends Model {

    const DELETE_STATUS = -1;

	protected $tableName = 'group_basic';


    /**
    * @param int|array  department_id 
    *
    * @return []
    */
    public function getAllGoups($id=0, $field=null){
        $this->where(array('status'=>1));

        if (is_numeric($id) && $id!=0) {
            $this->where(array('department_id'=>$id));
        } else if(is_array($id)){
            $this->where(array('department_id'=>array('IN', $id)));
        }

        if (!empty($field)) {
            $this->field($field);
        }

        $this->where(array('status'=>array('NEQ', self::DELETE_STATUS)));
        
        return $this->select();
    }

    public function delete($ids){
        $users = M('user_info')->where(array('group_id'=>array('in', $ids)))->getField('user_id', true);
        D('RbacUser')->delete($users);
        return $this->where(array('id'=>array('in', $ids )))->save(array('status'=>-1, 'user_id'=>null));
    }

}