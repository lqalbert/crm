<?php
namespace Home\Controller;

class UploadController extends CommonController {

	private $upload = null;

	private function upload($authSub = false) {
		$folder=I('post.folder');
		//$folder= $_POST['folder'];
		$this->upload = new \Think\Upload();// 实例化上传类
	    $this->upload->maxSize   =     3145728 ;// 设置附件上传大小3M
	    $this->upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	    $this->upload->rootPath  =     './Upload/'.$folder.'/'; // 设置附件上传根目录
	    $this->upload->autoSub   =     $authSub;
	    return $this->upload->upload();
	}

	public function index(){  //
	    $info   =   $this->upload();
	    if(!$info) {// 上传错误提示错误信息
	        $this->error($this->upload->getError());
	    }else{// 上传成功
	        $this->ajaxReturn(array('path'=>$info['file']['savename']));
	    }
	}

	public function index2(){
		
	    $info   =   $this->upload(true);
	    if(!$info) {// 上传错误提示错误信息
	        $this->error($this->upload->getError());
	    }else{// 上传成功
	        $this->ajaxReturn(array('path'=>substr($this->upload->rootPath, 1 ).$info['file']['savepath'].$info['file']['savename']));
	    }
	}

  //对layui上传的图片单独处理
	public function index3($folder,$authSub=true){
		$upload = new \Think\Upload();// 实例化上传类
	    $upload->maxSize   =     3145728 ;// 设置附件上传大小3M
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	    $upload->rootPath  =     './Upload/'.$folder.'/'; // 设置附件上传根目录
	    $upload->autoSub   =     $authSub;
	    $info   = $upload->upload();
	    if(!$info) {// 上传错误提示错误信息
				$this->ajaxReturn(array('yes'=>'上传失败，换个姿势再来一次！'),'JSON');
	    }else{// 上传成功
	    	$user_id=session('uid');
	    	foreach ($info as $file) {
	    		$data=array('head'=>substr($upload->rootPath, 1 ).$file['savepath'].$file['savename']);
	    	}
	    	$res=M('user_info')->where(array('user_id'=>$user_id))->save($data);
	    	if($res){
     			$_SESSION['account']['userInfo']['head']=$data['head'];
     			$this->ajaxReturn(array('yes'=>'上传成功，请刷新查看！'),'JSON');
	    	}else{
     			$this->ajaxReturn(array('yes'=>'上传失败，换个姿势再来一次！'),'JSON');
	    	}   
	    }
	}

    //处理微信和QQ二维码截图裁图缩图
    public function imageCropperUpload($folder,$authSub=true){
      $upload = new \Think\Upload();// 实例化上传类
      $upload->maxSize   =     3145728 ;// 设置附件上传大小3M
      $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
      $upload->rootPath  =     './Upload/'.$folder.'/'; // 设置附件上传根目录
      $upload->autoSub   =     $authSub;
      $info   = $upload->upload();
      if(!$info) {// 上传错误提示错误信息
        $this->error($upload->getError());
      }else{// 上传成功
      	$user_id=session('uid');
      	foreach ($info as $file) {
      		$qrPath =substr($upload->rootPath, 1 ).$file['savepath'].$file['savename'];
      		$data=array($folder=>$qrPath);
      	}
  			$image = new \Think\Image(); 
  			//将图片裁剪为选择的尺寸并保存为$qrPath
  			$image->open('./'.$qrPath);
  			$image->crop(I('post.w'), I('post.h'),I('post.x'),I('post.y'))->save('./'.$qrPath);
      	$res=M('user_info')->where(array('user_id'=>$user_id))->save($data);
      	if($res){
     			$_SESSION['account']['userInfo'][$folder]=$data[$folder];
          $this->ajaxReturn(array('info'=>'二维码上传成功！','path'=>$qrPath,'folder'=>$folder));
      	}else{
          $this->error($this->M->getError());
      	}
      }
    }


  //处理推广素材页Ueditor上传图片
  public function handleUeditorUpload($folder,$authSub=true){
  	$ueditor_config = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("./Public/plugins/ueditor/php/config.json")), true);  
    $action = $_GET['action'];  
    switch ($action) {  
      case 'config':  
        $result = json_encode($ueditor_config);  
        break;  
          /* 上传图片 */  
      case 'uploadimage':  
          /* 上传涂鸦 */  
      case 'uploadscrawl':  
          /* 上传视频 */  
      case 'uploadvideo':  
          /* 上传文件 */  
      case 'uploadfile':  
        $upload = new \Think\Upload();  
        $upload->maxSize = 3145728;  
        $upload->rootPath = './Upload/'.$folder.'/';  
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->autoSub = $authSub;
        $info = $upload->upload();  
        //var_dump($info);die();
        if (!$info) {  
          $result = json_encode(array(  
            'state' => $upload->getError(),  
          ));  
        } else { 
          $url = BETA_CRM_URL . substr($upload->rootPath, 1 ) . $info["upfile"]["savepath"] . $info["upfile"]['savename'];  
          $result = json_encode(array(  
            'url' => $url,  
            'title' => $info["upfile"]['name'],  
            'original' => $info["upfile"]['name'],
            'state' => 'SUCCESS'
          ));  
        }  
        break;  
      default:  
        $result = json_encode(array(  
        	'state' => '请求地址出错'  
        ));  
        break;  
    }  
    /* 输出结果 */  
    if (isset($_GET["callback"])) {  
      if (preg_match("/^[\w_]+$/", $_GET["callback"])) {  
          echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';  
      } else {  
          echo json_encode(array(  
                'state' => 'callback参数不合法'  
               ));  
      }  
    } else {  
      echo $result;  
    }  
 
  }

  //百度UMeditor上传图片 只实现上传图片的功能
  // 返回格式
  // {
  //     "originalName":"Chrysanthemum.jpg",
  //     "name":"15048371185276.jpg",
  //     "url":"upload\/20170908\/15048371185276.jpg",
  //     "size":879394,
  //     "type":".jpg",
  //     "state":"SUCCESS"
  // }

  // {"upfile":
  //     {
  //         "name":"Jellyfish.jpg",
  //         "type":"image\/jpeg",
  //         "size":775702,
  //         "key":"upfile",
  //         "ext":"jpg",
  //         "md5":"5a44c7ba5bbe4ec867233d67e4806848",
  //         "sha1":"3b15be84aff20b322a93c0b9aaa62e25ad33b4b4",
  //         "savename":"59b203f7d6038.jpg",
  //         "savepath":""
  //     }
  // }
  public function uploadUM(){
    $this->upload = new \Think\Upload();// 实例化上传类
    $this->upload->maxSize   =     3145728 ;// 设置附件上传大小3M
    $this->upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    $this->upload->rootPath  =     './Upload/'; // 设置附件上传根目录
    $this->upload->autoSub   =     true;
    $info =  $this->upload->upload();
    if(!$info) {// 上传错误提示错误信息
      $this->error(array('state'=>'ERROR'));
    }else{// 上传成功
      $re = array(
        'originalName'=>$info['upfile']['name'],
        'name'        =>$info['upfile']['savename'],
        'url'         =>__ROOT__.substr($this->upload->rootPath, 1 ).$info['upfile']['savepath'].$info['upfile']['savename'],
        'size'        =>$info['upfile']['size'],
        'type'        =>"." . $info['upfile']['ext'],
        'state'       =>'SUCCESS'
        );
      $this->ajaxReturn(json_encode($re), 'EVAL');
    }
  }



}