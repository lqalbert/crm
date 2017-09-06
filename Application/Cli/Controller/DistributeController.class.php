<?php 
namespace Cli\Controller;
use Think\Controller;
use Cli\Logic\Distribute;

class DistributeController extends Controller {

    private $configM = null;
    private $customerM = null;
    private $al = null;


    public function index(){

        $this->configM = M("distribute_basic");
        $this->customerM = M("customers_basic");
        $this->al =  new Distribute();

        $re = $this->toDepartment();
        if ($re) {
            $this->toGroup();
            $this->toUser();
        }
    }

    private function toDepartment(){
        $row = $this->configM->where(array("obj"=>0))->find();

        if (!$row) {
            return false;
        }

        $config = json_decode($row['config'], true);
        $data = $this->customerM->where(array('depart_id'=>0, 'spread_id'=>array('NEQ',0), 'olde_mark'=>array('exp','is null')))->getField("id", true);
        
        $alg = $this->al;
        $alg->setConfig($config);
        $alg->setAllCustomer($data);// array('id', 'id');
        
        if (!$alg->isOk()) {
            return true;
        }
        

        //进入分配
        $record_id = M('distribute_record')->add(array(
            'type' => 0,
            'obj_id' => 0,
            'num' => $alg->getDistotal()
        ));

        $dataList = $alg->getDataList();
        foreach ($dataList as $key => $value) {
            // M("customers_basic")->where(array("id"=>$value['ids']))->
            if ($value['ids']) {
                $sql = "update customers_basic set depart_id=".$value['id'].",dis_time='".Date('Y-m-d H:i:s')."' where id in (".implode(",", $value['ids']).")";
                M()->execute($sql);
            }
            

            M('distribute_detail')->add(array(
                'record_id' => $record_id,
                'name'      => D("Home/Department")->where(array('id'=>$value['id']))->getField("name"),
                'value'     => count($value['ids'])
            ));
        }
        return true;   
    }

    private function toGroup(){
        $rows = $this->configM->where(array("type"=>1))->select();
        foreach ($rows as $key => $row) {
            $config = json_decode($row['config'], true);

            $data = $this->customerM->where(array('depart_id'=>$row['obj_id'], 'to_gid'=>0))->getField("id", true);
            $alg = $this->al;
            $alg->setConfig($config);
            $alg->setAllCustomer($data);// array('id', 'id');
            if ($alg->isOk()){
                //进入分配
                $record_id = M('distribute_record')->add(array(
                    'type' => 1,
                    'obj_id' => $row['obj_id'],
                    'num' => $alg->getDistotal()
                ));


                $dataList = $alg->getDataList();
                foreach ($dataList as $key => $value) {
                    // M("customers_basic")->where(array("id"=>$value['ids']))->
                    if ($value['ids']) {
                        $sql = "update customers_basic set to_gid=".$value['id'].",dis_time='".Date('Y-m-d H:i:s')."' where id in (".implode(",", $value['ids']).")";
                        M()->execute($sql);
                    }

                    M('distribute_detail')->add(array(
                        'record_id' => $record_id,
                        'name'      => D("Home/Group")->where(array('id'=>$value['id']))->getField("name"),
                        'value'     => count($value['ids'])
                    ));
                }
            } else {
                
            }
        } 
    }

    private function toUser(){
        $rows = $this->configM->where(array("type"=>2))->select();
        foreach ($rows as $key => $row) {
            $config = json_decode($row['config'], true);

            $data = $this->customerM->where(array('to_gid'=>$row['obj_id'], 'salesman_id'=>0))->getField("id", true);
            $alg = $this->al;
            $alg->setConfig($config);
            $alg->setAllCustomer($data);// array('id', 'id');
            if ($alg->isOk()){
                //进入分配

                $record_id = M('distribute_record')->add(array(
                    'type' => 2,
                    'obj_id' => $row['obj_id'],
                    'num' => $alg->getDistotal()
                ));

                $dataList = $alg->getDataList();
                foreach ($dataList as $key => $value) {
                    // M("customers_basic")->where(array("id"=>$value['ids']))->
                    if ($value['ids']) {
                        $sql = "update customers_basic set salesman_id=".$value['id'].",dis_time='".Date('Y-m-d H:i:s')."' where id in (".implode(",", $value['ids']).")";
                        M()->execute($sql);
                    }
                    

                    M('distribute_detail')->add(array(
                        'record_id' => $record_id,
                        'name'      => M("user_info")->where(array('user_id'=>$value['id']))->getField("realname"),
                        'value'     => count($value['ids'])
                    ));
                }
            }
        } 
    }

    
}