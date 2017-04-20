<?php
namespace Home\Model;

use Think\Model;

class RoleModel extends Model {
	
	const GOLD = 'gold';
	const DEPARTMENTMASTER = 'departmentMaster';
	const CAPTAIN = 'captain';
	const STAFF = 'staff';
	const RISK_ONE = 'riskOne';
	const RISK_TWO = 'riskTwo';
	const CALL_BACK = 'callBack';
	const SUP_SERVICE = 'supService';
	const GEN_SERVICE = 'genService';
	const SERVICEMASTER = 'serviceMaster';
	const RISKEMASTER = 'riskMaster';

	protected $tableName = 'rbac_role';


	private $enames = array(
		'gold',  //总经办
		'departmentMaster', //部门经理
		'captain', //主管 队长
		'staff',//员工
		'riskOne',//风控一
		'riskTwo',//风控一
		'callBack',//回访专员
		'supService',//客服主管
		'genService',//普通客服
		'serviceMaster', //客服经理
		'riskMaster' //风控经理
	);

	public function getEnames(){
		return $this->enames;
	}

	/**
	* 跟据类型返回对应的 英文名
	* 0 => departmentMaster
	* 1 => serviceMaster
	* 2 => riskMaster
	* 这个 0、1、2 是 Department模型里面types的数字索引
	* 0 => 1
	* 1 => 9,
	* 2 => 10,
	* @param int
	* @return String
	*/
	public function getEnameByType($type){
		$map = array(
			1,9,10
		);
		return $this->enames[$map[$type]];
	}

	
	public function getIdByEname($name){
		return $this->where(array('ename'=>$name))->getField("id");
	}

	public function getIdByType($type){
		return $this->getIdByEname($this->getEnameByType($type));
	}

	public function getEnameById($id){
		return $this->where(array("id"=>$id))->getField('ename');
	}
}