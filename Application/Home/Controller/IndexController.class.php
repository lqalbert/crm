<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
    	$this->display();
    }

    function main(){
    	$this->display();
    }
}