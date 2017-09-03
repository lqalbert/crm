<?php
namespace Cli\Controller;

use Think\Controller;

class MergeAlterTableController  extends Controller{



    public function index(){
        $sql = " alter table customers_basic add column `olde_mark` char(10) null; ";
        $sql .= " alter table group_basic add column `old_userid` int unsigned null;";
        $sql .= " alter table user_info add column `old_id` int unsigned null;";

        M()->execute($sql);
    }
}