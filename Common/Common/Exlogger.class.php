<?php

namespace Common\Common;

use Think\Log;

class Exlogger {

    const LOG_PATH_COMMON = 'common';
    const LOG_PATH_API = 'api';
    const LOG_PATH_ADMIN = 'admin';

    /**
     * 日志写入接口
     * @access public
     * @param string/array $message 日志信息
     * @param string $target_path  写入日子的子目标文件夹
     * @return void
     */
    public static function log($message, $target_path = self::LOG_PATH_COMMON,$level = Log::ERR)
    {
        $filePath = ROOT."/Logs/".strtolower($target_path).'/'.date('Ymd').'/'.date("H")."/".strtolower($level).".log";
        // 自动创建日志目录
        $log_dir = dirname($filePath);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        if(file_exists($filePath)){
            @chmod($filePath,FILE_WRITE_MODE);
        }
        $context = array('get'=>$_GET,'post'=>$_POST,'cookie'=>$_COOKIE,'server'=>$_SERVER);
        $fp = fopen($filePath,'a+');
        fwrite($fp,date("Y-m-d H:i:s")."LEVEL-".self::getLevelValue($level).
            '; url:'.self::get_full_url().
            '; message:'.(is_string($message) ? $message: json_encode($message))."\r\n");
        fclose($fp);
    }

    //分层
    private static  function getLevelValue($level)
    {
        switch(strtoupper($level))
        {
            case Log::DEBUG:
                return 0;
            case Log::INFO:
            case Log::NOTICE:
                return 1;
            case Log::WARN:
            case Log::CRIT:
            case Log::EMERG:
            case Log::ERR:
                return 2;
            case Log::ALERT:
                return 3;
            default:
                return 0;
        }
    }
    /**
     * 获取当前页面完整URL地址
     */
    public static function get_full_url()
    {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
    }

}