<?php
namespace Home\Model;

use Think\Model;

class RoleModel extends Model {
	
	const GOLD = 'gold';
	const DEPARTMENTMASTER = 'departmentMaster';
	const CAPTAIN = 'captain';
	const STAFF = 'staff';
	const RISK_ONE = 'riskone';
	const RISK_TWO = 'risktwo';
	const CALL_BACK = 'callback';
	const SUP_SERVICE = 'sup_service';
	const GEN_SERVICE = 'gen_service';

	protected $tableName = 'rbac_role';


	private $enames = array(
		'gold',  //总经办
		'departmentMaster', //部门经理
		'captain', //主管 队长
		'staff',//员工
		'riskone',//风控一
		'risktwo',//风控一
		'callback',//回访专员
		'sup_service',//客服主管
		'gen_service'//普通客服
	);

	public function getEnames(){
		return $this->enames;
	}

	/**
	* 跟据区域类型返回对应的 英文名
	* 1 => areaMaster
	* 2 => careerMaster
	* 3 => generaMaster
	* 这个 1、2、3 是 Department模型里面types的数字索引
	* 这里正好与 enames索引对应上了
	* @param int
	* @return String
	*/
	public function getEnameByType($type){
		return $this->enames[$type];
	}

	
	public function getIdByEname($name){
		return $this->where(array('ename'=>$name))->getField("id");
	}

	public function getIdByType($type){
		return $this->getIdByEname($this->getEnameByType($type));
	}
}