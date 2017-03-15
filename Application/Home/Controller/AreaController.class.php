<?php
namespace Home\Controller;

/**
* 读取地区
*/
class AreaController extends CommonController {
	protected $table = "area_info";

	public function getAreasByPid(){
		$pid = I('get.pid',1);
		$list = $this->M->where(array("p_id"=>$pid))->select();
		$this->ajaxReturn($list);
        // echo json_decode($list);
	}
}