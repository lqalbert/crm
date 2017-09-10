<?php
namespace Home\Controller;


class CounselorBriefController extends CommonController{
    protected $table="counsel_basic";
    protected $pageSize = 11;


    public function index(){
        if ($this->M->where(array("coun_id"=>session('uid')))->find()) {
            redirect(U("editView"));
        }  else {
            redirect(U("addView"));
        }
    }


    public function addView(){
        $this->assign("coun_id", session('uid'));
        $this->assign('pageSize', $this->pageSize);
        $this->display();
    }


    public function editView(){
        $this->assign("row", $this->M->where(array("coun_id"=>session('uid')))->find());
        $this->assign('pageSize', $this->pageSize);
        $this->display();
    }

}