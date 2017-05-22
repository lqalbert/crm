<?php
namespace Home\Model;
use Think\Model;

class CustomerBuyModel extends Model{

    protected $tableName = 'customers_buy';

    private $todo_list = array(
            'order' =>'<el-button v-show="searchForm.status==0" size="small" type="primary" @click="setOrders(scope.row)">开单</el-button>',
            'account'=>'<el-button v-show="searchForm.status==1" size="small" type="primary" @click="setDistrute(scope.row)">分配</el-button>',
            'distribute'=>'<el-button v-show="searchForm.status==1" size="small" type="primary" @click="setAccount(scope.row)">账号</el-button>'
        );


    protected  $_auto = array(
            array('todo_list', 'getToList', 1, 'callback')
     );

                                
                                

    /**
    * 
    * @parame data 
    *
    * @return array
    */

    public function decoratorButtons($data){
      foreach ($data as $key => $value) {
        $tmp = json_decode($value['todo_list'], true);
        $data[$key]['buttons'] = $tmp;
      }
      return $data;
    }

    public function getToList(){
        return json_encode(array_keys($this->todo_list));
    }


    


}