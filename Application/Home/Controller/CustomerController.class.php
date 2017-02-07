<?php
namespace Home\Controller;
use Home\Model\CustomerModel;

class CustomerController extends CommonController {
	protected $table = "customer";
	protected $pageSize = 11;

	private function getDayBetween(){
		$today = Date("Y-m-d")." 00:00:00" ;
		return   array(
							array('GT', Date("Y-m-d")." 00:00:00"), 
							array('LT', Date("Y-m-d H:i:s", strtotime("+1 day", strtotime($today))))
						);
	}

	public function index () {
		// $dataList = $this->getList();
		$this->assign('customerType', $this->M->getType());
		$this->assign('sexType',      $this->M->getSexType());
		$this->assign('Quality',      $this->M->getQuality());
		$this->assign('Year',         $this->M->getYear());
		$this->assign('Income',       $this->M->getIncome());
		$this->assign('Sty',          $this->M->getStyle());
		$this->assign('Money',        $this->M->getMoney());
		$this->assign('Energy',       $this->M->getEnergy());
		$this->assign('Problem',      $this->M->getProblem());
		$this->assign('Mode',         $this->M->getMode());
		$this->assign('Attitude',     $this->M->getAttitude());
		$this->assign('Profession',   $this->M->getProfession());
		$this->assign('Intention',    $this->M->getIntention());
		$this->assign('Source',       $this->M->getSource());
		$this->assign('logType',      D('CustomerLog')->getType());
		$this->assign('steps',        D('CustomerLog')->getSteps());
		$this->assign('Proportion',   D('CustomerLog')->getProportion());
		$this->assign('Remind',       D('CustomerLog')->getRemind());
		$this->display();
	}

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
		$this->M->where(array("user_id"=> session('uid')));
		if (I('get.name')) {
			$this->M->where(array("name"=> array('like', I('get.name')."%")));
			var_dump($this->M->getLastSql());
		}

		// $today = Date("Y-m-d")." 00:00:00" ;
		$between_today =  $this->getDayBetween();

		switch (I('get.field')) {
			case 'plan':
				$this->M->where(array(
					'plan'=> $between_today
					));
				break;
			case 'log':
				$this->M->where(array('log_count'=> array('NEQ',0)));
				break;
			case 'unlog':
				$this->M->where(array('log_count'=> 0));
				break;
			case 'transfto':
				$this->M->where(array('transfer_status'=>1, 'transfer_to'=>array('NEQ', 0)));
				break;
			case 'transfin':
				$this->M->where(array('transfer_status'=>array( array('EQ', 1), array('EQ', 2), 'or'), 'transfer_to'=>session('uid')));
				break;
			case 'type':
				$this->M->where(array('type'=>CustomerModel::TYPE_V));
				break;
			case 'important':
				$this->M->where(array('important'=>1));
				break;
			case 'conflict':
				$this->M->where(array(
					'conflict'=> $between_today
					));
				break;
			default:
				# code...
				break;
		}

