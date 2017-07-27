<?php
namespace Home\Controller;

use Home\Model\RoleModel;
use Home\Service\CustomersGather;
use Common\Lib\User;
class TrendTradeController extends CommonController{
	protected $pageSize = 15;
  protected $table = "statistics_usercustomers";

 	protected function getSearchGroup(){
 		$searchGroup = array(
    	array('value'=>'user','key'=>"按队员查询" ),
      array('value'=>'group','key'=>"按团组查询" ),
      array('value'=>'department','key'=>'按部门查询')
 		);

 		return $searchGroup;
 	}

  protected function setInitSearch(){
    $ename = $this->getRoleEname();
    if ($ename == RoleModel::GOLD) {
      $department = null;
      $group = null;
    }elseif($ename == RoleModel::DEPARTMENTMASTER){
      $department = session('account')['userInfo']['department_id'];
      $group = null;
    }elseif ($ename == RoleModel::CAPTAIN) {
      $department = session('account')['userInfo']['department_id'];
      $group = session('account')['userInfo']['group_id'];
    }
    return array("department" => $department,"group" => $group);
  }


	public function index(){
		$this->assign('searchGroup',$this->getSearchGroup());
    $this->assign('initSearch',$this->setInitSearch());
    
		$this->display();
	}

  public function getList(){
    $this->M->where(array(
        'date'=>array(
          array('egt',I("get.start")), 
          array('elt', I("get.end")))))->field(" sum(today_v) as v, sum(create_num) as c,sum(conflict_to) as ct , sum(conflict_from) as cf ,`date`")
        ->group('`date`');

    $group_id = I("get.group_id", null);
   
    if ($group_id !== "") {
      if ($group_id!=0) {
        $this->M->where(array('group_id'=> $group_id));
      }
    } else {
      //如果department_id ==0 或 小于 0 则返回所有的
      $department_id = I("get.department_id", null);
      if ($department_id!== "") {
        if ($department_id != 0) {
          $this->M->where(array('department_id'=> $department_id));
        } 
      }
    }
    $re = $this->M->select();
    // var_dump($this->M->getLastSql());
    $row = $re[0];
    $this->getDateFromRange(I("get.start"), I('get.end'));
    if ($row['date'] != null ) {
      $dates = array_column($re, 'date');
      foreach ($this->date as $key => $value) {
        if (!in_array($value, $dates)) {
          array_splice($re, $key, 0, array(array(
            'v'    =>0,
            'c'    =>0,
            'cf'   =>0,
            'ct'   =>0,
            'data' =>$value
          )));
        }
      }

      $v = array_column($re, 'v');
      $c = array_column($re, 'c');
      $cf = array_column($re, 'cf');
      $ct = array_column($re, 'ct');
      $dd=array(
          'date'=>$this->date,
          'series'=>array(
              array('name'=>'自锁数','type'=>'line','yAxisIndex'=>0,'data'=>$c),
              array('name'=>'成交数','type'=>'line','yAxisIndex'=>1, 'data'=>$v),
              array('name'=>'冲突数','type'=>'line','yAxisIndex'=>1, 'data'=>$ct),
              array('name'=>'被冲突数','type'=>'line','yAxisIndex'=>1, 'data'=>$cf)
           )
      );
    } else {
      
      $dd=array(
            'date'=>$this->date ,
            'series'=>array()
        );
    }
    $this->ajaxReturn($dd);

  }



	/**
	 * 公用 获取列表
	 *
	 * @return array() || null
	 * 
	 **/
	public function getList_del(){
    
		$this->d = new CustomersGather;
		$this->setServiceQuery();
    $this->getDepartmentCount();
    $this->getGroupCount();
    $this->getUserCount();
    
    if(isset($_GET['department_id']) || isset($_GET['group_id'])){
    	$result = $this->getSelectCtrl();
      
	  }else{
      switch (I('get.type')) {
        case 'user':
             $result = $this->setReturnArr($this->users); //基于个人为条件查询
          break;
        case 'group':
            $result = $this->setReturnArr($this->groups); //基于团组为条件查询
          break;
        case 'department':
            $result = $this->setReturnArr($this->deps);
           break;
        default:
           $result = $this->setReturnArr($this->deps); //基于部门为条件查询
          break;
      }
    }
    
		$this->ajaxReturn($result);
	}

