<?php
namespace Upgrade\Controller;

use Think\Controller;

class VCustomerController extends Controller{

    public function index(){

    }

    public function alterTable(){
         $sql = " alter table `customers_buy` add column `dead_time` char(10)   null  comment '到期时间'; ";
         M()->execute($sql);
    }
}