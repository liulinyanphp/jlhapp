<?php
/**
 * Created by PhpStorm.
 * User: lly
 * Date: 2018/5/22
 * Time: 下午6:57
 */

namespace Common\Utils;

use Common\Utils\ExloggerUtils;


class HttpUtils{

    /**
	 * addby : lly
	 * date : 2018-03-18 17:31
	 * used : 共用的post请求
	 * @parame $platform 平台映射id
	 * @parame $uid 当前请求操作的用户id
	 * @parame $method 当前请求的操作action
	 * @parame $time 当前请求的时间戳
	 * @parame $params 请求的内容体需要加密
	 *
	 *
    */
    public static function post($url,$postdata='',$needToJson=1,$timeout = 30)
    {
        if(empty($url)) return ;
        //$postdata = http_build_query($postdata);
        if($needToJson!='0')
        {
            $postdata = json_encode($postdata);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:'));
        if( $postdata != '' && !empty( $postdata ) )
        {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:'));
            //curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($postdata)));
        }
        ExloggerUtils::log('SEND BEGIN '.$url.' param is :'.is_array($postdata)?json_encode($postdata):$postdata,'info');
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        $result = curl_exec($curl);
        $msg = is_array($result) ? json_encode($result) : $result;
        ExloggerUtils::log('RESPONSE '.$msg,'info');
        if($result === false){
            return curl_errno($curl);
        }
        return $result;
    }
}