<?php
namespace Home\Controller;
use Think\Model;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\CustomerModel;
use Home\Model\RealInfoModel;
use Home\Logic\CustomerLogic;
use Home\Model\CustomerLogModel;
class CommonFindDetailController extends CommonController{
  
  /**
  *   获取跟踪信息
  *   
  */
	public function trackInfo(){                   
    $type=D('Customer')->getType(I('post.type'));
    $arr=M('customers_log')->where(" cus_id = ".I('post.cus_id')." AND (`track_type` <> 11 or `track_type` is null) ")->order('id desc')->select();
    foreach ($arr as $key => $value){
    	$arr[$key]['type']=$type;
      $dep_user=M('department_basic as db')->join('user_info as ui on ui.department_id=db.id')
               ->where(array('ui.user_id'=>$value['user_id']))->getField("concat(db.name,'-',ui.realname) as user");
      foreach ($dep_user as $k => $v) {
          $arr[$key]['user']=$v['user'];
      }
    	$arr[$key]['name']=I('post.name');
      $arr[$key]['track_type'] = $arr[$key]['track_type'] == null ?:D('CustomerLog')->getType((int)$arr[$key]['track_type']);
      $arr[$key]['step'] = $arr[$key]['step'] == null ?:D('CustomerLog')->getType((int)$arr[$key]['step']);
    }
		if (IS_AJAX) {
			$this->ajaxReturn($arr);
		}  else {
			return $arr;
		}
  }    
        
  /**
  *   获取客户资料
  *
  */
  public function findDealInfo(){
    $re= M('customers_buy')->where(array('cus_id'=> I('post.user_id'), 'status'=>1))->select();
    $this->ajaxReturn($re);
    /*$arr=M('deal_info as di')->join('user_info as ui on di.user_id=ui.user_id')->field('ui.realname,di.*')
         ->where(array('di.user_id'=>I('post.user_id'),'di.cus_id'=>I('post.cus_id')))->select();
		if (IS_AJAX) {
			$this->ajaxReturn($arr);
		}  else {
			return $arr;
		}*/
  }

  /**
  *   获取账号信息
  *
  */
  public function softwareInfo(){
  	$arr=M('software_account as sa')->join('user_info as ui on ui.user_id=sa.open_id')->field('ui.realname,sa.*')
         ->where(array('sa.user_id'=>I('post.user_id'),'sa.cus_id'=>I('post.cus_id')))->select();
		if (IS_AJAX) {
			$this->ajaxReturn($arr);
		}  else {
			return $arr;
		}
  }

  /**
  *   获取投诉信息
  *
  */
  public function complainInfo(){
    $cus_id = empty(I('post.condition')) ? I('post.id') : I('post.cus_id');
    $type=D('Customer')->getType(I('post.type'));
    $arr=M('customers_log')->where(array('cus_id'=>$cus_id,'track_type'=>array('EQ',11)))->order('id desc')->select();
    foreach ($arr as $key => $value){
      $arr[$key]['type']=$type;
      $dep_user=M('department_basic as db')->join('user_info as ui on ui.department_id=db.id')
               ->where(array('ui.user_id'=>$value['user_id']))->getField("concat(db.name,'-',ui.realname) as user");
      foreach ($dep_user as $k => $v) {
          $arr[$key]['user']=$v['user'];
      }
      $arr[$key]['name']=I('post.name');
      $arr[$key]['track_type'] = $arr[$key]['track_type'] == null ?:D('CustomerLog')->getType((int)$arr[$key]['track_type']);
      $arr[$key]['step'] = $arr[$key]['step'] == null ?:D('CustomerLog')->getType((int)$arr[$key]['step']);
    }
    if (IS_AJAX) {
      $this->ajaxReturn($arr);
    }  else {
      return $arr;
    }
  }   

  
























  
}












































?>