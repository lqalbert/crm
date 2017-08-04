<?php
namespace Home\Controller;

use Home\Model\DistributeModel;

class DistributeController extends CommonController{

    protected $table = "Distribute";

    public function index(){

        $this->assign("types", DistributeModel::$type);
        $this->display();   
    }
}