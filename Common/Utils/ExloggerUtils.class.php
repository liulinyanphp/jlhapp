<?php

namespace Common\Utils;

use Think\Log;

class ExloggerUtils {

    static public $log_type = array('info','error');

    /**
     * 日志写入接口
     * @access public
     * @param string $log 日志信息
     * @param string $type  写入类型
     * @return void
     */
    public static function log($log,$type)
    {
        $now = date('Y-m-d H:i:s');
        if(empty($type) || !in_array(strtolower($type),self::$log_type))
        {
            return '';
        }
        /*
        $filePath = C('log_path').date('Ymd').'_'.strtolower($type).'.log';
        // 自动创建日志目录
        $log_dir = dirname($filePath);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        if(file_exists($filePath)){
            @chmod($filePath,FILE_WRITE_MODE);
        }
        $fp = fopen($filePath,'a+');
        fwrite($fp,"[{$now}] ".$_SERVER['REMOTE_ADDR'].' '.$_SERVER['REQUEST_URI']."\r\n{$log}\r\n");
        fclose($fp);
        */
        Vendor('log4php.Logger');
        //引入配置文件
        \Logger::configure(ROOT.'/Common/Conf/log4php.xml');
        $logHandle = \Logger::getLogger('comon');
        $logHandle->$type('IP：'.$_SERVER['REMOTE_ADDR']."; URL：".$_SERVER['REQUEST_URI']."\r\n{$log}\r\n");
    }
}