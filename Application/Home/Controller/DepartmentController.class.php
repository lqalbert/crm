<?php
namespace Home\Controller;

class DepartmentController extends CommonController {
	protected $table="Department";

	public function index(){
		
		$count = $this->M->count();
		
		$datalist=$this->getList();
		$namelist=$this->M->field("name,zone")->where(array("p_id"=>0))->select();

		$this->assign("typeList", $this->M->getType());
		$this->assign("datalist", $datalist);
		$this->assign("totalCount", $count);
		$this->assign("namelist",$namelist);

		$this->display();
	}

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
		$map = array(); //查询的参数
		// $map['name'] = array('like',"%". I('get.name')."%");
		if ( !empty(I('get.name')) ) {
			$map['name'] = array('like',"%". I('get.name')."%");
		}
		$map['id'] = array('neq',0);
		$this->M->where($map);
	}

	public function getTopD(){
		// where(array('type'=>array('EQ', I('get.type')-1)))
		$this->ajaxReturn(
			$this->M->field('id,name,level')->order('level asc ,id asc')->select()
		);
	}
    
    // public function _before_add(){     
    //    // if(I('post.p_name') == '0'){
    //    // 	 $_POST['p_id']=I('post.p_name');
    //    // 	 $_POST['p_name']='顶级组织';
    //    // }else{
    //    // 	 $_POST['p_id']=I('post.p_name');
    //    // 	 $arr=$this->M->where(array('id'=>I('post.p_name')))->field('name')->find();
    //    // 	 $_POST['p_name']=$arr['name'];
    //    // }
    // 	return true;
    // }


	/**
	* 在添加之前 处理
	* @return true
	*/
	public function _before_edit(){
		$id  = I('post.id');
		$old = $this->M->field('name')->find($id);
		$old_name = $old['name'];
		$new_name = I("post.name");
		if ($old_name != $new_name) {
			$re = $this->M->where(array('p_id'=>$id))->save(array('p_name'=>$new_name));
			if ($re !== false) {
				return true;
			} else {
				return false;
			}
		}
		return true;
	}
    
    /**
    * 获取区域/部门 对应的备选负责人
    * 1 获取 区域
    * 2 获取 事业部门
    * 3 获取 推广部门
    */
	public function getUsers(){
		$type = I("get.type");
		$role_id = D("role")->getIdByType($type);
		$sql  = "select user_id from rbac_role_user where role_id = $role_id";
		$sql  = "select user_id, realname, mphone from user_info where user_id in($sql)";
		$result = $this->M->query($sql);
		$this->ajaxReturn($result);

	}



}