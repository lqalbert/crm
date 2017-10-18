<?php
namespace Queue\Controller;

use Think\Controller;

use Exception;
use Resque;

class QueueController  {

    protected $vendor;
    protected $args = [];
    protected $keys = [];
    protected $queues = '*';

    private $defaultValue = array(
            'queue'    => '*',
            'interval' => 1,
            'count'    => 1,
            'app'      => '',
            'pid'      => '',
            'debug'    => '',

        );


    public function __construct(){
        vendor('php-resque.autoload');
        $argv = json_decode(getenv('Q_ARGV'));
        foreach($argv as $item){
            if(strpos($item, '=')) {
                list($key, $val) = explode('=', $item);
            } else {
                $key = $val = $item;
            }
            $this->keys[] = $key;
            $this->args[$key] = $val;
        }

        $this->init();
    }

    /**
    * 执行队列
    * 环境变量参数值：
    * --queue|QUEUE: 需要执行的队列的名字
    * --interval|INTERVAL：在队列中循环的间隔时间，即完成一个任务后的等待时间，默认是1秒
    * --app|APP_INCLUDE：需要自动载入PHP文件路径，Worker需要知道你的Job的位置并载入Job
    * --count|COUNT：需要创建的Worker的数量。所有的Worker都具有相同的属性。默认是创建1个Worker
    * --debug|VVERBOSE：设置“1”启用更啰嗦模式，会输出详细的调试信息
    * --pid|PIDFILE：手动指定PID文件的位置，适用于单Worker运行方式
    */
    private function init(){
        $is_sington = false;

        //根据参数设置QUEUE环境变量
        $QUEUE = $this->getDefaultValue('queue'); 
        if(empty($QUEUE)) {
            die('Set QUEUE env var containing the list of queues to work.\n');
        }
        $this->queues = explode(',', $QUEUE);

        //根据参数设置INTERVAL环境变量
        $interval = $this->getDefaultValue('interval');  
        putenv("INTERVAL={$interval}");

        //根据参数设置COUNT环境变量
        $count = $this->getDefaultValue('count'); 
        putenv("COUNT={$count}");

        //根据参数设置APP_INCLUDE环境变量
        $app = $this->getDefaultValue('app');
        putenv("APP_INCLUDE={$app}");

        //根据参数设置PIDFILE环境变量
        $pid = $this->getDefaultValue('pid');
        putenv("PIDFILE={$pid}");

        //根据参数设置VVERBOSE环境变量
        $debug = $this->getDefaultValue('debug');
        putenv("VVERBOSE={$debug}");
    }

    private function getDefaultValue($key){
        $argName = '--'.$key;
        return in_array($argName, $this->keys) ? $this->args[$argName] : $this->defaultValue[$key];
    }

    public function index() {
        $act = getenv('Q_ACTION');
        switch($act) {
            case 'stop':
             $this->stop();
             break;
            case 'status':
             $this->status();
             break;
            default:
             $this->start();
        }
    }

    /**
    * 开始队列
    */
    public function start() {
        // 载入任务类
        $path = COMMON_PATH . "Job";
        $flag = \FilesystemIterator::KEY_AS_FILENAME;
        $glob = new \FilesystemIterator($path, $flag);
        foreach ($glob as $file) {
            if('php' === pathinfo($file, PATHINFO_EXTENSION))
                require realpath($file);
        }

        $logLevel = 0;
        $LOGGING = getenv('LOGGING');
        $VERBOSE = getenv('VERBOSE');
        $VVERBOSE = getenv('VVERBOSE');
        if (!empty($LOGGING) || !empty($VERBOSE)) {
            $logLevel = Resque\Worker::LOG_NORMAL;
        } else {
            if (!empty($VVERBOSE)) {
                $logLevel = Resque\Worker::LOG_VERBOSE;
            }
        }

        $APP_INCLUDE = getenv('APP_INCLUDE');
        if ($APP_INCLUDE) {
            if (!file_exists($APP_INCLUDE)) {
                die('APP_INCLUDE (' . $APP_INCLUDE . ") does not exist.\n");
            }
            require_once $APP_INCLUDE;
        }

        $interval = 5;
        $INTERVAL = getenv('INTERVAL');
        if (!empty($INTERVAL)) {
            $interval = $INTERVAL;
        }

        $count = 1;
        $COUNT = getenv('COUNT');
        if (!empty($COUNT) && $COUNT > 1) {
            $count = $COUNT;
        }

        if ($count > 1) {
            for ($i = 0; $i < $count; ++$i) {
                $pid = pcntl_fork();
                if ($pid == -1) {
                    die("Could not fork worker " . $i . "\n");
                } // Child, start the worker
                else {
                    if (!$pid) { //子进程里 $pid == 0
                        $worker = new Resque\Worker($this->queues);
                        $worker->logLevel = $logLevel;
                        fwrite(STDOUT, '*** Starting worker ' . $worker . "\n");
                        $worker->work($interval);
                        break;
                    }
                }
            }
        } // Start a single worker
        else {
            $worker = new Resque\Worker($this->queues);
            $worker->logLevel = $logLevel;

            $PIDFILE = getenv('PIDFILE');
            if ($PIDFILE) {
                file_put_contents($PIDFILE, getmypid()) or
                die('Could not write PID information to ' . $PIDFILE);
            }

            fwrite(STDOUT, '*** Starting worker ' . $worker . "\n");
            $worker->work($interval);
        }
    }

    /**
    * 停止队列
    */
    public function stop() {
        (new Resque\Worker($this->queues))->shutdown();
    }

    /**
    * 查看某个任务状态
    */
    public function status() {
        $id = in_array('--id', $this->keys) ? $this->args['--id'] : '';
        $status = new \Resque\Job\Status($id);

        if (!$status->isTracking()) {
            die("Resque is not tracking the status of this job.\n");
        }
        echo "Tracking status of " . $id . ". Press [break] to stop.\n\n";

        while (true) {
            fwrite(STDOUT, "Status of " . $id . " is: " . $status->get() . "\n");
            sleep(1);
        }
    }
}