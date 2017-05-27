<?php
namespace Cli\Controller;

use Think\Controller;
use Cli\Service\GroupCountServiceModel;

/**
* 在 customerCount 完了之后再 运行统计
*/
class GroupCountController extends Controller{

    private $date = '';
    private $m = '';
    public function index($date){
      $this->date = $date;
      $this->m = new GroupCountServiceModel;
      return $this->m->index($this->date);
    }
}