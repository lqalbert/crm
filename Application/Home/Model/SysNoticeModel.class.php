<?php
namespace Home\Model;
use Think\Model;
class SysNoticeModel extends Model{

    const DELETE_STATUS = -1;

	  protected $tableName = 'sys_notice';
   
    protected $NoticeType = array(
     '1'=>'功能升级',
     '2'=>'新功能上线',
     '3'=>'功能测试',
     '4'=>'系统更新',
     '5'=>'系统BUG',
     '6'=>'系统维护',
     '7'=>'其它公告',
    );

    protected $_auto = array(
        array('start', 'transfer', 1, 'callback'),
        array('end', 'transfer', 1, 'callback'),
        array('user_id', 'getUserId', 1, 'callback'),
    );
    //UTC时间转换
    public function transfer($v){
      if (empty($v)) {
        return null;
      } else {
        return UTC_to_locale_time($v);
      }
    }

    //获取user_id
    public function getUserId(){
    	if(session('uid')){
    		$user_id=session('uid');
    		return $user_id;
    	}
    }

    /**
    * 返回类型 或 所有的类型
    * 
    * @return string|array
    */
    public function getType($index=NULL){
    	if (!empty($index)) {
    		return $this->NoticeType[$index];
    	} else {
    		return $this->NoticeType;
    	}
    }

    public function delete($ids){
        // return $this->where(array('id'=>array('in', $ids )))->save(array('status'=>-1));
        $id_arr = explode(",", $ids);
        return $this->data(array('status'=>self::DELETE_STATUS))->where(array('id'=> array('in', $id_arr)))->save();
    }


    /**
    * 跟据不同的权限  index.html 显示不同的内容
    * 列表的操作列
    * 添加按钮
    * @parame string 
    *
    * @return array
    */

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
      $oprateColumn = <<<'EOD'
<el-table-column label="操作"  align="center" width="180">
   <template scope="scope">
      <el-button type="info" @click.stop="handleEdit(scope.$index, scope.row)"     size="small">编辑</el-button>
      <el-button type="danger"  @click.stop="handleDelete(scope.$index, scope.row)"   size="small" >删除</el-button>
   </template>
</el-table-column>  
EOD;
      $addButton = <<< 'BUTTON'
<el-tooltip content="点击填写公告并发布" placement="right">
 <el-button size="small" type="info" @click="openDialog('add')" icon="edit">发布公告
 </el-button>
</el-tooltip>
BUTTON;
      return array(
            'oprate' => $oprateColumn,
            'button' => $addButton
        );
    }













}