	private function setServiceQuery(){
    $sort_field = I('get.sort_field', 'id');
    $sort_order = I('get.sort_order', 'asc');
    $start = I('get.start');
    $end = I('get.end');
    $today = Date('Y-m-d');
    $sort_field = empty($sort_field) ? 'id' :$sort_field;
    

    if($end >=  $today){
      $end = date('Y-m-d',strtotime("-1 day"));
    }


    $this->d
         ->setDate($start, $end)
         ->setOrder($sort_field." ".$sort_order);
  }

  private function splitList($list){
  	$page = I('get.p',0);
  	$re = array_chunk($list, $this->pageSize);
  	return $re[$page-1];
  }

	/**
	* 基于部门的录入统计
	*/
	private function getDepartmentCount(){
 
    $this->deps = arr_to_map($this->d->getDepartment(),'id');
	}

	/**
	* 基于小组的录入统计
	*/
	private function getGroupCount(){
		$this->groups = arr_to_map($this->d->getAllGroups(),'id');
	}

	/**
	* 基于人的录入统计
	*/
	private function getUserCount(){
		$this->users = arr_to_map($this->d->getAllUsers(),'user_id');
	}

  private function setReturnArr($arr){
  	return array('list'=>$this->splitList($arr), 'count'=>count($arr));
  }


  protected function treeOb(){
  	$treeOb = new TreeController;
  	return $treeOb;
  }

  //获取所有部门下拉
  public function getDeps($status,$depId){
  	$treeOb = $this->treeOb();
  	$arr = $treeOb->getAlldep($depId,"id,name");
  	$this->ajaxReturn($arr);
  }

  //获取所选属部门的小组
  public function getGroups($department_id){
  	$treeOb = $this->treeOb();
  	$arr = $treeOb->getAllGoups($department_id, 'id,name');
  	$this->ajaxReturn($arr);
  }

  //获取所选小组下的员工
  public function getUsers($department_id,$group_id){
  	$treeOb = $this->treeOb();
  	$arr = $treeOb->getGroupEmployee($department_id,$group_id, 'id,realname');
    // echo M()->getLastSql();die();
  	$this->ajaxReturn($arr);
  }

  //判断下拉框条件
  protected function getSelectCtrl(){
    $department_id = I('get.department_id');
    $group_id = I('get.group_id');
    $type = I('get.type');
    $arr = array();

    if($type=="user" && isset($_GET['department_id']) && !isset($_GET['group_id'])){
      foreach ($this->users as $k => $v) {
        if($v['department_id'] == $department_id){
          $arr[] = $v;
        }
      }
      $result = array('list'=>$this->splitList($arr), 'count'=>count($arr));
    }elseif ($type=="user" && isset($_GET['department_id']) && isset($_GET['group_id'])) {
      $res = M('user_info')->where(array('department'=>$department_id,'group_id'=>$group_id))->getField('user_id',true);
      foreach ($res as $k => $v) {
        if(array_key_exists($v,$this->users)){

          $arr[] = $this->users[$v];
        }
      }
      $result = array('list'=>$this->splitList($arr), 'count'=>count($arr));
    }elseif ($type=="group" && isset($_GET['department_id']) && !isset($_GET['group_id'])) {
      foreach ($this->groups as $k => $v) {
        if($v['department_id'] == $department_id){
          $arr[] = $v;
        }
      }
      $result = array('list'=>$this->splitList($arr), 'count'=>count($arr));
    }elseif ($type=="group" && isset($_GET['department_id']) && isset($_GET['group_id'])) {
      $arr[] = $this->groups[$group_id];
      $result = array('list'=>$arr, 'count'=>count($arr));
    }elseif ($type=="department" && isset($_GET['department_id']) && !isset($_GET['group_id'])) {
      if ($this->deps[$department_id]) {
        $arr[] = $this->deps[$department_id];
      } 
      
      $result = array('list'=>$arr, 'count'=>count($arr));
    }

    return $result;

  }

