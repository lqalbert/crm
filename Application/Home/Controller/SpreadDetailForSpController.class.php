<?php
namespace Home\Controller;

class SpreadDetailForSpController extends CommonController{

    public function index(){
        $role = $this->getRoleEname();
        if ($role== "gold") {
            $role = 'spreadMaster';
        }
        $controllerName = SpreadDetailFor. ucfirst($role);
        redirect(U($controllerName."/index"));
    }

}