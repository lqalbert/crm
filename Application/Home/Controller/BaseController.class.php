<?php
namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller{
			function _initialize(){
				  if(!isset($_SESSION['admin'])){
						 	$this->redirect('Login/login');
						 	die();
					 }
			}
}





























?>