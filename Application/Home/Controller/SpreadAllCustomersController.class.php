<?php
namespace Home\Controller;

class SpreadAllCustomersController extends CommonController{

    private $depart_id = 0;
    protected $table="Customer";
    protected $pageSize = 11;

    public function _initialize(){
        parent::_initialize();
        $this->depart_id = $this->getDepartMentID();
    }

    private function getDepartMentID(){
        $re =  M('department_basic')->where(array('user_id'=>session('uid')))->getField('id');
        if (is_numeric($re)) {
            return $re;
        } else {
            return 0;
        }
    }



  public function index(){
    $D = D('Customer');
    $id = I('get.id', '');
    $department_id = I('get.department_id', '');
    $group_id = I('get.group_id', '');
    $this->assign('user_id', $id);
    $this->assign('department_id', $department_id);
    $this->assign('group_id', $group_id);
    $this->assign('customerType', $D->getType());
    $this->assign('sexType',      $D->getSexType());
    $this->assign('Quality',      $D->getQuality());
    $this->assign('Year',         $D->getYear());
    $this->assign('Income',       $D->getIncome());
    $this->assign('Sty',          $D->getStyle());
    $this->assign('Money',        $D->getMoney());
    $this->assign('Energy',       $D->getEnergy());
    $this->assign('Problem',      $D->getProblem());
    $this->assign('Mode',         $D->getMode());
    $this->assign('Attitude',     $D->getAttitude());
    $this->assign('Profession',   $D->getProfession());
    $this->assign('Intention',    $D->getIntention());
    $this->assign('Source',       $D->getSource());
    $this->assign('Departments',  D('Department')->getAllDepartments('id,name'));
    $this->display();
  }


  private function checkLikeField(){
      //改造成复合查询
      if (I('get.name')) {
          $this->M->where(array('name'=>array('like', I('get.name')."%")));
      }

      $complexWhere = array('_logic'=>'OR');
      $arrList = array('phone', 'qq', 'weixin', );
      foreach ($arrList as $value) {
          if (I('get.'.$value)) {
              $complexWhere['cc.'.$value] = array('like', I('get.'.$value)."%");
              $complexWhere['cc2.'.$value] = array('like', I('get.'.$value)."%");
          }
      }

      if (count($complexWhere)>1) {
          $this->M->where(array('_complex'=>$complexWhere));
      }
  }


  /**
  * 设置查询参数
  * 
  * @return null
  */
  public function setQeuryCondition() {
      $this->M->setShowCondition();
      //手机 QQ WEIXIN name
      $this->checkLikeField();
   
      //个人
      if (I('get.user_id')) {
          // $this->M->setUser(I('get.user_id')); //$this->where(array('salesman_id'=>$value));
          $this->M->where(array('customers_basic.user_id'=>I('get.user_id')));
      } else if(I('get.department_id')){
          $this->M->join('left join user_info as ufo on ufo.user_id=customers_basic.user_id')
          ->where(array('ufo.department_id'=>I('get.department_id')));
      }else if(I('get.group_id')){
          $this->M->join('left join user_info as ufo on ufo.user_id=customers_basic.user_id')
          ->where(array('ufo.group_id'=>I('get.group_id')));
      }


      // 如果一个时间都没传
      // 近3个月之内的
      // if ( empty(I('get.start')) 
      //      && empty(I('get.end')) 
      //      && empty(I('get.track_start')) 
      //      && empty(I('get.track_end')) 
      //      && strpos(I('get.field'),'transf') === false
      //      ) {
      //     // $this->M->setStart('created_at', D('Customer','Logic')->ThreeMonthsAge());
      // }

      $this->M->join(' customers_contacts as cc on customers_basic.id =  cc.cus_id  and cc.is_main = 1')
              ->join('left join customers_contacts as cc2 on customers_basic.id =  cc2.cus_id and cc2.is_main = 0')
              ->join('left join user_info as ui on customers_basic.salesman_id = ui.user_id')
            ->join('left join user_info as usi on customers_basic.user_id = usi.user_id')
            ->field('customers_basic.*,cc.qq,cc.phone,cc.weixin,cc.qq_nickname,cc.weixin_nickname, cc.is_main as cc_main,
                cc2.qq as qq2,cc2.phone as phone2,cc2.weixin as weixin2,cc2.qq_nickname as qq_nickname2,
                cc2.weixin_nickname as weixin_nickname2, ui.realname,usi.realname as lock_name');


     
      
  }



  public function _getList(){
      $this->setQeuryCondition();
      //没有 is_main
      $count = (int)$this->M->count();
      
      $this->setQeuryCondition();
      // D('Customer','Logic')->getJoinCondition($this->M);
      if (I('get.sort_field', null)) {
          $this->M->order(I('get.sort_field')." ". I('get.sort_order'));
      } else {
          $this->M->order('customers_basic.id desc');
      }
      $list = $this->M->page(I('get.p',0). ','. $this->pageSize)->select();
      //echo M()->getLastSql();die();
      $result = array('list'=>$list, 'count'=>$count);
      return $result;
  }


  /**
  *   获取跟踪信息
  *
  */
public function trackInfo(){
      $arr=D('Customer','Logic')->trackInfo($this->M);
    if (IS_AJAX) {
        $this->ajaxReturn($arr);
    }  else {
        return $arr;
    }
}










}