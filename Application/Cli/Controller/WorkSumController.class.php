<?php
namespace Cli\Controller;
use Home\Model\RoleModel;
use Home\Model\DepartmentModel;

class WorkSumController extends \Think\Controller {

    private $startDate = '';
    private $endDate = '';
    private $date ='';
    private $allData = array();

    public function index($date='2017-05-16'){
        $this->types = D('Home/CustomerLog')->getType();

        $this->date = $date;
        $this->startDate = $this->date. " 00:00:00";
        $this->endDate   = Date('Y-m-d', strtotime($date) + 86400);
        $this->setDateRe();
        $this->setAllUser();
    }

    private function setDateRe(){
        $sql="SELECT count(id) as c, user_id, track_type FROM `beta_testcrm`.`customers_log` where created_at >= '".$this->startDate."' and created_at <'".$this->endDate."'  group by user_id,track_type ";
        $this->allData = M()->query($sql);

    }

    private function setAllUser(){
        /*$sql="select ui.user_id,realname,gb.name as group_name, gb.id as group_id ,db.name as department_name, db.id as department_id from user_info as ui ".
             " left join group_basic as gb on ui.group_id=gb.id left join department_basic as db on ui.department_id=db.id ";*/
        $roleM = new RoleModel();
        $alluser= M()->query("select ui.user_id,realname,gb.name as group_name, gb.id as group_id ,db.name as department_name, db.id as department_id from rbac_user inner join user_info as ui on rbac_user.id = ui.user_id  left join group_basic as gb on ui.group_id=gb.id left join department_basic as db on ui.department_id=db.id where rbac_user.status>=0 and ui.group_id<>0 and ui.department_id<>0 and role_id in (".
            $roleM->getIdByEname(RoleModel::CAPTAIN).",". 
            $roleM->getIdByEname(RoleModel::STAFF) .",".
            $roleM->getIdByEname(RoleModel::DEPARTMENTMASTER).")");

        foreach ($alluser as $value) {
            $tmp_row = array(
                'user_id'=>$value['id'], 
                'group_id'=>$value['group_id'],
                'group_name'=>$this->groups[$value['group_id']],
                'department_id'=>$value['department_id'],
                'department_name' =>$value['department_name'],
                'date'=>$this->date);

            // $content = array();

            foreach ($this->types as $v2) {
                // $tmp_row[$v2] = call_user_func(array($this, 'get'.parse_name($v2, 1)), $value['id']);
            }
            // $tmp_row['content'] = json_encode($content);

            $re[] = $tmp_row;
        }
    }


}