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
 * 结果工具类
 * add:lly
 */
class Result {

    // 接口正确返回信息
    static private $apiResultSuccess = array('status'=>'0','message'=>'ok','data'=>array());

    // 内部正确返回信息
    static private $innerResultSuccess = array('result'=>'0','res_info'=>'ok','result_rows'=>array());

    // 接口错误返回信息
    static private $apiResultFail = array('status'=>'-1','message'=>'', 'data'=>array());

    // 内部错误返回信息
    static private $innerResultFail = array('result'=>'-1','res_info'=>'','result_rows'=>array());

    /**
     * @param array $data
     * @return array
     * API接口数据成功返回函数
     */
    static public function apiResultSuccess($data=array())
    {
        $result = self::$apiResultSuccess;
        if(!empty($data)){
            $result['data'] = $data;
        }
        return $result;
    }

    /**
     * @param array $data
     * @return array
     * 内部使用数据成功返回函数
     */
    static public function innerResultSuccess($data=array())
    {
        $result = self::$innerResultSuccess;
        if(!empty($data)){
            $result['result_rows'] = $data;
        }
        return $result;
    }

    /**
     * @param Resource $e
     * @return array $result
     * API接口数据异常返回函数
     */
    static public function apiResultFail($e)
    {
        $result = self::$apiResultFail;
        $result['status'] = $e->getCode();
        $message = $e->getMessage();
        if(strpos($e->getMessage(),'[ SQL语句 ]')){
            $result['status'] = '300';
            $message = explode('[ SQL语句 ]',$e->getMessage());
            $message = $message[0];
        }
        $result['message'] = $message;  //$e->getMessage();

        return $result;
    }

    /**
     * @param Resource $e
     * @return array $result
     * 内部数据异常返回函数
     */
    static public function innerResultFail($e)
    {
        $result = self::$innerResultFail;
        $result['result'] = $e->getCode();
        $result['res_info'] = $e->getMessage();
        return $result;
    }

    /**
     * @param Resource array
     * @return array $result
     * 内部自定义异常返回函数
     */
    static public function selfResultFail($error=array())
    {
        $result = self::$innerResultFail;
        $result['result'] = $error[0];
        $result['res_info'] = $error[1];
        return $result;
    }
}