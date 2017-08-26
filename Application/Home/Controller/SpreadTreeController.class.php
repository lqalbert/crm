<?php
namespace Home\Controller;

use Home\Model\RoleModel;
use Home\Model\DepartmentModel;
use Home\Model\GroupModel;
use Home\Model\UserModel;
use Home\Model\RbacUserModel;


class SpreadTreeController extends CommonController{


    private $departType = DepartmentModel::SPREAD_DEPARTMENT;

    private $url = 'SpreadAllCustomers/index';


     //所有部门
    public function getAlldep($id=0,$fields="id,name"){
        if (is_numeric($id) && $id!=0) {
            D('Department')->where(array('id'=>$id));
        } else if(is_array($id)){
            D('Department')->where(array('id'=>array('IN', $id)));
        }
        return  D('Department')->cache(true,180)->where(array('type'=>$this->departType, 'status'=> array('NEQ', -1)))
              ->field($fields)->select();
    }
  
    //所有小组
    public function getAllGroup($field="id,name,department_id"){
      return D('Group')->cache(true,180)->where(array('status'=>array('NEQ', GroupModel::DELETE_STATUS)))
                     ->field($field)->select();
    }

    //先获取所有员工 缓存时间为3分钟
    public function getAllUser($field="id,realname,group_id,department_id"){
    $m = new RbacUserModel;
    return  $m->join('user_info on rbac_user.id = user_info.user_id')->cache(true,180)
             ->where(array('rbac_user.status'=>array('NEQ', RbacUserModel::DELETE_SATUS)))
             ->field($field)->select();

    }


  public function setMenuDep(){
    $re = array();
    $departments  = $this->getAlldep();
    //先一次性读取出来保存到其他地方
    $this->groups = arr_group($this->getAllGroup(), "department_id");
    $this->users  = arr_group($this->getAllUser(),  "group_id");
    foreach ($departments as $v) {
      $re[] = array(
           'name' => $v['name'],
           'id'   => $v['id'],
           'spread' => false,
           'children' => $this->setMenuGroup($v['id']),
           'field' => array(
              // 'icon' => "&#xe65a;",
              'title' => $v['name'],
              'href' => U($this->url, array('department_id'=>$v['id'])),
           ),
        );
    }
    return $re;

  }

  public function setMenuGroup($department_id){
    $re = array();
    foreach ($this->groups[$department_id] as $k => $v) {
        $re[]=array(
          'name' => $v['name'],
          'id' => $v['id'],
          'spread' => false,
          'children' => $this->setMenuUser($v['id']),
           'field' => array(
              // 'icon' => "&#xe65a;",
              'title' => $v['name'],
              'href' => U($this->url, array('group_id'=>$v['id'])),
           ),
        );
    
    }
    
    return $re;
 
  }

  public function setMenuUser($group_id){
    $re = array();
    foreach ($this->users[$group_id] as $k => $v) {
      $re[] = array(
          'name' => $v['realname'],
          'id' => $v['id'],
          'field' => array(
            'icon' => "&#xe65a;",
            'title' => $v['realname'],
            'href' => U($this->url, array('id'=>$v['id'])),
          ),
      );

    }
    
    return $re;
  }


  
}