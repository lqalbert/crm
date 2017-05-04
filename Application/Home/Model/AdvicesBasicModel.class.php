<?php
namespace Home\Model;
use Think\Model;

class AdvicesBasicModel extends Model {
    const DELETE_STATUS = -1;
    public function delete($ids){
        return $this->where(array('id'=>array('in', $ids )))->save(array('status'=>self::DELETE_STATUS));
    }


    

    public function decoratorView($roleEname){
      $funcName = $roleEname."GetView";
      if (method_exists($this, $roleEname."GetView")) {
        return call_user_func(array($this, $funcName));
      } else {
        return $this->getView();
      }  

    }

    private function getView(){
      return array(
            'oprate' => '',
            'button' => ''
        );
    }

    private function goldGetView(){
      $oprateColumn = "";
      $addButton = <<< 'BUTTON'
<el-tooltip content="选择一条建议并回复" placement="right">
    <el-button size="small" type="primary" @click="openDialog('reply')" icon="plus">建议回复
    </el-button>
</el-tooltip>
<el-tooltip content="选择多条建议" placement="right">
    <el-button size="small" type="danger" @click="deletes" icon="circle-close">删除
    </el-button>
</el-tooltip>
BUTTON;
      return array(
            'oprate' => $oprateColumn,
            'button' => $addButton
        );
    }
}