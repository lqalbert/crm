<?php
namespace Home\Controller;
use Think\Model;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\CustomerModel;
use Home\Model\RealInfoModel;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;
use Home\Model\ProductModel;
class CustomerController extends CommonController {
	protected $table = "Customer";
	protected $pageSize = 11;

	public function index () {
		// $dataList = $this->getList();
        // var_dump(D('Customer','Logic')->sb);die();
        $user = new User();
        $searchGroup = $user->getRoleObject()
                            ->getCustomerSearchGroup(array('user_id'=>'0','name'=>"团组" ));
        $groupMemberList = M('user_info')->where(array('group_id'=>session('account')['userInfo']['group_id']))->getField("user_id,realname");

        $this->assign('searchGroup',  $searchGroup);
        $this->assign('memberList',   array());
		$this->assign('customerType', $this->M->getType(null));
		$this->assign('sexType',      $this->M->getSexType(null));
		$this->assign('Quality',      $this->M->getQuality(null));
		$this->assign('Year',         $this->M->getYear(null));
		$this->assign('Income',       $this->M->getIncome(null));
		$this->assign('Sty',          $this->M->getStyle(null));
		$this->assign('Money',        $this->M->getMoney(null));
		$this->assign('Energy',       $this->M->getEnergy(null));
		$this->assign('Problem',      $this->M->getProblem(null));
		$this->assign('Mode',         $this->M->getMode(null));
		$this->assign('Attitude',     $this->M->getAttitude(null));
		$this->assign('Profession',   $this->M->getProfession(null));
		$this->assign('Intention',    $this->M->getIntention(null));

		$this->assign('Source',       $this->M->getSource(null));

  //       $this->assign('GoodsType',    D('CustomerLog')->getGoodsType());
		// $this->assign('ServiceCycle', D('CustomerLog')->getServiceCycle());

        $Products= D('Product')->where( array('status'=>array('NEQ', ProductModel::DELETE_STATUS)))->select();
        $this->assign('Products', $Products);

		$this->assign('logType',      D('CustomerLog')->getType(null));
		$this->assign('steps',        D('CustomerLog')->getSteps(null));
		$this->assign('Proportion',   D('CustomerLog')->getProportion(null));
		$this->assign('Remind',       D('CustomerLog')->getRemind(null));
        $this->assign('uid',  I('get.id', session('uid') ));

        $this->assign('isDepartment', $this->getRoleEname() != RoleModel::DEPARTMENTMASTER);

        $this->assign('complainTypes', D("CustomerComplain")->getType());
        
       

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
            $this->M->field("customers_basic.id");
			$aggregation[$value] = $this->M->count();
            
            
		}
		$this->assign('aggregation', $aggregation);
        $this->assign('pulled',      $this->getBeenPulld());
		$this->display();

	}

    
    public function setGroupCondition($condition){

        if ($condition==0) {
            $user = new User();
            $user->getRoleObject()->setMemberUserCondition($this->M);
        } else {
            $this->M->where(array("salesman_id|customers_basic.user_id"=> $condition ));
        }


    }

    /**
    * 被索取的数量 
    * 默认为3个月时间
    */
    public function getBeenPulld(){
        $data = D('Customer','Logic')->ThreeMonthsAge();
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
        $this->setGroupCondition(I('get.group',session('uid')));

        if (I('get.name')) {
            $this->M->where(array("customers_basic.name"=> array('like', I('get.name')."%")));
        }

        if (I('get.phone')) {
            // $this->M->where(array("cc.phone"=> array('like', I('get.name')."%")));
            $val = I('get.phone');
            $complexWhere = array('_logic'=>'OR');
            $complexWhere['cc.phone']  = array('like', $val."%");
            $complexWhere['cc2.phone'] = array('like', $val."%");

            if (count($complexWhere)>1) {
                $this->M->where(array('_complex'=>$complexWhere));
            }
        }

        if (I('get.type')) {
            $this->M->where(array("customers_basic.type"=> I('get.type')));
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
            // $timeCondition = D('Customer','Logic')->ThreeMonthsAge();
            // $this->M->where(array("customers_basic.created_at"=>array('EGT',$timeCondition)));

            $this->setGroupCondition(I('get.group',session('uid')));

            if (I('get.name')) {
                $this->M->where(array("customers_basic.name"=> array('like', I('get.name')."%")));
            }

            if (I('get.contact')) {
                $complexWhere = array('_logic'=>'OR');
                $arrList = array('phone', 'qq', 'weixin' );
                $val = I('get.contact');
                foreach ($arrList as $value) {
                    $complexWhere['cc.'.$value] = array('like', $val."%");
                    $complexWhere['cc2.'.$value] = array('like', $val."%");
                }

                if (count($complexWhere)>1) {
                    $this->M->where(array('_complex'=>$complexWhere));
                }
            }

            D('Customer','Logic')->getSingleButton('Cus',$this->M);
           
        }

        $this->M->join(' customers_contacts as cc on customers_basic.id =  cc.cus_id  and cc.is_main = 1')
                ->join('left join customers_contacts as cc2 on customers_basic.id =  cc2.cus_id and cc2.is_main = 0')
                ->where(array('customers_basic.status'=>array('NEQ', -1)));

	}



    protected function _getList(){
        $this->setQeuryCondition();
        $count = (int)$this->M->count();
        $this->setQeuryCondition();
        D('Customer','Logic')->getJoinCondition($this->M);
        if (I('get.sort_field', null)) {
            $this->M->order(I('get.sort_field')." ". I('get.sort_order').", id desc");
        } else {
            $this->M->order('customers_basic.id desc');
        }
        $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->select();
        
        foreach($list as $k=>$v){
            $list[$k]['qq_nickname']=mb_substr($v['qq_nickname'],0,12);
            $list[$k]['weixin_nickname']=mb_substr($v['weixin_nickname'],0,12);
        }

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
    *
    *
    * 
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
    * if 超过三层了  的逻辑需要重构封装 
    */
    public function edit() {
        $this->M->startTrans();
        if ($this->M->create($_POST, Model::MODEL_UPDATE) && ($this->M->save() !== false) )  {
            $D_cc  = D('CustomerContact');
            $D_cc->where(array('is_main'=>1, 'cus_id'=>$_POST['id']))->find();
            $mainData = $D_cc->getMainPost();
            $mainData['id'] = $D_cc->id;
            $reData = $D_cc->create($mainData);
            if (!$reData) {
                $this->M->rollback();
                $this->error($D_cc->getError());
            }
            $re = $D_cc->edit($reData);
            if ($re === false) {
                $this->M->rollback();
                $this->error($D_cc->getError());
            }

            $data2 = $D_cc->getSecondPost();
            $row2  = $D_cc->where(array('is_main'=>0, 'cus_id'=>$_POST['id']))->find();
            if ($row2) {

                $mainData = $D_cc->getMainPost();
                $data2['id'] = $row2['id'];
                $reData = $D_cc->create($data2);
                if (!$reData) {
                    $this->M->rollback();
                    $this->error($D_cc->getError());
                }

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
                if ( empty($data2['phone']) 
                    && empty($data2['qq']) 
                    && empty($data2['weixin'])
                    && empty($data2['qq_nickname'])
                    && empty($data2['weixin_nickname'])
                    ) {
                    $this->M->commit();
                    $this->success('编辑成功2');
                }
                $data2['cus_id'] = $_POST['id'];
                if ( $D_cc->create($data2) && $D_cc->add()  ) {
                    $this->M->commit();
                    $this->success('编辑成功3_');
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

	// /**
	// * 添加跟踪纪录
	// *
	// */
	// public function addTrackLogs(){
    //     D('Customer','Logic')->addTrackLogs();
    // }

    /**
    *   添加计划记录
    *
    */
    public function addPlanLogs(){
       // $this->authCheck(I('post.cus_id'));
        // $re = $this-M>->where(array("salesman_id"=>session('uid'), 'id'=> I('post.cus_id')))->find();
        // if (!$re) {
        //     $this->error("你没有权限");
        // }

        if($this->M->auto(false)->create() && I('post.cus_id')){
           $re=$this->M->where(array('id'=>I('post.cus_id')))->save();
           if($re === false){
             $this->error(L('ADD_ERROR').$this->M->getError());
           }else{
            $this->success($this->M->getlastsql());
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
        $arr=D('Customer','Logic')->trackInfo($this->M);
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
		$between_today =  D('Customer','Logic')->getDayBetween();
		$this->M->where(array('plan'=>$between_today))
		        ->where(array("salesman_id"=> session('uid')))
                ->join('customers_contacts as cc on customers_basic.id = cc.cus_id and cc.is_main=1')
		        ->field('customers_basic.id,qq,customers_basic.name,plan,remind');
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
        // $this->authCheck($id);
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


   
    
    
    
    public function findRealInfo($cus_id){
       $arr=M('deal_info')->where(array('cus_id'=>$cus_id))->find();
       if($arr){
         $this->ajaxReturn($arr);
       }else{
         return $arr; 
       }
    }


    public function checContact($value, $type){
        $row = M('customers_contacts')->where(array($type=>$value))->find();
        if ($row) {
            if ( !session('?'.$type."_".$value."_".HOOK_ADDCONTACT)) {
                $pa = array('list'=>array($row['cus_id']), 'uid'=>session('uid'), 'type'=>$type, 'value'=> $value);
                tag(HOOK_ADDCONTACT , $pa);
                session($type."_".$value."_".HOOK_ADDCONTACT, true);
            }

            $this->error("存在");
        } else {
            $this->success();
        }
    }

    public function buy(){


        // $data['user_id'] = session("uid");


        //UTC时间转化成本地时间日期
        $_POST['buy_time'] = UTCToLocaleDate($_POST['buy_time']);
        //设成V
        //更新客户资料 如果不一样 id_card address
        //添加购买纪录
        $row = $this->M->find(I("post.id"));

        if (!$row) {
            $this->error("没找到对应的数据");
        }

        $buyRow = D("CustomerBuy")->where(array('cus_id'=>$row['id']))->find();
        if ($buyRow) {
            $this->error("已经有购买记录,请选择续费");
        }



        $this->setVType($this->M);
        $this->setDetail($this->M, array('id_card'=>I("post.id_card"), 'address'=>I('post.address'), 'name'=>I('post.name')));
        $re = $this->M->save();
        if ($re === false) {
            $this->error('更新失败');
        }

        //购买纪录
        unset($_POST['id']);
        $data = D('CustomerBuy')->create($_POST);
        // unset($data['id']);
        $data['cus_id'] = $row['id'];
        $data['user_id'] = session("uid");

        //设置到期时间
        $product = M("products")->find(I("post.product_id"));
        if ($product) {
            // service_cycle
           $date['dead_time'] =  strtotime("+".$product['service_cycle']." months 23 hours 59 minutes 59 seconds",  strtotime(I("post.buy_time")) );
        } else {
            // $date['dead_time'] = 
        }
        
       

       
        $id = $this->addBuy($data);
        
        if (!$id) {
            $this->error('购买失败');
        }
        $data['id'] = $id;
        
        //自动分配给风控专员和回访专员
        tag(HOOK_DISTRIBUTE_BUY_CUSTOMER, $data);

    }

    private function setVType($m){
        if ( strpos($m->type, 'V') == false  ) {
            $m->type = 'V';
        }
    }

    private function setDetail($m, $data){
        foreach ($data as $key => $value) {

            if ($m->{$key} != $value) {
                $m->{$key} = $value;
            } 
        }
    }

    private function addBuy($data){
        return D('CustomerBuy')->data($data)->add();
    }

    private function fixPhone($phone){
        if (strpos($phone, chr(32)) !== false ) {
            return str_replace(chr(32), '', $phone);
        } else {
            return $phone;
        }
    }

    /**
    * 续费
    */
    public function renewal(){

        //升级要求
        // 指定时间之内
        // 只能升级一次
        // 其它情况只能是续费


        $data = D('CustomerBuy')->create($_POST);
        $data['user_id'] = session("uid");
        $data['type'] = 2;

        $buy_time = I('post.buy_time');
        $data['buy_time'] = UTCToLocaleDate($buy_time);

        $cus_id = I("post.cus_id");
        $row = $this->M->find($cus_id);
        
        if (!$row) {
            $this->error("没找到对应的数据");
        }
       
        $this->setDetail($this->M, array('address'=>I('post.address')));
        $re = $this->M->save();
        if ($re === false) {
            $this->error('更新失败');
        }

        $tmp = json_decode($data['todo_list'], true);
        unset($tmp['distribute']);
        $data['todo_list'] = json_encode($tmp);

        // 检测是不是 已经有升级了
        $hasRow = M('customers_buy')->where(array(
            'cus_id'=>$cus_id,
            'type'=>1,
            '_string' => 'status=1'
            ))->find();

        if ($hasRow) { //那就是续费
            $data['type'] = 2;
        } else { // 如果没有 检查是不是在时间段之内
            //检查是不是在时间段之内
            $hasRow = M('customers_buy')->where(array(
                'cus_id'=>$cus_id,
                'type'=>0,
                '_string' => 'status=1'
                ))->find();

            $productRow = M("products")->find($hasRow['product_id']);
            if ($productRow['upgrade']!=0) { //升级时限
                //
                $time = time();
                //不能自动计算准确 2017-08-31 + 1month = 2017-10-01
                $upgradeTime = strtotime("+".$productRow['upgrade']." months 23 hours 59 minutes 59 seconds",  strtotime($hasRow['buy_time']) );
                if ($time <= $upgradeTime) {
                    //升级
                    $data['type'] = 1;
                } else {
                    //续费
                    $data['type'] = 2;
                }

            } else {
                //续费
                $data['type'] = 2;
            }
        }

        $re = $this->addBuy($data);
        if (!$re) {
            $this->error("操作失敗");
        }

        $data['id'] = $re;
        // var_dump($data);
        //自动分配给风控专员和回访专员
        tag(HOOK_DISTRIBUTE_BUY_CUSTOMER, $data);
    }

    /**
    * "成功"的历史纪录
    */
    public function history(){
        $cus_id = I("get.cus_id");
        $re = M("customers_buy")->where(array('cus_id'=>$cus_id, "status"=>1))->select();
        $this->ajaxReturn($re);
    }

   /* public function loadRecords(){
        $cus_id = I("get.id");
        $re = M("customers_tracks")->where(array('cus_id'=>$cus_id))->select();
        $this->ajaxReturn($re);
    }*/




}