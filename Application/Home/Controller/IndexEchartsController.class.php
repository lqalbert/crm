<?php
namespace Home\Controller;

class IndexEchartsController extends \Think\Controller{

    public function index(){

        $this->ajaxReturn(preg_replace('/\s/','',$this->gold()));
    }


    public function gold(){
        $dd=<<< EOF
{
                 title: {
                    text: '最近7天'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { // 坐标轴指示器，坐标轴触发有效
                        type: 'shadow' // 默认为直线，可选为：'line' | 'shadow'
                    }
                },
                legend: {
                    data: ['自锁数', '成交数'],
                    align: 'right',
                    right: 10
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: [{
                    type: 'category',
                    data: ['2017-01-01', '2017-01-02', '2017-01-03', '2017-01-04', '2017-01-05']
                }],
                yAxis: [{
                    type: 'value',
                    name: '数量',
                    axisLabel: {
                        formatter: '{value}'
                    }
                }],
                series: [{
                    name: '自锁数',
                    type: 'bar',
                    data: [20, 12, 31, 34, 31]
                }, {
                    name: '成交数',
                    type: 'bar',
                    data: [10, 20, 5, 9, 3]
                }]
            }
EOF;
        return $dd;
    }
}