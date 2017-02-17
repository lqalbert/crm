<?php
namespace Home\Controller;

class UserDetailController extends CommonController {

	public function user_id(){
        return $user_id = session("?uid")==false?'':session("uid");
    }

    public function index(){
        $info=M('user_info')->where(array('user_id'=>$this->user_id()))->find();
        $username=M('rbac_user')->where(array('id'=>$this->user_id()))->getField('account');
        $role=M('rbac_role')->where(array('id'=>$info['role_id']))->getField('name');
        $info['role_name']=$role;
        $info['username']=$username;
        $this->assign('info',$info);
        $this->display();
	  }

    //修改个人信息
    public function edit(){
       unset($_POST['head']);
       $user=M('user_info');
       $re=$user->where(array('user_id'=>$this->user_id()))->save($user->create($_POST,2));
       if($re){
         $this->ajaxReturn(array('yes'=>'修改成功，刷新一下看看吧！'),'JSON');
       }else{
         $this->ajaxReturn(array('yes'=>'出错啦，换个姿势再来一次！'),'JSON');
       }
    }

    //显示设置密码页面
    public function changepwd(){
      $username=M('rbac_user')->where(array('id'=>$this->user_id()))->getField('account');
      $info['username']=$username;
      $this->assign('info',$info);
      $this->display();
    }

    //修改密码
    public function updatepwd(){
      //var_dump(I('post.'));
      $ob = D('rbac_user');
      $where=array('account'=>I('post.username'),'password'=>md5(I('post.password')),'id'=>$this->user_id());
      $result = $ob->relation('userInfo')->where($where)->find();
      if($result){
        $data=array(
           'password'=>md5(I('post.newpwd1')),
        );
        $re=$ob->where(array('id'=>$result['id']))->save($data);
        if($re){
          session("uid",null);
          session("account",null);
          session('[destroy]');
          $this->ajaxReturn(array('yes'=>'修改成功！换个姿势重新登录！'),'JSON');
        }else{
          $this->ajaxReturn(array('yes'=>'出错啦，换个姿势再来一次！'),'JSON');
        }
      }else{
        $this->ajaxReturn(array('yes'=>'出错啦，换个姿势再来一次！'),'JSON');
      }
      
    }

}