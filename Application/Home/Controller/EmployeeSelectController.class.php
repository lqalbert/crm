<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/8
 * Time: 15:08
 */

namespace Home\Controller;


class EmployeeSelectController extends CommonController
{
    protected $pageSize=12;
    protected $table='RbacUser';

    public function index(){
        $this->assign("sexType", array("未定义", "男", "女"));
        $this->assign('allRoles', D('Role')->getField('id,name', true));
        $this->display();
    }

    public function setQeuryCondition() {
        // $this->M->relation(true)->field('password',true)->where(array('no_authorized'=>0));
        $this->M->join('user_info ON rbac_user.id = user_info.user_id')
            ->join('department_basic as db on user_info.department_id=db.id', 'left')
            ->field('db.name as department_name,account,user_info.address,
		        	area_city,area_district,
		        	area_province,created_at,
		        	department_id,group_id,
		        	head,rbac_user.id,mphone,no_authorized,phone,
		        	qq,qq_nickname,realname,role_ename,role_id,sex,rbac_user.status,user_info.user_id,weixin,weixin_nikname,id_card,card_img,card_front,card_back,ip,location,lg_time,out_time')->where(array('no_authorized'=>0))
            ->where(array('rbac_user.status'=>I('get.status')));
        $arr=array('realname','qq','weixin','mphone');
        $num=0;
        foreach ($arr as $v){
            if(I('get.'.$v)==''){
                $num+=1;
            }
        }
        if($num==4){
            $this->M->where(array('account'=>'kajshdjkashjkdash123132'));
        }
        //账号查询
        /*if (isset($_GET['name'])) {
            $this->M->where(array('account'=>array('like', I('get.name')."%")));
        }*/
        //姓名查询
        if (isset($_GET['realname'])) {
            $this->M->where(array('realname'=>array('like', I('get.realname')."%")));
        }
        //qq查询
        if (isset($_GET['qq'])) {
            $this->M->where(array('qq'=>I('get.qq')));
        }
        //手机号查询
        if (isset($_GET['mphone'])) {
            $this->M->where(array('mphone'=>I('get.mphone')));
        }
        //微信号查询
        if (isset($_GET['weixin'])) {
            $this->M->where(array('weixin'=>I('get.weixin')));
        }

        $this->M->where(array('rbac_user.id'=>array('neq', session('uid'))));


    }
}