<?php
namespace Home\Controller;

use Home\Model\DistributeModel;

class DistributeController extends CommonController{

    protected $table = "Distribute";


    public function index(){

        $this->assign("types", DistributeModel::$type);
        $this->display();   
    }

    public function edit(){
        // $data = $_POST;
        $data       = array();
        $data['id'] = I("post.id");
        $config     = array();
        $config['limina'] = I("post.limina");
        $config['type']   = I("post.type");
        $config['list']   = I("post.percent");
        
        $data['config'] = json_encode($config);

        if ($this->M->create($data, 2) && ($this->M->save() !== false) )  {
            $this->success(L('EDIT_SUCCESS'));
        } else {
            $this->error($this->M->getError());
        }
    }

    public function view(){
        $this->setRow();

        $this->assign("pageSize", 0);
        $this->display("_view");
    }

    private function setRow(){
        $roleName = $this->getRoleEname();
        $funcName = $roleName."Condition";
        if (method_exists($this, $funcName)) {
           call_user_func(array($this, $funcName));
        } else {
          $this->commonCondition();
        }
    }

    private function goldCondition(){
        $row = $this->M->where(array('obj_id'=>0))->find();
        if ($row) {
            $this->assign("id",     $row['id']);
            $this->assign("limina", $row['limina']);
            $this->assign("type",   $row['type']);
            $this->assign("optionList", D("Department")->getSalesDepartments("id,name", 1));
            $this->assign("percent", $row['config']);
        } else {
            $this->assign("id",     0);
            $this->assign("limina", 0);
            $this->assign("type",   "1");
            $this->assign("optionList", D("Department")->getSalesDepartments("id,name", 1));
            $this->assign("percent", "[]");
        }
    }
}