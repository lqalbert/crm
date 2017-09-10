<?php
namespace Upgrade\Controller;

use Think\Controller;

class SpreadFixTableController extends Controller{

    public function index(){

    }

    public function alterTable(){
         $sql = " alter table `customers_basic` add column `recommend` tinyint unsigned not null default '0' comment '推荐'; ";

         M()->execute($sql);
    }
}