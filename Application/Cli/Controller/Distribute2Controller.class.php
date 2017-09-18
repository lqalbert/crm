<?php 
namespace Cli\Controller;
use Think\Controller;
use Cli\Logic\DistributeSencod;

class Distribute2Controller extends Controller {

    const ODEPART = 'disPlan';
    const OGROUP  = 'disGPlan';


    private $configM = null;
    private $customerM = null;
    private $al = null;


    public function index(){

        $this->configM = M("distribute_basic");
        $this->customerM = M("customers_basic");

        $this->al =  new DistributeSencod();

        $list = $this->getODisPlan();

        $this->toDepartment($list);

        $this->toGroup();
        $this->toUser();


        /*
        $this->customerM = M("customers_basic");
        

        $re = $this->toDepartment();
        if ($re) {
            
        }*/
    }



    private function getODisPlan(){
        $re = F(self::ODEPART);
        if ($re) {
            return $re;
        } else {
            $row = $this->configM->where(array("obj"=>0))->find();
            if (!$row) {
                exit();
            }
            $list = json_decode($row['config'], true);
            F(self::ODEPART, $list);
            return $list;
        }
    }

    private function saveODisPlan($list){
        $listConfig = $list['list'];
        $total = 0;
        foreach ($listConfig as $value) {
            $total += $value['value'];
        }
        if ($total ==0 ) {
            $list = null;
        }
        return F(self::ODEPART, $list);
    }

    private function getGroupList($id, $type, $config){
        $field = self::OGROUP."_".$id."_".$type;
        $re = F($field);
        if ($re) {
            return $re;
        } else {
            $list = $config;
            F($field, $list);
            return $list;
        }
    }

    private function saveGroupList($id, $type, $confg){
        $field = self::OGROUP."_".$id."_".$type;

        $listConfig = $confg['list'];
        $total = 0;
        foreach ($listConfig as $value) {
            $total += $value['value'];
        }
        if ($total ==0 ) {
            $confg = null;
        }

        return F($field, $confg);
    }


    private function toDepartment($config){
        
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
        
        $oldList = &$config['list'];
        $oldListMap = arr_to_map($oldList, "id", "value");
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

            $oldListMap[$value['id']] = $oldListMap[$value['id']] - $value['num'];
        }
        //重保存 下一次用
        foreach ($oldList as &$value) {
            $value['value'] = $oldListMap[$value['id']];
        }
        $this->saveODisPlan($config);
        return true;   
    }

    private function toGroup(){
        $rows = $this->configM->where(array("type"=>1))->select();
        foreach ($rows as $key => $row) {
            $config = json_decode($row['config'], true);
            $list = $this->getGroupList($row['obj_id'], $row['type'], $config);

            $data = $this->customerM->where(array('depart_id'=>$row['obj_id'], 'to_gid'=>0))->getField("id", true);
            $alg = $this->al;
            $alg->setConfig($list);
            $alg->setAllCustomer($data);// array('id', 'id');
            if ($alg->isOk()){
                //进入分配
                $record_id = M('distribute_record')->add(array(
                    'type' => 1,
                    'obj_id' => $row['obj_id'],
                    'num' => $alg->getDistotal()
                ));


                $dataList = $alg->getDataList();
                $oldList = &$list['list'];
                $oldListMap = arr_to_map($oldList, "id", "value");
                foreach ($dataList as $key => $value) {
                    // M("customers_basic")->where(array("id"=>$value['ids']))->
                    if ($value['ids']) {
                        $sql = "update customers_basic set to_gid=".$value['id'].",dis_time='".Date('Y-m-d H:i:s')."' where id in (".implode(",", $value['ids']).")";
                        M()->execute($sql);
                    }
                    $gname = D("Home/Group")->where(array('id'=>$value['id']))->getField("name");
                    if (!$gname) {
                        $gname = $value['id'];
                    }

                    M('distribute_detail')->add(array(
                        'record_id' => $record_id,
                        'name'      => $gname,
                        'value'     => count($value['ids'])
                    ));

                    $oldListMap[$value['id']] = $oldListMap[$value['id']] - $value['num'];
                }

                //重保存 下一次用
                foreach ($oldList as &$value) {
                    $value['value'] = $oldListMap[$value['id']];
                }
                $this->saveGroupList($row['obj_id'], $row['type'], $list);
            }
        } 
    }

    private function toUser(){
        $rows = $this->configM->where(array("type"=>2))->select();
        foreach ($rows as $key => $row) {
            $config = json_decode($row['config'], true);
            $list = $this->getGroupList($row['obj_id'], $row['type'], $config);

            $data = $this->customerM->where(array('to_gid'=>$row['obj_id'], 'salesman_id'=>0))->getField("id", true);
            $alg = $this->al;
            $alg->setConfig($list);
            $alg->setAllCustomer($data);// array('id', 'id');
            if ($alg->isOk()){
                //进入分配

                $record_id = M('distribute_record')->add(array(
                    'type' => 2,
                    'obj_id' => $row['obj_id'],
                    'num' => $alg->getDistotal()
                ));

                $dataList = $alg->getDataList();
                $oldList = &$list['list'];
                $oldListMap = arr_to_map($oldList, "id", "value");
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

                    $oldListMap[$value['id']] = $oldListMap[$value['id']] - $value['num'];
                }

                //重保存 下一次用
                foreach ($oldList as &$value) {
                    $value['value'] = $oldListMap[$value['id']];
                }
                $this->saveGroupList($row['obj_id'], $row['type'], $list);
            }
        } 
    }

    
}