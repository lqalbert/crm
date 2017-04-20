<?php
namespace Cli\Controller;

use Think\Controller;
use Cli\Service\GroupCountModel;

/**
* 在 customerCount 完了之后再 运行统计
*/


class GroupCountController extends Controller{

    private $date = '';
    private $m = '';
    public function index($date){
        $this->date = $date;
        $this->m = new GroupCountModel;
        var_dump($this->m->index($this->date));
    }
}