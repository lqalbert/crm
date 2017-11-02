<?php
namespace Home\Controller;
use Think\Controller;

class SearchMaterialController extends CommonController{

  protected $pageSize = 15;
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
    //echo $this->M->getLastSql();die();
		$this->setQeuryCondition();
		$list = $this->M->page(I('get.p',0). ','. $this->pageSize)->select();
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
    $user_name = I('get.user_name');
    $content = I('get.content');
    $title = I('get.title');
    $start = I('get.start');
    $end = I('get.end');
    $this->M->alias("pm")->join("user_info as ui on ui.user_id = pm.user_id")->where("pm.user_id <> $user_id and pm.type=0")
            ->field("pm.*,ui.realname as user");
    
    $userArr = M('quote_material')->where(array("user_id"=>$user_id))->getField('pm_id',true);
    if($userArr){
    	$this->M->where(array('pm.id'=>array('NOT IN',$userArr)));
    }
    
    if(I('get.sort_field')){
    	$this->M->order("$sort_field $sort_order");
    }else{
    	$this->M->order('pm.id desc');
    }
    
    if(I('get.user_name')){
    	$user_id = M('user_info')->where(array("realname"=>array("LIKE",'%'.$user_name.'%')))->getField("user_id",true);
      if($user_id){
        $this->M->where(array('pm.user_id'=>array('IN',$user_id)));
      }else{
        $this->M->where(array('pm.user_id'=>$user_id));
      }
  	    
    }
    
    if(I('get.content')){
    	$this->M->where(array('pm.content'=>array('LIKE','%'.$content.'%')));
    }

    if(I('get.title')){
      $this->M->where(array('pm.title'=>array('LIKE','%'.$title.'%')));
    }

    if(I('get.start') && I('get.end')){
      $this->M->where(array('pm.ct_time'=>array(array('EGT',$start." 00:00:00"),array('ELT',$end." 23:59:59"))));
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
    $list = $this->M->page(I('get.p',0). ','. $this->imgPageSize)->select();
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
    $user_name = I('get.user_name');
    $title = I('get.title');
    $start = I('get.start');
    $end = I('get.end');
    $this->M->alias("pm")->join("user_info as ui on ui.user_id = pm.user_id")->where("pm.user_id <> $user_id and pm.type=1")
            ->field("pm.*,ui.realname as user");
    
    $userArr = M('quote_material')->where(array("user_id"=>$user_id))->getField('pm_id',true);
    if($userArr){
      $this->M->where(array('pm.id'=>array('NOT IN',$userArr)));
    }
    
    if(I('get.user_name')){
      $user_id = M('user_info')->where(array("realname"=>array("LIKE",'%'.$user_name.'%')))->getField("user_id",true);
      if($user_id){
        $this->M->where(array('pm.user_id'=>array('IN',$user_id)));
      }else{
        $this->M->where(array('pm.user_id'=>$user_id));
      }

    }
    
    if(I('get.title')){
      $this->M->where(array('pm.title'=>array('LIKE','%'.$title.'%')));
    }

    if(I('get.start') && I('get.end')){
      $this->M->where(array('pm.ct_time'=>array(array('EGT',$start." 00:00:00"),array('ELT',$end." 23:59:59"))));
    }
 
  }

  /**
   * 获取文字引用排名
   * @return [arr] [<description>]
   */
  public function getTextQuoteRank(){
    $user_id = session('uid');
    $TextQuoteRank = $this->M->field('id,user_id,content,title,y_num')->where(array("type"=>array("EQ",0)))
                       ->order('y_num desc')->limit("0,15")->select();
    $this->ajaxReturn(array("TextQuoteRank"=>$TextQuoteRank));                 
  }

  /**
   * 获取图片引用排名
   * @return [arr] [<description>]
   */
  public function getImgQuoteRank(){
    $user_id = session('uid');
    $ImgQuoteRank = $this->M->field('id,user_id,content,title,y_num')->where(array("type"=>array("EQ",1)))
                       ->order('y_num desc')->limit("0,15")->select();
    $this->ajaxReturn(array("ImgQuoteRank"=>$ImgQuoteRank));                 
  }

  /**
   * 公共引用图片和文字
   * @return [type] [description]
   */
  public function quote(){
    $qm = M('quote_material');
    $user_id = session('uid');
    $ids = I('post.pm_ids');
    $type = I('post.type');
    $row = $_POST['row'];
    $qm->startTrans();
    foreach ($ids as $k => $v) {
      $res = $qm->add(array("user_id" => $user_id,"pm_id" =>$v));
      if(!$res){
        $qm->rollback();
        $this->error('引用失败');
      }
    }
    
    foreach ($row as $key => $value) {
      $data = array(
          "type" => $type,
          "user_id"=>$user_id,
          "title" => $value['title'],
          "content"=>$value['content']
        );
      $re = $this->M->add($data);
      if(!$re){
        $qm->rollback();
        $this->error('引用失败');
      }
    }

    $incRes = $this->M->where(array("id"=>array('IN',$ids)))->setInc('y_num');
    if(!$incRes){
      $qm->rollback();
      $this->error('引用失败');
    }

    $qm->commit();
    $this->success("引用成功");
    
  }

  /**
   * 文字图片排名引用
   */
  public function commonRankQuote(){
    $qm = M('quote_material');
    $user_id = session('uid');
    $pm_id = I('post.pm_id');
    $type = I('post.type');
    $title = I('post.title');
    $content = I('post.content');
    
    $findRes = $qm->where(array('user_id'=>$user_id,'pm_id'=>$pm_id))->find();
    if($findRes){
      $this->error('此素材已存在您的仓库');
    }else{
      $qm->startTrans();
      $res = $qm->add(array("user_id" => $user_id,"pm_id" =>$pm_id));
      if(!$res){
        $qm->rollback();
        $this->error('引用失败');
      }
      $data = array(
          "type" => $type,
          "user_id"=>$user_id,
          "title" => $title,
          "content"=>$content
        );
      $re = $this->M->add($data);
      if(!$re){
        $qm->rollback();
        $this->error('引用失败');
      }

      $incRes = $this->M->where(array("id"=>array('IN',$pm_id)))->setInc('y_num');
      if(!$incRes){
        $qm->rollback();
        $this->error('引用失败');
      }

      $qm->commit();
      $this->success("引用成功");
    }

  }

















}