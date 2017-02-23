<?php
namespace Home\Controller;
use Home\Model\CustomerModel;
use Home\Model\CustomerLogModel;
use Home\Model\RoleModel;
use Common\Lib\User;

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
        $user = new User();
        $searchGroup = $user->getRoleObject()
                            ->getCustomerSearchGroup(array(array('value'=>'user_id','key'=>"本人" ) , array('value'=>'group','key'=>"团组" )));
        $groupMemberList = M('user_info')->where(array('group_id'=>session('account')['userInfo']['group_id']))->getField("user_id,realname");

        $this->assign('searchGroup',  $searchGroup);
        $this->assign('memberList',   $groupMemberList);
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


		//统计
		//条件的数组
		$field = array(
			'plan',
			'log',
			'unlog',
			'transfto',
			'transfin',
			'type',
			'important',
			'conflict'
		);

		$aggregation = array();
		foreach ($field as $value) {
			$_GET['field'] = $value;
			$this->setQeuryCondition();
			$aggregation[$value] = $this->M->count();
		}
		$this->assign('aggregation', $aggregation);
		$this->display();
	}

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
		// $this->M->where(array("user_id"=> session('uid')));

        switch (I('get.group',"user_id")) {
            case 'user_id':
                $this->M->where(array("user_id"=> session('uid')));
                break;
            case 'group':
                $user = new User();
                $user->getRoleObject()->setMemberUserCondition($this->M);
                break;
            case 'precheck':

                break;
            default:
                break;
        }
        

		if (I('get.name')) {
			$this->M->where(array("name|phone|qq|qq_nickname|weixin"=> array('like', I('get.name')."%")));
			//var_dump($this->M->getLastSql());
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
				
				break;
		}

		// if (I('get.plan', false)) {
			
			
		// }
	}

    /**
    * 三参 要必填一个
    */
    private function uniquCheck(){
        if(I('post.phone')=='' && I('post.qq')=='' && I('post.weixin')==''){
            $this->error('手机/QQ/微信任填其一');
        }else if(I('post.phone')=='' && I('post.qq')==''){
            $_POST['phone']= null;
            $_POST['qq']= null;
            return true;
        }else if(I('post.phone')=='' && I('post.weixin')==''){
            $_POST['phone'] = null;
            $_POST['weixin']= null;
            return true;
        }else if(I('post.qq')=='' && I('post.weixin')==''){
            $_POST['qq']= null;
            $_POST['weixin']= null;
            return true;
        }else{
            return true;
        }
    }

	/**
	* 在添加之前 加入一个参数
	* @return true
	*/
	public function _before_add(){
		$_POST['user_id'] = session('uid');
		
		return $this->uniquCheck();
	}

    /**
    * 如果不是我的客户， 以及不是我团组的客户 就
    * 反回 false
    * 赶进度 暂时 这样写
    */
    private function authCheck($id){
        $captainId = D('Role')->getIdByEname(RoleModel::CAPTAIN);
        $userlist = array();
        if (session('account')['userInfo']['role_id'] == $captainId ) {
            $userlist = M('user_info')->where(array('group_id'=>session('account')['userInfo']['group_id'] ))->getField("user_id", true);
        } else {
            $userlist[] = session('uid');
        }
        // var_dump($userlist);
        $row = $this->M->where(array('id'=>$id, 'user_id'=>array('in', $userlist) ))->field('id,phone,qq,weixin')->find();
        if (!$row) {
            $this->error("你没有权限");
        } 
    }


	/**
	* 在编辑之前 判断是不是 “我” 的客户
	* 判断有没有修改 手机 QQ 微信号
	* 如果修改了 则要
	* @return boolean
	*/
	public function _before_edit(){
		$this->uniquCheck();
		$id = I('post.id');
        $this->authCheck($id);
        
		
        if (I('post.phone') != $row['phone']) {
            $re = $this->M->where(array('phone'=> I('post.phone'), 'id'=>array('NEQ', $row['id'])))->field('id')->find();
            if ($re) {
                D('CustomerConflict')->addPhone($re['id']);
                $this->error('手机号已存在');
            }
        }

        if (I('post.qq') != $row['qq']) {
            $re = $this->M->where(array('qq'=> I('post.qq'), 'id'=>array('NEQ', $row['id']) ))->field('id')->find();
            if ($re) {
                D('CustomerConflict')->addQQ($re['id']);
                $this->error('QQ号已存在');
            }
            
        }

        if (I('post.weixin') != $row['weixin']) {
            $re = $this->M->where(array('weixin'=> I('post.weixin'), 'id'=>array('NEQ', $row['id'])))->field('id')->find();
            if ($re) {
                D('CustomerConflict')->addWx($re['id']);
                $this->error('微信号已存在');
            }
        }
	}

	/**
	* 添加跟踪纪录
	*
	*/
	public function addTrackLogs(){
		// $id = I('post.id');
        $this->authCheck(I('post.cus_id'));
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

        if( $LogM->track_type == 0 || !empty($LogM->track_type)){
            $LogM->track_text = D('CustomerLog')->getType((int)$LogM->track_type);
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
       $this->authCheck(I('post.cus_id'));
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
        $groupInfo=M('group_basic')->where(array('id'=>$group_id['group_id']))->field('name')->find();
        $userName=M('user_info')->where(array('user_id'=>I('user_id')))->field('realname')->find();
        $user=$groupInfo['name']."-".$userName['realname'];
        $arr=M('customers_log')->where(array('cus_id'=>I('post.id')))->order('id desc')->select();
        foreach ($arr as $key => $value){
        	$arr[$key]['type']=$type;
        	$arr[$key]['user']=M('user_info')->where(array('user_id'=>$arr[$key]['user_id'],'id'=>I('post.id')))->getField('realname');
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
		        ->field('id,qq,name,plan,remind');
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
        $this->authCheck($id);
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
    	$re=M('group_basic')->where(array('p_name'=>$p_name))->field('id')->select();
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

    /**
    * 设置提醒
    */
    public function setRemid(){
        $data = array();
        $data['remind'] = I('post.remind');
        $data['remark'] = I('post.remark');
        $data['id'] = I('post.cus_id');
        $this->authCheck($data['id']);

    	if (!empty($data['remind'])) {
    		$data['remind'] = D('CustomerLog')->getRemind((int)$data['remind']);
    	}

    	if(!empty($data['remark'])) {
    		$data['remind'] = $data['remind']."\n". $data['remark'];
    	}

    	unset($data['remark']);

    	$re = $this->M->data($data)->save();
    	if ($re !== false) {
    		$this->success("操作成功");
    	} else {
    		$this->error("操作失败");
    	}
    }

    /**
    * 是否自选 
    *
    */
    public function setImportant(){
        $data = array(
                'important' => I('post.choose'),
                'id'        => I('post.cus_id')
            );
        $this->authCheck($data['id']);
    	$re = $this->M->data($data)->save();
    	if ($re !== false) {
    		$this->success("操作成功");
    	} else {
    		$this->error("操作失败");
    	}
    }

    /**
    *  检测是否有未导入的客户数据
    *  按真实姓名来的
    */
    public function checkCucstomers(){
        $realname = session('account')['userInfo']['realname'];
        $group_id = session('account')['userInfo']['group_id'];
        //创建的
        $c_count = $this->M->where(array('help_user'=> $realname,     'help_group_id'=>$group_id, 'user_id'=>0))->count();
        //转让的
        $t_count = $this->M->where(array('help_transfer'=> $realname, 'help_user'=> array('neq', $realname),'help_group_id'=>$group_id, 'user_id'=>0))->count();

        $this->ajaxReturn(array('c'=>$c_count, 't'=>$t_count));
    }


    public function importMyc(){
        $realname = session('account')['userInfo']['realname'];
        $group_id = session('account')['userInfo']['group_id'];
        $re = $this->M->where(array('help_user'=> $realname))->data(array('user_id'=>session('uid')))->save();
        $re2 = $this->M->where(array('help_transfer'=> $realname))->data(array('transfer_to'=>session('uid'), 'transfer_status'=>2))->save();

        $this->ajaxReturn(array('c'=>$re, 't'=>$re2));
    }

}