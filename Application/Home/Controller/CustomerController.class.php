<?php
namespace Home\Controller;
use Home\Model\CustomerModel;

class CustomerController extends CommonController {
	protected $table = "customer";
	protected $pageSize = 11;
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
		}

		$today = Date("Y-m-d")." 00:00:00" ;
		$between_today =  array(
							array('GT', Date("Y-m-d")." 00:00:00"), 
							array('LT', Date("Y-m-d H:i:s", strtotime("+1 day", strtotime($today))))
						);

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
		$this->M->find($LogM->cus_id);
		$LogM->startTrans();

		if (!$LogM->create()) {
			$LogM->rollback();
			$this->error(L('ADD_ERROR').$LogM->getError());
		}


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
}