  /**
  * 设置图表
  * @param array
  * @return array
  *
  */
  public function echarts($id,$start,$end,$name,$type){
  	//$this->setDate($start,$end);
  	$this->getDateFromRange($start,$end);
  	$this->ajaxReturn($this->setOption($id,$type));
  }
  
  protected function setDate($start,$end){
    $re = M('statistics_usercustomers')->where(" `date`>='".$start."' AND  `date`<='".$end."'")
                                       ->field(" distinct `date` ")->select();
    $this->date = array_column($re, 'date');

  }

  protected function setOption($id,$type){
    if ($this->date) {
        $dateRe = $this->getDateString();
        $where = $this->getCondition($id,$type);

        $sql = "select sum(today_v) as v , sum(create_num) as c,sum(conflict_from) as cf, sum(conflict_to) as ct,date  from statistics_usercustomers where $where  `date` in (".implode(',', $dateRe).") group by `date`";
        $re = M()->query($sql);
        
        $dates = array_column($re, 'date');
        foreach ($this->date as $key => $value) {
          if (!in_array($value, $dates)) {
            array_splice($re, $key, 0, array(array(
              'v'    =>0,
              'c'    =>0,
              'cf'   =>0,
              'ct'   =>0,
              'data' =>$value
            )));
          }
        }

        $v = array_column($re, 'v');
        $c = array_column($re, 'c');
        $cf = array_column($re, 'cf');
        $ct = array_column($re, 'ct');
        $dd=array(
            'date'=>$this->date,
            'series'=>array(
                array('name'=>'自锁数','type'=>'line','data'=>$c),
                array('name'=>'成交数','type'=>'line','data'=>$v),
                array('name'=>'冲突','type'=>'line','data'=>$ct),
                array('name'=>'被冲突','type'=>'line','data'=>$cf),
                /*array('name'=>'自锁数曲线','type'=>'line',"smooth"=>  true,'data'=>$c),
                array('name'=>'成交数曲线','type'=>'line',"smooth"=>  true,'data'=>$v),
                array('name'=>'冲突曲线','type'=>'line',"smooth"=>  true,'data'=>$ct),
                array('name'=>'被冲突曲线','type'=>'line',"smooth"=>  true,'data'=>$cf),*/
               //array('name'=>'成交数','type'=>'bar',"barWidth"=> '20%','data'=>$v),
             )
        );
        
    } else {
        $dd=array(
            'date'=>$this->date ,
            'series'=>array()
        );
    }
    
    return $dd;
  }

  private function getDateString(){
    $re = array();
    foreach ($this->date as $value) {
        $re[] = "'".$value."'";
    }
    return $re;
  }


  private function getCondition($id,$type){
  	$where = '';
  	switch ($type) {
  		case 'department':
  			$where = "department_id=$id and ";
  			break;
  		case 'group':
  			$where = "group_id=$id and ";
  			break;
  		case 'user':
  			$where = "user_id=$id and ";
  			break;
  		default:
  			# code...
  			break;
  	}

  	return $where;
  }

	/**
	 * 获取指定日期段内每一天的日期
	 * @param Date $start 开始日期
	 * @param Date $end  结束日期
	 * @return Array
	 */
	private function getDateFromRange($start, $end){
	  $stimestamp = strtotime($start);
	  $etimestamp = strtotime($end);
	  // 计算日期段内有多少天
	  $days = ($etimestamp-$stimestamp)/86400+1;
	  // 保存每天日期
	  $date = array();
	  for($i=0; $i<$days; $i++){
	    $date[] = date('Y-m-d', $stimestamp+(86400*$i));
	  }

    $this->date = $date;
    //va_dump($this->date);die();
	}
}