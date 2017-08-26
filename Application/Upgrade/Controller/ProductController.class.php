<?php
namespace Upgrade\Controller;

use Think\Controller;

class ProductController extends Controller{

    public function index(){

    }

    public function alterTable(){
         $sql = " alter table `products` add column `upgrade` tinyint unsigned not null default '0' comment '升级时间段单位月'; ";

         M()->execute($sql);
    }
}