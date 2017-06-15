<?php 
namespace Cli\Controller;


class QQController extends \Think\Controller {

    public function index(){
        // $noRecords = M('import_table4_no_record')->where('no_record=1')->select();

        $this->sourceM = M('import_table4_no_record', null, 'mysql://beta_testcrm:beta2008beta@139.224.40.238/beta_testcrm#utf8'); //
        $this->m = M();
        $groupId = $this->sourceM->field('id,qq')->select();
        foreach ($groupId as $group) {
            echo $group['id'], " ",$group['qq'];
            
            if (!empty($group['qq'])) {
                $row = $this->m->query("select id from customers_contacts where qq='".$group['qq']."'");
                var_dump(empty($row));
                if (!empty($row)) {
                    $this->sourceM->execute("update import_table4_no_record set is_qq=1 where id=".$group['id']);
                }
            }

            echo "\n";
        }
    }
}