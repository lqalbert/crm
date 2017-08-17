<?php
namespace Cli\Logic;
/**
* 分配的算法
*
*/
class Distribute {

    private $type = 0;
    private $disRate = array();
    private $limina = 0;

    private $allCus = array();
    private $totalNum  = 0 ;
    private $disTotal = 0;

    
    private function setDisTotal(){
        $this->disTotal = $this->totalNum - ( $this->totalNum % $this->limina );
    }

    public function getDistotal(){
        return $this->disTotal;
    }

    public function setConfig($config){
        $this->limina = $config['limina'];
        $this->disRate = $config['list'];
        $this->type   = $config['type'];
    }

    public function setAllCustomer($dataList){
        $this->allCus = $dataList;
        $this->totalNum = count($this->allCus);
    }

    public function isOk(){
        if ($this->type ==1) { //手动
            return false;
        }

        if ($this->totalNum < $this->limina) { //不够数量
            return false;
        }

        $this->setDisTotal();

        return true;
    }



    public function getDataList(){
        //待分配的数量
        $total = $this->disTotal;
        $itemRate = array();
        foreach ($this->disRate as $key => $value) {
            $tmp = array();
            $tmp['id'] = $value['id'];
            $tmp['num'] = intval($total * $value['value'] / 100);
            $tmp['ids'] = array_slice($this->allCus, 0, $tmp['num']);
            $itemRate[] = $tmp;

            $this->allCus = array_diff($this->allCus, $tmp['ids']);
        }
        return $itemRate;
    }   
}