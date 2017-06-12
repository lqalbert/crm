<?php
namespace Home\Controller;
use Home\Model\RoleModel;
use Home\Model\DepartmentModel;
use Home\Model\GroupModel;
use Home\Model\UserModel;
use Home\Model\RbacUserModel;

class TreeController extends CommonController{

	public function setDepartment(){
		$ename = $this->getRoleEname();
		if($ename == RoleModel::GOLD){
			$department = $this->getSalesDepartments(); //cache(true)->
			$re = array();
			foreach ($department as $k => $v) {
				$re[] = array(
	         'name' => $v['name'],
	         'id' => $v['id'],
	         'spread' => false,
	         'children' => $this->setGroup($v['id']),
	         'title' => $v['id'].$v['name'],
				);
			}
		}
		return $re;
	}

  protected function setGroup($department_id){
  	$group = $this->getAllGoups($department_id, 'id,name');
  	$re = array();
  	foreach ($group as $k => $v) {
  		$re[] = array(
      	'name' => $v['name'],
      	'id' => $v['id'],
      	'spread' => false,
				'children' => $this->setUser($v['id']),
				'title' => $v['id'].$v['name'],
  		);
  	}

  	return $re;
  }

  protected function setUser($group_id){
  	$user = $this->getGroupEmployee($group_id, 'id,realname');
  	$re = array();
  	foreach ($user as $k => $v) {
  		$re[] = array(
      	'name' => $v['realname'],
      	'id' => $v['id'],
				'spread' => false,
				'field' => array(
          'icon' => "&#xe65a;",
          'title' => $v['realname'],
          'href' => U('DepartmentCustomer/index', array('id'=>$v['id'])),
				),
  		);
  	}

  	return $re;
  }

  //获取所有部门
  protected function getSalesDepartments($fields="id,name"){
  	return D('Department')->cache(true)->where(array('type'=>DepartmentModel::SALES_DEPARTMENT, 'status'=> array('NEQ', -1)))
  	      ->field($fields)->select();
  }

  //获取所有小组
  protected function getAllGoups($id=0, $field=null){
	  D('Group')->cache(true)->where(array('status'=>1));

	  if (is_numeric($id) && $id!=0) {
	      D('Group')->cache(true)->where(array('department_id'=>$id));
	  } else if(is_array($id)){
	      D('Group')->cache(true)->where(array('department_id'=>array('IN', $id)));
	  }

	  if (!empty($field)) {
	      D('Group')->cache(true)->field($field);
	  }

	  D('Group')->cache(true)->where(array('status'=>array('NEQ', GroupModel::DELETE_STATUS)));
	  
	  return D('Group')->cache(true)->select();
  }

  //获取员工
  protected function getGroupEmployee($group_id, $field="id,account,realname"){
    $m = new RbacUserModel;
    return $m->join('user_info on rbac_user.id = user_info.user_id')
         ->cache(true)->where(array('group_id'=>$group_id, 'rbac_user.status'=>array('NEQ', RbacUserModel::DELETE_SATUS)))
         ->field($field)
         ->select();
  }

/*----------------------------------下面为优化后的部分---------------------------------------------------*/ 
  //所有部门
	public function getAlldep($fields="id,name"){
  	return  D('Department')->cache(true,180)->where(array('type'=>DepartmentModel::SALES_DEPARTMENT, 'status'=> array('NEQ', -1)))
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
           'id' => $v['id'],
           'spread' => false,
           'children' => $this->setMenuGroup($v['id']),
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
            //'href' => U('DepartmentCustomer/index', array('id'=>$v['id'])),
            'href' => U('AllUserCustomerTree/index', array('id'=>$v['id'])),
          ),
      );

    }
    
    return $re;

  }




















}