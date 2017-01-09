<?php
namespace Home\Controller;

class DepartmentController extends CommonController {
	protected $table="department_basic";

	public function index(){
		
		$count = $this->M->count();
		
		$datalist=$this->getList();
		$namelist=$this->M->field("name,zone")->where(array("p_id"=>0))->select();
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
		$map['name'] = array('like',"%". I('get.name')."%");
		$this->M->where($map);
	}

	public function getTopD(){
		$this->ajaxReturn(
			$this->M->where(array('p_id'=>0))->field('id,name')->select()
		);
	}
    
    public function _before_add(){     
       if(I('post.p_name') == '0'){
       	 $_POST['p_id']=I('post.p_name');
       	 $_POST['p_name']='顶级组织';
       }else{
       	 $_POST['p_id']=I('post.p_name');
       	 $arr=$this->M->where(array('id'=>I('post.p_name')))->field('name')->find();
       	 $_POST['p_name']=$arr['name'];
       }
    }

    public function _before_edit(){
    	$this->_before_add();
    }

}