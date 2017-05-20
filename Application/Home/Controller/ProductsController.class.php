<?php
namespace Home\Controller;
use Common\Lib\User;
use Home\Model\RoleModel;
use Home\Model\ProductModel;
class ProductsController extends CommonController{
	protected $table="Product";
	protected $pageSize = 13;
  
	public function index(){
    //var_dump($this->M->getUserId());die;
    $this->display();
	}

  //表格数据获取
	public function getList(){
		$this->setQeuryCondition();
    $count = (int)$this->M->count();
    $this->setQeuryCondition();
    $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order('id desc')->select();
    foreach ($list as $k => $v) {
      $dep_user=M('department_basic as db')->join('user_info as ui on ui.department_id=db.id')
               ->where(array('ui.user_id'=>$v['operator_id']))->getField("concat(db.name,'-',ui.realname) as user");
      foreach ($dep_user as $key => $val) {
          $list[$k]['operator']=$val['user'];
      }
    }
    $result = array('list'=>$list, 'count'=>$count);
		$this->ajaxReturn($result);

	}
  

  //设置查询
  public function setQeuryCondition(){

		if (I('get.name')) {
			$this->M->where(array("name"=> array('like', "%".I('get.name')."%")));
		}

    $this->M->where( array('status'=>array('NEQ', ProductModel::DELETE_STATUS) ) );

  }

  //查询回去产品列表
  public function getProduct(){
    $id = I('get.id',1);
    $list = $this->M->where( array('id'=>$id,'status'=>array('NEQ', ProductModel::DELETE_STATUS)))->find();
    $this->ajaxReturn($list);
  }




























}