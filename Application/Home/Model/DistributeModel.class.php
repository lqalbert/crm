<?php
namespace Home\Model;

use Think\Model;

class DistributeModel extends Model{

    protected $tableName = 'distribute_basic';

    public static $type  = array(
        0=>"总经办",
        1=>"部门",
        3=>"小组"
    );
}