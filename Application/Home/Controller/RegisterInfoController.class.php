<?php
namespace Home\Controller;
use Home\Model\RegisterInfoModel;
// use Home\Model\CustomerModel;
class RegisterInfoController extends CommonController{
	protected $table = "RegisterInfo";
  protected $pageSize = 15;

  public function index(){
    $this->assign("moneyOptions", D("Customer")->getMoney());
  	$this->display();
  }

	/**
	*  获取列表
	*
	* @return array() || null
	* 
	**/
	public function getList(){

		$this->setQeuryCondition();
		$count = (int)$this->M->count();

		$this->setQeuryCondition();
		$list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('id desc')->select();

		$result = array('list'=>$list, 'count'=>$count);
	  
		if (IS_AJAX) {
			$this->ajaxReturn($result);
		}else{
			return $result;
		}

	}

	/**
	* 设置查询参数
	* 
	* @return null
	*/
	public function setQeuryCondition(){
		$user_id = session('uid');
		$this->M->where(array('user_id'=>$user_id));

		if(I('get.qq')){
			$this->M->where(array('qq'=>array('like', '%'.I('get.qq').'%')));
		}
    
    if(I('get.phone')){
    	$this->M->where(array('phone'=>array('like', '%'.I('get.phone').'%')));
    }

		$this->setTimeDiv('reg_time', I('get.start'), I('get.end'));
   
	}

  public function setTimeDiv($field, $start=null, $end=null){
    if ($start && $end) {
       $this->M->where(array($field=>array( array('EGT', $start." 00:00:00"), array('ELT', $end." 23:59:59"))));
    }else if($start){
      $this->setStart($field, $start);
    } else if($end){
      $this->setEnd($field, $end);
    }
  }

  public function setStart($field, $value){
    $this->M->where(array($field=>array('EGT', $value." 00:00:00")));
  }

  public function setEnd($field, $value){
    $this->M->where(array($field=>array('ELT', $value." 23:59:59")));
  }  


  public function leadIn(){
    $_POST['source'] = 11;
    $_POST['user_id'] = session('uid');
    $data = D("Customer")->create();

    if (!$data) {
        $this->error(D("Customer")->getError());
    }
    
    $id = D("Customer")->add();
    if (!$id) {
        
        $this->error(D("Customer")->getError());
    }

    $update = array('leadin'=>1);
    D("RegisterInfo")->where(array('id'=>I("post.reg_id")))->save($update);

    $this->success("操作成功");
  }



























}