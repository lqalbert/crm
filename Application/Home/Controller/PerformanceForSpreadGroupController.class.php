<?php
namespace Home\Controller;

class PerformanceForSpreadGroupController extends PerformanceForSpreadController {

   

    protected $searchGroup = array(
                                array('value'=>'user','key'=>"显示队员" ),
                            );
    public function index(){

        $groupId = $this->getUserGroupId();
        $this->assign('groupId', $groupId);
        $this->assign('searchGroup', $this->getSearchGroup());

        $this->display();
    }

}