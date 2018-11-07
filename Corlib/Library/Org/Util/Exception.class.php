<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Org\Util;
/**
 * 异常抛出工具类
 * add:lly
 */
class Exception {

    /**
     * 带异常代码的异常抛出
     * $errorInfo = array('错误码20','错误信息')
     * @param $errorInfo
     * 使用E抛出异常
     */
    public static function throwsErrorMsg($errorInfo) {
        E($errorInfo[1], $errorInfo[0]);
    }
}