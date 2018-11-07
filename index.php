<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
//$redis = new redis();
//$redis->connect('127.0.0.1', 6379);
//$result = $redis->get('pro_investment_ins');
//$result = json_decode($result,true);
//$ttm = $result['INVESTMENT_57B783DF1540893715'];
//
//print_r($ttm);
//echo ($result);
//die();

// 应用入口文件
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
!defined('APP_DEBUG') AND define('APP_DEBUG',true);

//设置状态配置
!defined('APP_STATUS') AND define('APP_STATUS','dev');


/**后续有单独的域名自己走起*/
if(in_array($_SERVER['SERVER_NAME'],['www.jlhapp.com']))
{
    define('CLIENT_TYPE', 'am');
}else{
	define('CLIENT_TYPE', 'pc');
}
require_once ("./" . strtolower(CLIENT_TYPE) . ".php");




