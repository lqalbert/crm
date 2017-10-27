<?php
namespace Home\Controller;
use Think\Controller;

class MyMaterialController extends CommonController{

	protected $pageSize = 14;
	protected $imgPageSize = 15;
  protected $table='promotion_material';

	public function index(){
    $this->assign("mainUrl",BETA_PROMATERIAL);
    $this->assign("imgPageSize",$this->imgPageSize);
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
		$list = $this->M->page(I('get.p',0). ','. $this->pageSize)->order("id desc")->select();
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
    $sort_field = I('get.sort_field', 'y_num');
    $sort_order = I('get.sort_order', 'desc');
    $content = I('get.content');
    $title = I('get.title');
    $start = I('get.start');
    $end = I('get.end');
  	// $arr = M('quote_material')->where("user_id = $user_id")->group("pm_id")->getField("pm_id",true);
		// $condition['user_id'] = $user_id;
		// $condition['id'] = array("IN", $arr);
		// $condition['_logic'] = 'OR';
    $this->M->where(array("user_id"=>$user_id,"type"=>0));
     
    if(I('get.sort_field')){
    	$this->M->order("$sort_field $sort_order");
    }else{
    	$this->M->order('id desc');
    }
    
    if(I('get.content')){
    	$this->M->where(array('content'=>array('LIKE','%'.$content.'%')));
    }

    if(I('get.title')){
      $this->M->where(array('title'=>array('LIKE','%'.$title.'%')));
    }

    if(I('get.start') && I('get.end')){
      $this->M->where(array('ct_time'=>array(array('EGT',$start." 00:00:00"),array('ELT',$end." 23:59:59"))));
    }

	}

  public function saveEdit(){
  	$user_id = session('uid');
  	$id = I('post.id');
  	$content = $_POST['content'];
    $result = $this->M->where(array("id"=>$id,"user_id"=>$user_id))->save(array("content"=>$content));
    if(!$result){
      $this->error("重复内容修改");
    }else{
    	$this->success("修改成功");
    }

  }


  /**
  *  获取图片列表
  *
  * @return array() || null
  * 
  **/
  public function getImgList(){

    $this->ImgQeuryCondition();
    $count = (int)$this->M->count();
    //echo $this->M->getLastSql();die();
    $this->ImgQeuryCondition();
    $list = $this->M->page(I('get.p',0). ','. $this->imgPageSize)->order("id desc")->select();
    $result = array('list'=>$list, 'count'=>$count);
    
    if (IS_AJAX) {
      $this->ajaxReturn($result);
    }else{
      return $result;
    }

  }

  /**
  * 设置图片查询参数
  * 
  * @return null
  */
  public function ImgQeuryCondition(){
    $user_id = session('uid');
    $title = I('get.title');
    $start = I('get.start');
    $end = I('get.end');
    $this->M->alias("pm")->join("user_info as ui on ui.user_id = pm.user_id")->where("pm.user_id = $user_id and pm.type=1")
            ->field("pm.*,ui.realname as user");

    if(I('get.title')){
      $this->M->where(array('pm.title'=>array('LIKE','%'.$title.'%')));
    }

    if(I('get.start') && I('get.end')){
      $this->M->where(array('pm.ct_time'=>array(array('EGT',$start." 00:00:00"),array('ELT',$end." 23:59:59"))));
    }
  }

  /**
   * 删除图片
   * @return [json] [info]
   */
  public function delImg(){
  	$user_id = session('uid');
  	$id = I('get.id');
    $result = $this->M->where(array("id"=>$id,"user_id"=>$user_id))->delete();
    if(!$result){
      $this->error("删除失败");
    }else{
    	$this->success("所选图片已删除");
    }
  }




























}