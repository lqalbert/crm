<?php
namespace Home\Controller;
use Common\Lib\User;

class DepartmentDivisionController extends CommonController {
    protected $table = 'department_division';


    public function index(){
        $this->assign('dm', $this->getDM());
        $this->display();
    }


    public function setQeuryCondition() {
        $this->M->where(array('status'=> 1));
    }

    public function delete() {
        if ($this->M->data(array('status'=>-1))->where(array('id'=>array('in', I("post.ids"))))->save()) {
            $this->success(L('DELETE_SUCCESS'));
        } else {
            $this->error(L('DELETE_ERROR').$this->M->getError());
        }
    }

    private function getDM(){
        return D("User")->getDM();
    }


}