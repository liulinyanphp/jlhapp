<?php
/*
 * 项目管理
*/
namespace Admin\Controller;
use Think\Controller;
//引入公用的结果处理类
use \Org\Util\Result;
//引入公用的异常抛出类
use \Org\Util\Exception;

class RedisController extends AdminBaseController {

    public function ListAction()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        echo "Connection to server sucessfully";
        //查看服务是否运行
        echo "Server is running: " . $redis->ping();
    }

}