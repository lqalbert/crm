<?php
namespace Home\Controller;

class PerformanceForSpreadDepartController extends PerformanceForSpreadController {

   

    protected $searchGroup = array(
                                array('value'=>'user','key'=>"显示队员" ),
                                array('value'=>'group','key'=>"显示团组" ),
                               
                            );
    public function index(){

        $departId = $this->getUserDepartmentId();
        $this->assign('departId', $departId);
        $this->assign('searchGroup', $this->getSearchGroup());
        $this->assign('groups',   D("Group")->getAllGoups($departId, 'id,name') );

        $this->display();
    }

}