		// if (I('get.plan', false)) {
			
			
		// }
	}

	/**
	* 在添加之前 加入一个参数
	* @return true
	*/
	public function _before_add(){
		$_POST['user_id'] = session('uid');
		return true;
	}


	/**
	* 在编辑之前 判断是不是 “我” 的客户
	* @return boolean
	*/
	public function _before_edit(){
		$id = I('post.id');
		if ($this->M->where(array('id'=>$id, 'user_id'=>session('uid') ))->field('id')->find()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* 添加跟踪纪录
	*
	*/
	public function addTrackLogs(){
		// $id = I('post.id');
		$LogM = D('CustomerLog');
		$to_type = I('post.to_type', '');
		$LogM->startTrans();
		if (!$LogM->create()) {
			
			$LogM->rollback();
			$this->error(L('ADD_ERROR').$LogM->getError());
		}
		$this->M->find($LogM->cus_id);
		if ($to_type !== "" &&  $to_type != $this->M->type) {
			$LogM->contentSetChangeType($this->M->type, $to_type);
			$this->M->type = $to_type;
			
			$re = $this->M->save();
			if ($re === false) {
				$LogM->rollback();
				$this->error(L('ADD_ERROR').$this->M->getError()."e");
			}
		}

		if ($LogM->add()) {
			$LogM->commit();
			$this->success(L('ADD_SUCCESS'));
		} else {
			$LogM->rollback();
			$this->error(L('ADD_ERROR').$LogM->getError());
		}
		
	}

    /**
    *   添加计划记录
    *
    */
    public function addPlanLogs(){
    	if($this->M->create() && I('post.cus_id')){
           $re=$this->M->where(array('id'=>I('post.cus_id')))->save();
           if($re === false){
           	 $this->error(L('ADD_ERROR').$this->M->getError());
           }
    	}
    }

	public function getTracks(){
		var_dump(D('CustomerLog')->select());
	}
    
    /**
    *   获取跟踪信息
    *
    */
	public function trackInfo(){
        $type=$this->M->getType(I('post.type'));
        $group_id=M('user_info')->where(array('user_id'=>I('post.user_id')))->field('group_id')->find();
        $groupInfo=M('group')->where(array('id'=>$group_id['group_id']))->field('name,p_name')->find();
        $userName=M('user_info')->where(array('user_id'=>I('user_id')))->field('realname')->find();
        $user=$groupInfo['p_name']."-".$groupInfo['name']."-".$userName['realname'];
        $arr=M('customers_log')->where(array('cus_id'=>I('post.id')))->order('id desc')->select();
        foreach ($arr as $key => $value) {
        	$arr[$key]['type']=$type;
        	$arr[$key]['user']=$user;
        	$arr[$key]['name']=I('post.name');
        	$arr[$key]['track_type']=D('CustomerLog')->getType((int)$arr[$key]['track_type']);
        }
		if (IS_AJAX) {
			$this->ajaxReturn($arr);

		}  else {
			
			return $arr;
		}
	}


	/**
	* 获取今天之内的 计划客户
	*/
	public function getTodays(){
		$between_today =  $this->getDayBetween();
		$this->M->where(array('plan'=>$between_today))
		        ->where(array("user_id"=> session('uid')))
		        ->field('id,qq,name,plan');
		$re = $this->M->select();
		foreach ($re as $key => $value) {
			$re[$key]['time'] = strtotime($value['plan']) * 1000;
		}
		$this->ajaxReturn($re);
	}

	/**
	* 设置 plan 为 null
	* @param $id int
	*/
	public function setPlan() {
		$id = I("post.id");
		$re = $this->M->where("id=".$id)->data(array("plan"=>NULL))->save();
		if ($re !== false) {
			$this->success($this->M->getLastSql());
		} else {
			$this->error();
		}
	}

    /**
    *   获取接收单位
    *
    */
    public function getRecDep(){
    	$arr=M('department_basic')->select();
    	foreach ($arr as $k => $v) {
    		$arr[$k]['dep_name']="[".$arr[$k]['type']."]".$arr[$k]['name'];
    		$arr[$k]['value']=$arr[$k]['zone']."-".$arr[$k]['name'];
    	}
    	$this->ajaxReturn($arr);

    }

    /**
    *   获取接收员工
    *
    */
    public function getRecUser($p_name){
    	$arr=explode('-', $p_name);
    	$pname=end($arr);
    	$re=M('group')->where(array('p_name'=>$p_name))->field('id')->select();
    	$res=array();
    	foreach ($re as $k => $v) {

    		$res[]=M('user_info')->where(array('group_id'=>$re[$k]['id']))->select();
    	}   
        foreach ($res as $k => $v) {
        	foreach ($v as $key => $val) {
        		$res[$k][$key]['value']="[".$pname."]".$res[$k][$key]['realname'];
        	}
        }
        $resu=array();
        foreach ($res as $value) {
        	foreach ($value as $va) {
        		$resu[]=$va;
        	}
        }
        $this->ajaxReturn($resu);
    }     

    /**
    * 一次性使用的
    */      
   /* public function setTrackText(){
    	$allTypes = D("CustomerLog")->getType();
    	foreach ($allTypes as $key => $value) {
    		$sql = "update customers_log set track_text = '{$value}' where track_type={$key}";
    		$re = M()->execute($sql);
    	}
    }*/

}