<?php
namespace Home\Model;

class CounselArticleModel extends \Think\Model{

    protected $tableName = 'counsel_article';


    private $types = array(
        "行情分析",
        "股票推荐"
    );

    /**
    * 返回类型 或 所有的类型
    * 
    * @return string|array
    */
    public function getType($index){
        if (is_int($index)) {
            return $this->types[$index];
        } else {
            return $this->types;
        }
    }
    
}