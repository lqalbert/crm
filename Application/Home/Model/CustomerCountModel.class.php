<?php
namespace Home\Model;

/**
* 父类 分类统计 的父类
*
*
*/ 

use Cli\Service\CustomerCountServiceModel;


abstract class CustomerCountModel extends \Think\Model{


    protected $date = '';



    /**
    *  `type` 统计结果
    */
    protected $typeCount = array();


    /**
    *  `V` 统计结果
    */
    protected $VCount = array();

    /**
    *  `冲突` 别人 统计结果
    */
    protected $conflictCount = array();

    /**
    *  `冲突` 被冲突 统计结果
    */
    protected $conflictedCount = array();


    /**
    * 索取的 统计结果
    */
    protected $pullCount = array();

    /**
    * 当日录入
    * 
    */
    protected $createCount = array();

    /**
    *  自锁总数
    */
    protected $ownCount = array();



    /**
    * @param string '2017-01-01'
    * 
    * @return null;
    */
    public function setDate($date){
        $timestamp = strtotime($date);
        
        $this->date['start'] = date("Y-m-d H:i:s", strtotime('-1 second', $timestamp));
        $this->date['end']   = date("Y-m-d H:i:s", strtotime('+1 day', $timestamp));
    }


    public function getFields(){
        $d = new CustomerCountServiceModel;
        $this->customerTypes = array_keys(D('Home/Customer')->getType());
        return $d->getFields();
    }


    /**
    * 存储层结构
    * 私有方法
    */
    private function getATypeCount($key){
        if (isset($this->typeCount[$key])) {
            return $this->typeCount[$key];
        } else {
            return 0;
        }
    }

    /**
    * 存储层结构
    * 私有方法
    */
    private function getACount($type, $key){
        if (isset($this->{$type}[$key])) {
            return $this->{$type}[$key];
        } else {
            return 0;
        }
    }



    /**
    * 存储层 结构
    */
    public function getA($id){
        return $this->getATypeCount('A'.$id);
    }

    /**
    * 存储层 结构
    */
    public function getB($id){
        return $this->getATypeCount('B'.$id);
    }

    /**
    * 存储层 结构
    */
    public function getC($id){
        return $this->getATypeCount('C'.$id);
    }

    /**
    * 存储层 结构
    */
    public function getD($id){
        return $this->getATypeCount('D'.$id);
    }

    /**
    * 存储层 结构
    */
    public function getF($id){
        return $this->getATypeCount('F'.$id);
    }

    /**
    * 存储层 结构
    */
    public function getN($id){
        return $this->getATypeCount('N'.$id);
    }

    /**
    * 存储层 结构
    */
    public function getV($id){
        return $this->getATypeCount('V'.$id);
        if (isset($this->VCount[$id])) {
            return $this->VCount[$id];
        } else {
            return 0;
        }
    }

    /**
    * 存储层 结构
    */
    public function getVX($id){
        return $this->getATypeCount('VX'.$id);
    }

    /**
    * 存储层 结构
    */
    public function getVT($id){
        return $this->getATypeCount('VT'.$id);
    }

    /**
    * 存储层 结构
    */
    public function getTodayV($id){
        if (isset($this->VCount[$id])) {
            return $this->VCount[$id];
        } else {
            return 0;
        }
    }

    

    /**
    * 存储层 结构
    */
    public function getConflictTo($id){
        return $this->getACount('conflictCount', $id);
    }

    /**
    * 存储层 结构
    */
    public function getConflictFrom($id){
        return $this->getACount('conflictedCount', $id);
    }

    /**
    * 存储层 结构
    */
    public function getPullsNum($id){
        return $this->getACount('pullCount', $id);
    }

    /**
    * 存储层 结构
    */
    public function getCreateNum($id){
        return $this->getACount('createCount', $id);
    }

    /**
    * 存储层 结构
    */
    public function getAllNum($id){
        $tmp = 0;
       
        foreach ($this->customerTypes as $value) {
            $tmp += (int)$this->getATypeCount($value.$id);
            
        }
        
        return $tmp;
    }

    /**
    * 存储层 
    */
    public function getOwnNum($id){
        if (isset($this->ownCount[$id])) {
            return $this->ownCount[$id];
        } else {
            return 0;
        }
    }
}