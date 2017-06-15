<?php
namespace Cli\Controller;


class SetGroupUserController extends \Think\Controller{


    public function index(){
        // $noRecords = M('import_table4_no_record')->where('no_record=1')->select();
        $sql = "select distinct group_id from import_table4_no_record where no_record=1";
        $this->sourceM = M('import_table4_no_record', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8'); //

        $groupId = $this->sourceM->query($sql);
        foreach ($groupId as $group) {
            $group_id = $group['group_id'];
            $user_id = M('group_basic')->where('id='.$group_id)->getField('user_id');
            if (!empty($user_id)) {
                $updateSql = "update import_table4_no_record set group_user=$user_id where group_id=$group_id";
                
            } else {
                $updateSql = "update import_table4_no_record set group_user=0 where group_id=$group_id";
            }
            $this->sourceM->execute($updateSql);
        }


        
    }
}