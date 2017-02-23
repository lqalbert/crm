<?php
namespace Home\Controller;
use Home\Model\CustomerLogModel;
class AddCountController extends CommonController{
	protected $pageSize = 15;
	protected $table = "user_info";
	protected $fields = "realname,user_id";
	private function getDayBetween(){
		$today = Date("Y-m-d")." 00:00:00" ;
		return   array(
					array('GT', Date("Y-m-d")." 00:00:00"), 
					array('LT', Date("Y-m-d H:i:s", strtotime("+1 day", strtotime($today))))
		         );
	}

	public function index(){
		$this->display();
	}



	/**
	 * 公用 获取列表
	 *
	 * @return array() || null
	 * 
	 **/
	public function getList(){
		$between_today =  $this->getDayBetween();
		$this->setQeuryCondition();
		$count=(int)M($this->table)->count();
        $list=M($this->table)->field($this->fields)->page(I('get.p',0). ','. $this->pageSize)->select();
        $this->setQeuryCondition();
        foreach ($list as $k=> $v) {
        	$list[$k]['count']=M('customers_basic')->where(array('created_at'=>$between_today,'user_id'=>$v['user_id']))->count();
        	$list[$k]['all_count']=M('customers_basic')->where(array('user_id'=>$v['user_id']))->count();
        }
        $this->setQeuryCondition();
		$result = array('list'=>$list, 'count'=>$count);
		if (IS_AJAX) {
			$this->ajaxReturn($result);
		}  else {	
			return $result;
		}

	}

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
		switch (I('post.object')) {
			case 'user':
			      M($this->table)->where(array('group_id'=>array('GT',0)));
				break;
			case 'group':
			      $between_today =  $this->getDayBetween();
				  $this->table="group_basic";
				  $this->fields="name,id";
				  $list=M($this->table)->field($this->fields)->page(I('get.p',0). ','. $this->pageSize)->select();
                  var_dump($list);
				break;
			default:
				# code...
				break;
		}


		
	}




























}


