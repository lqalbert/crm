<?php
namespace Home\Controller;

use Home\Service\CustomersGather;


class SortCustomersCountDepartPersonController extends CommonController{

    protected $table = "";
    protected $pageSize = 30;
    protected $d = null;

    public function index(){
        $this->display();
    }


    public function getList(){
        $this->department_id = session('account')['userInfo']['department_id'];

        $this->d =   new CustomersGather;
        $sort_field = I('get.sort_field', 'id');
        $sort_order = I('get.sort_order', 'asc');
        $sort_field = empty($sort_field) ? 'id' :$sort_field;
        $this->d
             ->setDate(I('get.start'), I('get.end'))
             ->setOrder($sort_field." ".$sort_order);



        $list = $this->d->getDepartmentAllUsers($this->department_id);


        $result = array('list'=>$this->splitList($list), 'count'=>count($list));
        $this->ajaxReturn($result);
    }

    private function splitList($list){
        $page = I('get.p',0);
        $re = array_chunk($list, $this->pageSize);
        return $re[$page-1];
      }
}