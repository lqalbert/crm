<?php
namespace Home\Controller;
use Think\Controller;

class GenerateUrlController extends CommonController{

	protected $pageSize = 10;
  protected $imgPageSize = 12;
  protected $imgTextPageSize = 12;
  protected $table='promotion_material';

  public function index(){
    //echo(strip_tags($str));die();
    $this->assign("mainUrl",BETA_PROMATERIAL);
    $this->assign("Test1Url",BETA_TEST1);
    $this->assign("BetaPreview",BETA_PREVIEW);
    $this->assign("imgPageSize",$this->imgPageSize);
    $this->assign("imgTextPageSize",$this->imgTextPageSize);
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
    $content = I('get.content');

  //   $arr = M('quote_material')->where("user_id = $user_id")->group("pm_id")->getField("pm_id",true);
    // $condition['user_id'] = $user_id;
    // $condition['id'] = array("IN", $arr);
    // $condition['_logic'] = 'OR';
    $user_id = session('uid');
    $sort_field = I('get.sort_field', 'y_num');
    $sort_order = I('get.sort_order', 'desc');
    $content = I('get.content');
    $this->M->where(array("user_id"=>$user_id,"type"=>0));
     
    if(I('get.sort_field')){
      $this->M->order("$sort_field $sort_order");
    }else{
      $this->M->order('id desc');
    }
    
    if(I('get.content')){
      $this->M->where(array('content'=>array('LIKE','%'.$content.'%')));
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
    $this->M->alias("pm")->join("user_info as ui on ui.user_id = pm.user_id")->where("pm.user_id = $user_id and pm.type=1")
            ->field("pm.*,ui.realname as user");

  }

  /**
  *  获取图文列表
  *
  * @return array() || null
  * 
  **/
  public function getImgTextList(){

    $this->ImgTextQeuryCondition();
    $count = (int)$this->M->count();
    //echo $this->M->getLastSql();die();
    $this->ImgTextQeuryCondition();
    $list = $this->M->page(I('get.p',0). ','. $this->imgTextPageSize)->select();
    $list = $this->stripHtml($list);
    $result = array('list'=>$list, 'count'=>$count);
    if (IS_AJAX) {
      $this->ajaxReturn($result);
    }else{
      return $result;
    }

  }

  /**
   * 提出html标签
   */
  protected function stripHtml($list){
    foreach ($list as $k => $v) {
      $list[$k]['content'] = strip_tags($v['content']);
    }

    return $list;
  }

  /**
  * 设置图文查询参数
  * 
  * @return null
  */
  public function ImgTextQeuryCondition(){
    $user_id = session('uid');
    $sort_field = I('get.sort_field', 'y_num');
    $sort_order = I('get.sort_order', 'desc');
    $content = I('get.content');
    $this->M->alias("pm")->join("user_info as ui on ui.user_id = pm.user_id")->where("pm.user_id = $user_id and pm.type=4")
            ->field("pm.*,ui.realname as user");

    if(I('get.sort_field')){
      $this->M->order("$sort_field $sort_order");
    }else{
      $this->M->order('id desc');
    }
    
    if(I('get.content')){
      $this->M->where(array('content'=>array('LIKE','%'.$content.'%')));
    }
  }

  /**
   * 设置预览、保存和生成链接
   */
  public function setPreviewSave(){
    $content = $_POST['content'];
    $user_id = session('uid');

    $findRes = $this->M->where(array('user_id'=>$user_id,'content'=>$content))->getField('id');
    if($findRes){
      //redirect("http://www.baidu.com");
      $this->ajaxReturn(array('p'=>$findRes,'uid'=>$user_id));
    }else{
      $dataArr = array(
          "content" => $content,
          "user_id" => $user_id,
          "type" => 4
        );
      $res=$this->M->add($dataArr);
      if($res){
        $this->ajaxReturn(array('p'=>$res,'uid'=>$user_id));
      }else{
        $this->error($this->M->getError());
      }
    }

  }

  /**
   * 查看某一图文
   */
  public function showImgTextRow($id){
    $user_id = session('uid');
    $res = $this->M->where(array('id'=>$id,'user_id'=>$user_id))->getField('content');
    if($res){
      $this->ajaxReturn(array('content'=>$res));
    }else{
      $this->error($this->M->getError());
    }



  }
 














}