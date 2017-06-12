<?php
namespace Home\Controller;
use Home\Model\RoleModel;
class IndexController extends CommonController {

	protected $pageSize = 4;


	public function index() {
		$tree = null;
    $ename = $this->getRoleEname();
    if($ename == RoleModel::GOLD){
    	$treeOb = new TreeController;
      $tree = $treeOb->setDepartment();
    }
    $this->assign('tree',$tree);
		$this->display ();
	}

	public function main() {

		// $userInfo = session('account')['userInfo'];
		// $arr=M('user_info as ui')->join("group_basic as gb on gb.id=ui.group_id")
		//      ->join('department_basic as db on db.id=ui.department_id')
		//      ->field('ui.*,gb.name as g_name,db.name as d_name')
		//      ->where(array('ui.user_id'=>$userInfo['user_id']))->find(); 
		
	 //  echo M('user_info as ui')->getLastSql();die();
		$this->assign("pageSize", $this->pageSize);
		$this->assign('NoticeType', D('sys_notice')->getType());
		$this->display();
	}


	public function getList(){
	    $count = (int)D('SysNotice')->where(array('status'=>array('NEQ', -1)))->count();
	    $list = D('SysNotice')->where(array('status'=>array('NEQ', -1)))->page(I('get.p',0). ','. $this->pageSize)->order('id desc')->select();
	    $result = array('list'=>$list, 'count'=>$count);
		$this->ajaxReturn($result);
	}



	//屏幕解锁
	public function lock(){
         echo '1';
	}
	//检查锁状态
	public function checkLock(){		
		$id=session('uid');
		$re=M('rbac_user')->where(array('id'=>$id,'lockpwd'=>md5(I('post.lockpwd'))))->find();
		if($re){
          echo 1;
		}else{
          echo 0;
		}
	}

	//培训学院 跳转地址
	public function up(){
		echo("<script language='javascript'>window.top.location.href='http://up.riign.cn/'</script>");
		//redirect('http://up.riign.cn/');
	}

	//素材库地址
	public function material(){
		echo("<script language='javascript'>window.top.location.href='http://www.riign.com/'</script>");
		//redirect('http://www.riign.com/');
	}


  //退出登录时间
  public function test(){
  	$user_id=session('uid');
  	M('rbac_user')->where(array('id'=>$user_id))->save(array('out_time'=>time()));
  }
















}