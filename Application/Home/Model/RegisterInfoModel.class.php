<?php
namespace Home\Model;
use Think\Model;

class RegisterInfoModel extends Model{
  
  protected $connection = array(
    'db_type'  => 'mysql',
    'db_user'  => 'beta_spread',
    'db_pwd'   => 'beta2008beta',
    'db_host'  => '139.224.40.238',
    'db_port'  => '3306',
    'db_name'  => 'beta_spread',
    'db_charset' =>'utf8',
    'db_params' =>  array(), // 非必须
  );

  protected $tableName = 'register';












}