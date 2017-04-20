<?php
namespace Home\Controller;
use Home\Model\CustomerLogModel;
use Home\Model\CustomerModel;
use Home\Model\DepartmentModel;
use Home\Model\RoleModel;
use Common\Lib\User;

class GroupCountController extends CommonController{
  protected $pageSize = 15;

  private function getDayBetween(){
    $date=I('get.dist', Date("Y-m-d")." 00:00:00");
    $start = Date("Y-m-d H:i:s",strtotime($date));
    $end = Date("Y-m-d H:i:s",strtotime("+1 day",strtotime($date)));
    return "cb.created_at > '$start' and cb.created_at < '$end' ";
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
    $result=$this->getGroupCount();
    $this->ajaxReturn($result);

  }

  private function getGroupName(){
  	$name = I('get.name',null);
    if(empty($name)){
    	return null;
    }else{
    	return "where gb.name='$name'";
    }

  }

  private function getOffset(){
    return (I('get.p',1)-1) * $this->pageSize;
  }


  /**
  *
  * 基于所有小组客户成交、录入、员工数的统计
  *
  */
  private function getGroupCount(){
    $count = M('group_basic')->where(array('status'=>1))->count();
    $list = M()->query("select gb.id,gb.name,IFNULL(uc.daycount,0) as daycount,IFNULL(uc.vcount,0) as vcount,count(uio.user_id) as ygcount from group_basic as gb left join
      (select count(case when cb.type='V' then cb.id end) as vcount,count(cb.id) as daycount,ui.user_id,ui.group_id from customers_basic as cb
       inner join user_info as ui on cb.user_id=ui.user_id where ". $this->getDayBetween() ." group by ui.group_id) as uc
       on gb.id=uc.group_id left join user_info as uio on gb.id=uio.group_id ". $this->getGroupName() ."group by gb.id limit ". $this->getOffset().",".$this->pageSize);
    return array('list'=>$list,'count'=>$count);
  }












}













