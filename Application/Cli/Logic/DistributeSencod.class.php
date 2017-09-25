<?php
namespace Cli\Logic;
/**
* 分配的算法
*
*/
class DistributeSencod {

    private $type = 0;
    private $disRate = array();
    private $limina = 0;

    private $allCus = array();
    private $totalNum  = 0 ;
    private $disTotal = 0;
    private $needTotal = 0;

    public function setConfig($config){
        
        $this->disRate = $config['list'];
        $this->type   = $config['type'];
    }



    public function setAllCustomer($dataList){
        $this->allCus = $dataList;
        $this->totalNum = count($this->allCus); //所有客户数量
    }

    private function setDisTotal(){
        $this->disTotal =  $this->totalNum > $this->needTotal ? $this->needTotal : $this->totalNum;
    }


    private function needDisTotal(){
        $total = 0;
        foreach ($this->disRate as $key => $value) {
            $total += $value['value'];
        }
        $this->needTotal = $total;
    }
    
    public function getDistotal(){
        return $this->disTotal;
    }


    public function isOk(){
        
        // if ($this->type ==1) { //禁用
        //     return false;
        // }

        if ($this->totalNum == 0) {
            return false;
        }

        $this->needDisTotal();
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
            // $tmp['num'] = intval($total * $value['value'] / 100);
            $tmp['num'] = $total > intval($value['value']) ? $value['value'] :  $total;
            $tmp['ids'] = array_slice($this->allCus, 0, $tmp['num']);
            $itemRate[] = $tmp;

            $this->allCus = array_diff($this->allCus, $tmp['ids']);
            $total -= $tmp['num'];
        }
        return $itemRate;
    } 
}