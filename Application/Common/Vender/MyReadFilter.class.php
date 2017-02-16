<?php

class MyReadFilter implements \PHPExcel_Reader_IReadFilter
{
    public function readCell($column, $row, $worksheetName = '') {
        // Read title row and rows 20 - 30
        if ($row == 1 || ($row >= 3 && $row <= 5)) {
            // var_dump($column);

            var_dump($row);
            var_dump($worksheetName);
            ob_flush();
            die();
            return true;
        }
        
        return false;
    }
}