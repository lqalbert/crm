<?php
namespace Common\Behaviors;

use Think\Behavior;

class QueueBehavior extends Behavior{


    public function run(&$params){
        $config = C('QUEUE');
        if($config){
            vendor('php-resque.autoload');

            \Resque::setBackend(array('redis'=>$config), 1);

            if(isset($config['prefix']) && !empty($config['prefix']))
            \Resque\Redis::prefix($config['prefix']);
        }
    }
}