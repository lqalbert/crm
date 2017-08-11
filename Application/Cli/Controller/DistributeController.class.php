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
        if ($row) {
            $config = json_decode($row['config'], true);

            $data = $this->customerM->where(array('depart_id'=>0))->getField("id", true);
            
            $alg = $this->al;
            $alg->setConfig($config);
            $alg->setAllCustomer($data);// array('id', 'id');
            if ($alg->isOk()){
                //进入分配
                $dataList = $alg->getDataList();
                foreach ($dataList as $key => $value) {
                    // M("customers_basic")->where(array("id"=>$value['ids']))->
                    $sql = "update customers_basic set depart_id=".$value['id']." where id in (".implode(",", $value['ids']).")";
                    M()->execute($sql);
                }
            } else {
                //不分配
                return false;
            }
            return true;
        } else {
            return false;
        }
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
                $dataList = $alg->getDataList();
                foreach ($dataList as $key => $value) {
                    // M("customers_basic")->where(array("id"=>$value['ids']))->
                    $sql = "update customers_basic set to_gid=".$value['id']." where id in (".implode(",", $value['ids']).")";
                    M()->execute($sql);
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
                $dataList = $alg->getDataList();
                foreach ($dataList as $key => $value) {
                    // M("customers_basic")->where(array("id"=>$value['ids']))->
                    $sql = "update customers_basic set salesman_id=".$value['id']." where id in (".implode(",", $value['ids']).")";
                    M()->execute($sql);
                }
            }
        } 
    }

    
}