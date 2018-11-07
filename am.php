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

// 应用入口文件
ini_set("data.timezone","Asia/Shanghai");
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
!defined('APP_DEBUG') AND define('APP_DEBUG',false);

//设置状态配置
!defined('APP_STATUS') AND define('APP_STATUS','dev');

// 定义应用根目录
define("ROOT", __DIR__);

// 定义运行时目录
define('RUNTIME_PATH','./Runtime/Admin/');

//改变所有模块的模板文件目录
define('TMPL_PATH','./Template/');

// 定义应用目录
define('APP_PATH',realpath('./Apps').'/');


//关闭目录安全文件的生成
define('BUILD_DIR_SECURE', false);

// 引入ThinkPHP入口文件
require './Corlib/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单