<?php
namespace Cli\Service;

use Home\Model\RoleModel;

/**
* 纪录应该是死的 
* 不能因为 人员变动 纪录也变
* 不能因为 小组变动 纪录也变
* 不能因为部门 变动 纪录也变
* 所以 生成这条纪录时 对应的 小组名称  部门等 就应该写死在里面
* 添加 小组id 部门id 可以以后核对用
*/
class CustomerCountServiceModel extends \Think\Model{

    protected $autoCheckFields = false;

    private $store = true;

    /**
    * 时间
    */
    private $date = array('start'=>'', 'end'=>'');


    /**
    *  `type` 统计结果
    */
    private $typeCount = array();


    /**
    *  `V` 统计结果
    */
    private $VCount = array();

    /**
    *  `冲突` 别人 统计结果
    */
    private $conflictCount = array();

    /**
    *  `冲突` 被冲突 统计结果
    */
    private $conflictedCount = array();


    /**
    * 索取的 统计结果
    */
    private $pullCount = array();

    /**
    * 当日录入
    * 
    */
    private $createCount = array();

    /**
    *  自锁总数
    */
    private $ownCount = array();


    /**
    * 存储层
    * 小组
    */
    private $groups = array();

    /**
    * 存储层
    * 小组
    */
    private $departments = array();


    public function getFields(){
        $this->customerTypes = array_keys(D('Home/Customer')->getType());
        return array_merge( $this->customerTypes, array('today_v', 'conflict_to', 'conflict_from', 'pulls_num', 'create_num', 'all_num', 
            'own_num'));
    }

    /**
    * set Store 
    * @param boolean true;
    *
    * @return 
    */
    public function setStore($isStore = true){
        $this->store = $isStore;
    }

    private function setGroups(){
        $this->groups = M('group_basic')->where(array('status'=>array('EGT', 0)))->getField('id,name');
    }

    private function setDepartments(){
        $this->departments = M('department_basic')->where(array('status'=>array('EGT', 0)))->getField('id,name');
    }


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


    /**
    * 生成 类型 统计结果
    */
    private function setTypeCount(){
        $sql = "select count(id) as c , `type` , salesman_id from customers_basic  group by `type`, salesman_id  ";

        $re = M()->query($sql);

        foreach ($re as  $value) {
            $this->typeCount[$value['type'].$value['salesman_id']] = $value['c'];
        }



        // 结果 存储：
        //   类型user_id  总数
        // [
        //     'A12'=> 12,
        // ]
    }

    private function setConflictCount(){
        $sql = "select count(id) as c, user_id from customers_conflict where created > '".$this->date['start']."' and created <'".$this->date['end']."'  group by user_id";

        $re = M()->query($sql);

        foreach ($re as  $value) {
            $this->conflictCount[$value['user_id']] = $value['c'];
        }

        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }

    private function setConflictedCount(){
        $sql = "select count(customers_conflict.id) as c , customers_basic.user_id from customers_basic inner join customers_conflict on customers_basic.id = customers_conflict.cus_id where customers_conflict.created > '".$this->date['start']."' and customers_conflict.created <'".$this->date['end']."'  group by customers_basic.user_id";
        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->conflictedCount[$value['user_id']] = $value['c'];
        }

        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }

    private function setPullCount(){
        $sql = "select count(cus_id)as c, to_id from customers_pulls where created_at > '".$this->date['start']."' and created_at <'".$this->date['end']."' group by to_id";
        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->pullCount[$value['to_id']] = $value['c'];
        }

        // 结果 存储：
        //   to_id  总数
        // [
        //     '12'=> 12,
        // ]
    }

    /**
    * 统计 指定时间 的成交量
    */
    private function setVCount(){
        $sql = "select count(cus_id), user_id from customers_service where time > '".$this->date['start']."' and time <'".$this->date['end']."' group by user_id";

        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->VCount[$value['user_id']] = $value['c'];
        }
        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }


    /**
    * 当日 添加的客户
    */
    private function setCreateCount(){
        $sql = "select count(id) as c  , user_id from customers_basic where created_at > '".$this->date['start']."' and created_at <'".$this->date['end']."' group by user_id  ";

        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->createCount[$value['user_id']] = $value['c'];
        }

        // 结果 存储：
        //   user_id  总数
        // [
        //     '12'=> 12,
        // ]
    }

    /**
    * 自锁客户 总数
    */
    private function setOwnCount(){
        $sql = "select count(id) as c  , user_id from customers_basic  group by user_id  ";
        $re = M()->query($sql);
        foreach ($re as  $value) {
            $this->ownCount[$value['user_id']] = $value['c'];
        }
    }






    public function index($date){
        
        //业务逻辑层
        $this->setDate($date);

        // repository 层 存储层
        $this->setTypeCount();
        $this->setConflictCount();
        $this->setConflictedCount();
        $this->setPullCount();
        $this->setVCount();
        $this->setCreateCount();
        $this->setOwnCount();
        $this->setGroups();
        $this->setDepartments();

        //业务逻辑层
        $this->setUserRecord($date);

        //存储数据
        /*[
            'user_id' ,'A', 'B'... 'conflict_to', 'conflict_from', 'pulls_num', 'create_num', 'all_num(总数，所有的类型加起来)', 'date(Y-m-d)'

        ]*/
    }


    private function setUserRecord($date){

        $roleM = new RoleModel();

        $alluser= M()->query("select id,group_id,department_id from rbac_user inner join user_info on rbac_user.id = user_info.user_id where rbac_user.status>=0 and group_id<>0 and department_id<>0 and role_id in(".$roleM->getIdByEname(RoleModel::CAPTAIN).",". $roleM->getIdByEname(RoleModel::STAFF) .")");

        /*$this->customerTypes = array_keys(D('Home/Customer')->getType());
        $fields = array_merge( $this->customerTypes, array('today_v', 'conflict_to', 'conflict_from', 'pulls_num', 'create_num', 'all_num'));*/
        $fields = $this->getFields();
        $re = array();
        foreach ($alluser as $value) {
            $tmp_row = array(
                'user_id'=>$value['id'], 
                'group_id'=>$value['group_id'],
                'group_name'=>$this->groups[$value['group_id']],
                'department_id'=>$value['department_id'],
                'department_name' =>$this->departments[$value['department_id']],
                'date'=>$date);

            $content = array();
            foreach ($fields as $v2) {
                $tmp_row[$v2] = call_user_func(array($this, 'get'.parse_name($v2, 1)), $value['id']);
            }
            // $tmp_row['content'] = json_encode($content);

            $re[] = $tmp_row;
        }

        
        if ($this->store) {
            return M('statistics_usercustomers')->addAll($re);
        } else {
            return $re;
        }
        
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

