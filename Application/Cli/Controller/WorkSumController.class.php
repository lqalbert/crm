<?php
namespace Cli\Controller;
use Home\Model\RoleModel;
use Home\Model\DepartmentModel;

class WorkSumController extends \Think\Controller {

    private $startDate = '';
    private $endDate = '';
    private $date ='';
    private $allData = array();
    private $insert_data =array();

    private $fieldMap = array(
        'phone_track',  
        'door_track', 
        'qq_track' ,   
        'email_track', 
        'weixin_track' ,
        'v_summary' ,  
        'teach_track' ,
        'captain_track' ,
        'sell_track' , 
        'risk_track',  
        'manager_track', //经理建议
        'retroaction_track' ,
        'other_track'    
    );

    /* `sum_track`   
        `self_track`  */


    public function index($date='2017-05-16'){
        $this->types = D('Home/CustomerLog')->getType();

        $this->date = $date;
        $this->startDate = $this->date. " 00:00:00";
        $this->endDate   = Date('Y-m-d H:i:s', strtotime($date) + 86400);
        $this->setDateRe();
        $this->setAllUser();
    }

    private function setDateRe(){
        $sql="SELECT count(id) as c, user_id, track_type FROM  `customers_log` where created_at >= '".$this->startDate."' and created_at <'".$this->endDate."' and track_type is not null  group by user_id,track_type ";
        $allData = M()->query($sql);
        
        $allDataGroup = arr_group($allData, 'user_id');
        $re = array();
        foreach ($allDataGroup as $key => $value) {
            $tmp = arr_to_map($value, 'track_type', 'c');
            $re[$key] = $tmp;
        }
        $this->allData = $re;
    }

    private function getSum($user_id){
        if (isset($this->allData[$user_id])) {
            $col = array_values($this->allData[$user_id]);
            return array_sum($col);
        } else {
            return 0;
        }
    }

    private function getSelf($user_id){
        $sql = "select id from customers_basic where user_id=salesman_id and user_id=".$user_id;
        $sql = "select count(id) as c from customers_log where cus_id in (". $sql .") and created_at >= '".$this->startDate."' and created_at <'".$this->endDate."'";
        $re = M()->query($sql);
        if ($re) {
            return $re[0]['c'];
        } else {
            return 0;
        }

    }



    private function setAllUser(){
        /*$sql="select ui.user_id,realname,gb.name as group_name, gb.id as group_id ,db.name as department_name, db.id as department_id from user_info as ui ".
             " left join group_basic as gb on ui.group_id=gb.id left join department_basic as db on ui.department_id=db.id ";*/
        $roleM = new RoleModel();
        $alluser= M()->query("select ui.user_id,realname,gb.name as group_name, gb.id as group_id ,db.name as department_name, db.id as department_id from rbac_user inner join user_info as ui on rbac_user.id = ui.user_id  left join group_basic as gb on ui.group_id=gb.id left join department_basic as db on ui.department_id=db.id where rbac_user.status>=0 and ui.group_id<>0 and ui.department_id<>0 and role_id in (".
            $roleM->getIdByEname(RoleModel::CAPTAIN).",". 
            $roleM->getIdByEname(RoleModel::STAFF) .",".
            $roleM->getIdByEname(RoleModel::DEPARTMENTMASTER).") ");   
        $this->insert_data = array();
        foreach ($alluser as $value) {
            $tmp_row = array(
                'user_id'=>$value['user_id'], 
                'group_id'=>$value['group_id'],
                'group_name'=>$value['group_name'],
                'department_id'=>$value['department_id'],
                'department_name' =>$value['department_name'],
                'date'=>$this->date);

            if (isset($this->allData[$value['user_id']])) {
                foreach ($this->allData[$value['user_id']] as $k=>$c) {
                    $tmp_row[$this->fieldMap[$k]] = $c;
                } 

                $tmp_row['sum_track'] = $this->getSum($value['user_id']);
                $tmp_row['self_track'] = $this->getSelf($value['user_id']);
            } 

            
            // $content = array();

            /*foreach ($this->types as $k=>$v2) {
                var_dump($v2);
            }*/
            // $tmp_row['content'] = json_encode($content);

            $this->insert_data[] = $tmp_row;

            $this->save();
        }
        if (count($this->insert_data)>0) {
            $this->lastSave();
        } 
    }

    private function save(){
        if (count($this->insert_data) > 500 ) {
            $this->lastSave();
        }
    }

    private function lastSave(){
        $re = M('statistics_quantization2')->addAll($this->insert_data);
        echo $re;
        echo "\n";
        $this->insert_data  =array();
    }


}

/*  
    `phone_track` mediumint not null default '0' comment '电话跟踪', 0 
    `door_track`  mediumint not null default '0' comment '上门服务',1
    `qq_track`    mediumint not null default '0' comment 'QQ跟踪', 2
    `email_track` mediumint not null default '0' comment 'email跟踪', 3
    `weixin_track` mediumint not null default '0' comment '微信跟踪', 4
    `v_summary`   mediumint not null default '0' comment '成交总结', 5
    `teach_track` mediumint not null default '0' comment '讲师指导', 6
    `captain_track` mediumint not null default '0' comment '主管建议', 7
    `sell_track`  mediumint not null default '0' comment '售前回访', 8
    `risk_track`  mediumint not null default '0' comment '风控建议', 9
    `retroaction_track` mediumint not null default '0' comment '反馈投诉', 10
    `other_track` mediumint not null default '0' comment '其它方式', 11
    `sum_track`   mediumint not null default '0' comment '总数', 12
    `self_track`  mediumint not null default '0' comment '锁定自跟', 13 
*/