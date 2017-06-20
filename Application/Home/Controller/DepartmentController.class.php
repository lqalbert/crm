<?php
namespace Home\Controller;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Service\EmployeeOutput;

class DepartmentController extends CommonController {
	protected $table="Department";


	public function index(){
		$count = $this->M->count();
		$type = $this->M->getType();
		$this->assign("typeList", $type);
		$this->assign("totalCount", $count);
		$this->assign("zoneList", M('department_zone')->getField('id,name'));
		$this->assign("divisions", D('DepartmentDivision')->where(array('status'=>array('egt', 0)))->field('id,name')->select());

		 $ename = $this->getRoleEname();

    	$this->assign('viewDecorator', $this->M->decoratorView($ename));
		
		$this->display();
	}

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
		$name = I('get.name');
		$contact = I('get.contact');
		$tel = I('get.tel');
		$type = I('get.type');
		$map = array('department_basic.status'=>array('EGT',0) ); //查询的参数
		if ( !empty(I('get.name')) ) {
			$map['department_basic.name'] = array('like', $name ."%");
		}
		$this->M->where($map);

		$this->M->join('left join user_info as ui on department_basic.user_id = ui.user_id ')
				->field("department_basic.*, ui.realname as contact ,ui.mphone as tel");

    if(I('get.contact')){
      $this->M->where(array('ui.realname'=>array('like',"%".$contact."%")));
    }

    if(I('get.tel')){
    	$this->M->where(array('ui.mphone'=>array('like',"%".$tel."%")));
    }
    
    if(I('get.type')){
    	$this->M->where(array('department_basic.type'=>$type));
    }

	}


	public function getDivision(){
		$this->ajaxReturn( D('DepartmentDivision')->getAll('id,name') );
	}


	private function setConfig(){
		//临时写到这里
		// '销售部', 0
    // '客服部', 1
    // '风控部', 2
		// '人事部', 3
		$configMap = array(
			array('EmployeeQueryCondition'=>"DepartmentEmployee"),
			array('EmployeeQueryCondition'=>"DepartmentEmployee"),
			array('EmployeeQueryCondition'=>"DepartmentEmployee"),
			array('EmployeeQueryCondition'=>"AllEmployee"),

		);

		$_POST['config'] = json_encode($configMap[$_POST['type']]);
	}

	public function _before_edit(){
		$this->setConfig();
		$this->old = $this->M->find(I('post.id'));

		$newUserId = I('post.user_id');
		if ($this->old['user_id'] != $newUserId) {
			M('user_info')->where(array('user_id'=>$newUserId))
					      ->data(array('department_id'=>$this->old['id']))->save();
		}
	}
    

	public function _before_add(){
		if (empty($_POST['user_id'])) {
			$_POST['user_id'] = null;
		}

		$this->setConfig();
	}


    /**
    * 获取区域/部门 对应的备选负责人
    *
    * 销售部 部门经理
    * 客服部 客服经理
    * 风控部 风控经理
    *  人事部 人事经理
    */
	public function getUsers(){
		$type = I('get.type', 0);
		$id   = I("get.id",   0);
		$RoleModle = D('Role');
		$roleId = $RoleModle->getIdByEname($RoleModle->getEnameByType($type));
		
		switch ($type) {
			case '0':
				$sql = "select user_id,mid(realname, 1, 5) as realname from user_info where (role_id=$roleId and user_id not in(select user_id from department_basic where user_id is not null) ) or user_id=$id";
				break;

			default:
				$sql = "select user_id,mid(realname, 1, 5) as realname from user_info where (role_id=$roleId ) or user_id=$id";
				break;
		}
		
		$result = $this->M->query($sql);
		$this->ajaxReturn($result);
	}


	public function outPutExecle(){

		$id = I('get.id'); // department_id

		$outExcel = new EmployeeOutput();
		$outExcel->setDepartmentId($id);
		$outExcel->setTitle(D('Department')->where(array('id'=>$id))->getField('name'));
		outPutExcel($outExcel);
	}

	private function getHr(){
		return D('User')->getUnSHr();
	}


	public function setHr(){
		$userids = I('post.user_ids');
		$id = I('post.id');
		if ($userids) {
			$re = M('user_info')->data(array('department_id'=>$id))->where(array('user_id'=>array('in',$userids )))->save();
			if ($re !== false) {
				$this->success();
			} else {
				$this->error(M('user_info')->getError());
			}
		} else {
			$this->success();
		}
	}

	public function delHrFromDepartment(){
		$user_id = I('post.user_id');
		if ($user_id) {
			$re = M("user_info")->data(array('department_id'=>0))->where(array('user_id'=>$user_id))->save();
			if ($re) {
				$this->success();
			} else {
				$this->error('出错了');
			}
		} else {
			$this->success();
		}
	}



	public function getDepartmentHr($id){
		return D('User')->getSnHr($id);
	}

	/**
	* 获取一个部门的人事专员并获取备选的人事专员
	* @param $id int
	*/
	public function getSnUHrs(){
		$bHr = $this->getHr();
		$id = I('get.id',0);
		if ($id !=0 ) {
			$hrs = $this->getDepartmentHr(I('get.id'));
		} else {
			$hrs = array();
		}
		

		$this->ajaxReturn(array('bhr'=>$bHr, 'hrs'=>$hrs));
	}




}