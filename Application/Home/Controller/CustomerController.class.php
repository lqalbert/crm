<?php
namespace Home\Controller;

class CustomerController extends CommonController {
	protected $table = "customer";
	protected $pageSize = 11;
	public function index () {
		// $dataList = $this->getList();
		$this->assign('customerType', $this->M->getType());
		$this->assign('sexType',      $this->M->getSexType());
		$this->assign('logType',      D('CustomerLog')->getType());

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
	* 批量添加跟踪纪录
	*
	*/
	public function addTrackLogs(){
		$ids = I('post.cus_ids');
		// 传过来的时间是 utc 通用标准时间 这里进行一个转化
		$time = date('Y-m-d', strtotime(I('post.next_datetime')));
		$insert_arr = array();
		foreach ($ids as $key => $value) {
			$tmp = array();
			$tmp['cus_id']        = $value;
			$tmp['user_id']       = session('uid');
			$tmp['track_type']    = I('post.track_type');
			$tmp['content']       = I('post.content');
			$tmp['next_datetime'] = $time;

			$insert_arr[] = $tmp;
		}

		$result = D('CustomerLog')->addAll($insert_arr);
		if ($result !== false) {
			$this->success("添加成功");
		} else {
			$this->error("操作出错".D('CustomerLog')->getError());
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
        $type=$this->M->getType(I('post.type/d'));
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