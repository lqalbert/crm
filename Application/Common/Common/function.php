<?php 

/**
 * 二维数级转成 tree
 * array('id'=>array("id"=>,"parentid"=>...),'id'=>array("id"=>,"parentid"=>...)...);
 * id 不能为 0  
 * 顶级parentid=0 必须有的 
 * @param arr  二维数组
 * @param parent_key  指定的父级id
 * @param value_key   要保留的 值的对应键   | null 为所有的 | 多个值以 ","分隔
 * @return array
 */
function arr_to_tree($arr, $parnet_key, $value_key=null ,$sonname="sons"){
    foreach ($arr as $key => $value) {
        $arr[$value[$parnet_key]][$sonname][$key] = &$arr[$key];
    }
    
    return $arr[0][$sonname];
}

 /**
 * 转换二维数组
 * @param arr  二维数组
 * @param thekey  key
 * @param value_key  要保留的 值的对应键   | null 为所有的 | 多个值以 ","分隔不要有whitespace
 * @return void
 *
 * array( 0=>
 *  array("id"=>1,"value"=>"xxx"),
 *  1=>
 *  array("id"=>2,"value"=>"xxx"),
 *  2=>
 *  array("id"=>3,"value"=>"xxx"),
 *  3=>
 *  array("id"=>4,"value"=>"xxx"),
 *  );
 *  to=========>
 *  array("id"=>array(...),"id"=>array(...)... );
 *
 */
function arr_to_map($arr, $thekey, $value_key=null){
    $return = array();
    if (is_null($value_key)) {
        foreach ($arr as $key => $value) {
            $return[$value[$thekey]] = $value;
        }
    }else{
        foreach ($arr as $key => $value) {
            if (strpos($value_key,",")) {
                $tmp_key = explode(",", $value_key);
                $tmp     = array();
                foreach ($value as $key2 => $value2){
                    if(in_array($key2, $tmp_key)){
                        $tmp[$key2] = $value2;
                    }
                }
            }else{
                $tmp = $value[$value_key];
            }
             
            $return[$value[$thekey]] = $tmp;
        }
    }
    return $return;
}

/**
 * 转换二维数组 to group
 * @param arr  二维数组
 * @param thekey  key
 * @return array
 *
 * array( 0=>
 *  array("groupid"=>1,"value"=>"xxx"),
 *  1=>
 *  array("groupid"=>1,"value"=>"xxx"),
 *  2=>
 *  array("groupid"=>2,"value"=>"xxx"),
 *  3=>
 *  array("groupid"=>2,"value"=>"xxx"),
 *  );
 *  to=========>
 *  array("groupid"=>array(array("groupid"=>1,"value"=>"xxx"),array("groupid"=>1,"value"=>"xxx") ),"groupid"=>array(...)... );
 *
 */
function arr_group($arr, $thekey){
	$result = array();
	foreach ($arr as $key => $value) {
		if (!isset($result[$value[$thekey]])) {
			$result[$value[$thekey]] = [];
		}
		$result[$value[$thekey]][] = $value;
	}

	return $result;
}


/**
 * 格式化输出数组
 * @var arr 目标数组
 * @return 竖向排列数组
 * @example:
 */
function va_dump($arr){
    echo '<pre style="font-size: 2em;color:red;">';
    var_dump($arr);
    echo '</pre>';
}


/**
 * 将UTC时间转换为当地时间
 * @param  string "2017-02-07T05:40:24.558Z"
 * @return string "2017-02-07 15:40:24"
 *
 */

function UTC_to_locale_time($v){
    return Date('Y-m-d H:i:s', strtotime($v));
}

/**
* 读取 excel 文件 并返回 array 形式的数据
* 只支持第一个 Sheet;
* 如果文件过大，可能会卡死 到时可以参考以下链接对代码进行重构
* http://www.01happy.com/phpexcel-read-big-excel-file/
* 
* @param string $filpath
*
* @return array
*/
function getExcelArrayData($filename){
    import('Common.Vender.PhpExcel.PHPExcel',APP_PATH,'.php');
    // import('Common.Vender.MyReadFilter');
   
    
    //检测文件类型
    $inputFileType = \PHPExcel_IOFactory::identify($filename);
    //跟据文件类型创建读取器
    $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
    //设置过滤器
    // $objReader->setReadFilter( new \MyReadFilter() );

    // $obj = \PHPExcel_IOFactory::load($filename);
    // var_dump($obj);
    $obj = $objReader->load($filename);
    $worksheet = $obj->getSheet(0);
    // var_dump($worksheet);
    
    /*$row_iterator = $worksheet->getRowIterator();
    var_dump(iterator_count($row_iterator));*/
    //以下代码参考 文档 http://www.01happy.com/phpexcel-read-big-excel-file/
    $startRow = 1;
    $endRow = $worksheet->getHighestRow();// 总行数

    // $endRow = 5;
    $highestColumn = $worksheet->getHighestColumn();// 最后列数所对应的字母
    // var_dump($highestColumn); 
    $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);// 总列数
    
    $data = array();
    for ($row = $startRow; $row <= $endRow; $row++) {
        for ($col = 0; $col < $highestColumnIndex; $col++) {
            $data[$row][chr(ord('A')+$col)] = (string) $worksheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
            // $data[$row][chr(ord('A')+$col)] = (string) $worksheet->getCellByColumnAndRow($col, $row)->getValue();
            // 部分有格式的列 getValue会返回原始值 例 如 日期 2016/01/01 原始值 类似于42685.466099537
        }
    }
   
    return $data;
}

