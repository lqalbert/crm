<?php
namespace Home\Controller;
use Home\Model\CustomerModel;
use Home\Model\CustomerLogModel;
use Home\Model\RoleModel;
use Home\Model\RealInfoModel;
use Common\Lib\User;
use Think\Model;

class CustomerController extends CommonController {

    const THREE_MONTH_AGE = 7776000;


	protected $table = "customer";
	protected $pageSize = 11;

	public function getDayBetween(){
		$today = Date("Y-m-d")." 00:00:00" ;
		return   array(
					array('GT', $today), 
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

        $this->assign('GoodsType',    D('CustomerLog')->getGoodsType());
		$this->assign('ServiceCycle', D('CustomerLog')->getServiceCycle());

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
        $this->assign('pulled',      $this->getBeenPulld());
		$this->display();

	}

    
    public function setGroupCondition($condition){
        switch ($condition) {
                case 'user_id':
                    $this->M->where(array("salesman_id"=> session('uid')));
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
    }

    /**
    * 被索取的数量 
    * 默认为3个月时间
    */
    public function getBeenPulld(){
        $date = Date("Y-m-d H:i:s", time()- self::THREE_MONTH_AGE );
        $c = D('customers_pulls')->where(array('from_id'=> session('uid'), 'created_at'=>array('GT', $date )   ))->count();
        if ($this->IS_AJAX) {
            $this->ajaxReturn($c);
        } else {
            return $c;
        }
    }


    /**
    *
    * 设置高级查询条件
    */
    public function advanceSearch(){
        $this->setGroupCondition(I('get.group',"user_id"));

        if (I('get.name')) {
            $this->M->where(array("name"=> array('like', I('get.name')."%")));
        }

        if (I('get.phone')) {
            $this->M->where(array("cc.phone"=> array('like', I('get.name')."%")));
        }

        $this->M->setTimeDiv('created_at', I('get.start'), I('get.end'));

        
        $track_start = str_replace('/','-',I('get.track_start')) ;
        $track_end   = str_replace('/','-',I('get.track_end'));
        $this->M->setTimeDiv('last_track', $track_start, $track_end);



    }

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition() {
        
        if(I('get.ctrl') == 'advance'){   
           $this->advanceSearch();
        }else{  

            $timeCondition=date("Y-m-d H:i:s",strtotime("-3 months",time()));
            $this->M->where(array("created_at"=>array('EGT',$timeCondition)));

            $this->setGroupCondition(I('get.group',"user_id"));

            if (I('get.name')) {
                $this->M->where(array("name|cc.phone|cc.qq|cc.qq_nickname|cc.weixin"=> array('like', I('get.name')."%")));
                // $this->M->where(array("phone"=> array('like', I('get.name')."%")));
            }


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
        }

        $this->M->join(' customers_contacts as cc on customers_basic.id =  cc.cus_id ');

        /*$this->M->join(' customers_contacts as cc on customers_basic.id =  cc.cus_id and cc.is_main=1');
                ->join('left join customers_contacts as cc2 on customers_basic.id =  cc2.cus_id and cc2.is_main!=1')
                ->field('customers_basic.*,cc.qq,cc.phone,cc.weixin,cc.qq_nickname,cc.weixin_nickname,cc2.qq as qq2,cc2.phone as phone2,cc2.weixin as wexin2,cc2.qq_nickname as qq_nickname2,cc2.weixin_nickname as weixin_nickname2');*/

	}

    private function setNamelike(){
        if(I('get.ctrl') != 'advance'){ 
            if (I('get.name')) {
                $this->M->where(array("name|cc.phone|cc.qq|cc.qq_nickname|cc.weixin"=> array('like', I('get.name')."%")));
            }
        }
    }

    protected function _getList(){
        $this->setQeuryCondition();
        // $this->setNamelike();
        // $this->M->join(' customers_contacts as cc on customers_basic.id =  cc.cus_id ');
        $count = (int)$this->M->count();
        $this->setQeuryCondition();
        $this->M->join('left join customers_contacts as cc2 on customers_basic.id =  cc2.cus_id and cc2.id!=cc.id')
                ->join('left join user_info as ui on customers_basic.salesman_id = ui.user_id')
                ->field('customers_basic.*,cc.qq,cc.phone,cc.weixin,cc.qq_nickname,cc.weixin_nickname,cc.is_main as cc_main,cc2.qq as qq2,cc2.phone as phone2,cc2.weixin as weixin2,cc2.qq_nickname as qq_nickname2,cc2.weixin_nickname as weixin_nickname2,ui.realname');



        if (I('get.sort_field', null)) {
            $this->M->order(I('get.sort_field')." ". I('get.sort_order'));
        }
        
        

        $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->select();
        // echo $this->M->getLastSql();
        $result = array('list'=>$list, 'count'=>$count);
        
        return $result;
    }



    /** 
    * 三参 要必填一个
    */
    private function uniquCheck(){
        if(I('post.qq')=='' && I('post.weixin')==''){
            $this->error('QQ1/微信1二者必填一');
        }else if(I('post.qq')==''){
            $_POST['qq']= null;
            return true;
        }else if(I('post.weixin')==''){
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

        //
        $_POST['salesman_id'] = $_POST['user_id'];
        $_POST['service_time'] = time();
		
		return $this->uniquCheck();
	}

    /**
    * 
    */
    /*public function add() { 
        if (!empty($_POST) && $this->M->create($_POST, Model::MODEL_INSERT) && $this->M->add()) {
            $this->success(L('ADD_SUCCESS'));
        } else {
            $this->error($this->M->getError());
        }
    }*/

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
        $row = $this->M->where(array('id'=>$id, 'salesman_id'=>array('in', $userlist) ))->field('id')->find();
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
		$id = I('post.id');
        $this->authCheck($id);	
	}


    /**
    * 编辑
    *
    * 知道 丑丑丑丑丑丑丑丑丑丑丑丑  
    * if 超过三层了 fuck customerContact 的逻辑需要重构封装 
    */
    public function edit() {
        $this->M->startTrans();
        if ($this->M->create($_POST, Model::MODEL_UPDATE) && ($this->M->save() !== false) )  {
            $D_cc  = D('CustomerContact');
            $D_cc->where(array('is_main'=>1, 'cus_id'=>$_POST['id']))->find();
            $re = $D_cc->edit($D_cc->getMainPost());
            if ($re === false) {
                $this->M->rollback();
                $this->error($D_cc->getError());
            }

            $data2 = $D_cc->getSecondPost();

            if ($D_cc->where(array('is_main'=>0, 'cus_id'=>$_POST['id']))->find()) {
                $re = $D_cc->edit($data2, true);
                if ($re !== false) {
                    $this->M->commit();
                    $this->success('编辑成功1');
                } else {
                     $this->M->rollback();
                    $this->error($D_cc->getError());
                }
            } else {
                // 
                if ((empty($data2['phone']) && empty($data2['qq']) && empty($data2['weixin']))) {
                    $this->M->commit();
                    $this->success('编辑成功2');
                }
                $data2['cus_id'] = $_POST['id'];
                if ( $D_cc->create($data2) && $D_cc->add()  ) {
                    $this->M->commit();
                    $this->success('编辑成功3_'.$D_cc->getLastSql());
                } else {
                    $this->M->rollback();
                    $this->error($D_cc->getError());
                }
            }
            $this->M->commit();
            $this->success('编辑成功4');
            //$this->success(L('EDIT_SUCCESS'));
        } else {
            $this->M->rollback();
            $this->error($this->M->getError());
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
		        ->where(array("salesman_id"=> session('uid')))
                ->join('customers_contacts as cc on customers_basic.id = cc.cus_id and cc.is_main=1')
		        ->field('customers_basic.id,qq,name,plan,remind');
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
    
    /**
    *  添加客户真实资料
    *  
    */
    public function realInfo(){ 
        $ob=D('RealInfo');
        $_POST['user_id']=session('uid'); 
        if($ob->where(array('cus_id'=>I('post.cus_id'),'identity'=>I('post.identity')))->find()){
            $ob->where(array('cus_id'=>I('post.cus_id')))->save(I('post.'));
        }else{
            if($ob->create($_POST) && $ob->add()){
                $this->success(L('真实资料添加成功'));
            }else{
                $this->error($ob->getError());    
                
            }
        }
    }
    
    public function findRealInfo($cus_id){
       $arr=M('deal_info')->where(array('cus_id'=>$cus_id))->find();
       if($arr){
         $this->ajaxReturn($arr);
       }else{
         return $arr; 
       }
    }

}