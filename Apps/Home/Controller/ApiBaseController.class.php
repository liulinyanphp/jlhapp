<?php
/*
 * desc 接口服务基础服务类
*/
namespace Home\Controller;
use Think\Controller;
use Common\Service\RsaService;
use Common\Service\PlatformService;
use \Org\Util\Exception;
use \Org\Util\Result;
use \Common\Utils\ExloggerUtils;

class ApiBaseController extends Controller {

	//白名单和doman过滤
	private $_domain_list = array('usdt.com');
	private $_allow_ips = array('127.0.0.1');

	private $_api_is_pass = 0;
	private $_post_data = array();

	/**
	 * addby : lly
	 * date : 2018-03-18 17:08
	 * used : api接口请求,签名验证
	 * desc : 共用接口初始化
	**/
	public function _initialize()
	{
	    try{
            $postdata = $this->_get_post_data();
            if(empty($postdata)){
                Exception::throwsErrorMsg(C('API_PARAM_IS_EMPTY'));
            }
            //查看平台是否开启对接
            $token = PlatformService::get_plat_token($postdata['platform']);
            if(empty($token)){
                Exception::throwsErrorMsg(C('API_PLATCODE_IS_NOT_FIND'));
            }
            //启用签名认证
            $ck_sign = $this->_cszb_chenk_sign($postdata['platform'],$postdata['time'],$postdata['random']);
            if($ck_sign !== $postdata['sign'])
            {
                Exception::throwsErrorMsg(C('API_SIGN_IS_ERROR'));
            }
            //启用密钥认证 以防攻击
            $private_v = $this->_rsa_safe_check($postdata['platform'],$postdata['content']);
            $postbody = json_decode(base64_decode($private_v),true);
            if(empty($postbody)) {
                Exception::throwsErrorMsg(C('API_PARAM_SAFE_CHECK_IS_ERROR'));
            }else{
                //赋一个平台id,方便后面对接平台扩展用
                //为了兼容二维数组
                if(isset($postbody['postdata'])){
                    $tmp_data = $postbody['postdata'];
                    unset($postbody['postdata']);
                    $postbody = array_merge($postbody,$tmp_data);
                }
                $postbody['platcode'] = $postdata['platform'];
                $this->_api_is_pass = 1;
                $this->_post_data = $postbody;
            }
        }catch(\Exception $e){
	        $result = Result::apiResultFail($e);
            ExloggerUtils::log('错误信息为：'.$e->getMessage(),'error');
            return $result;
        }
	}
	//为子类提供数据载体
	public function api_content()
	{
		if($this->_api_is_pass > 0)
		{
            ExloggerUtils::log('接口解析后的数据：'.json_encode($this->_post_data,JSON_UNESCAPED_UNICODE),'info');
			return $this->_post_data;
		}
	}

	/**
	 * addby : lly
	 * date : 2018-04-18 19:16
	 * used : 获取接口请求数据,返回我们最终需要的数据
	**/
	private function _get_post_data()
	{
		$post_content = I('post.');
        $post_data = array();
		if(!empty($post_content))
		{
			$post_data = array_keys($post_content);
			$post_data = json_decode($post_data[0],true);
            ExloggerUtils::log('接口请求过来的数据：'.json_encode($post_data),'info');
		}
        return $post_data;
	}
	/**
	 * addby : lly
	 * date : 2018-03-18 17:14
	 * used : 内部校验签名
	 * @param $platform 平台id 拿取密钥用 默认火币
     * @param $timeStamp 时间戳
     * @param $randomStr 随机字符串
     * @return string 返回签名
    **/
    private function _cszb_chenk_sign($platcode='huobi',$timeStamp,$randomStr){
        $arr['timeStamp'] = $timeStamp;
        $arr['randomStr'] = $randomStr;
        $token = PlatformService::get_plat_token($platcode);
        if(empty($token)){
        	return 'false';
        }
        $arr['token'] = $token;
        //按照首字母大小写顺序排序
        sort($arr,SORT_STRING);
        //拼接成字符串
        $str = implode($arr);
        //进行加密
        $signature = sha1($str);
        $signature = md5($signature);
        //转换成大写
        $signature = strtoupper($signature);
        //echo '生存后的签名为'.$signature;
        return $signature;
    }

    /**
     * addby : lly
     * date : 2018-04-15 19:26
     * used : 接收第三方接口rsa验证请求
     * @param $postbody 第三方接口传过来的用公钥加密的内容
     * @return string 解密出来的文件
	**/
	private function _rsa_safe_check($platcode,$postbody)
	{
		$request_str = str_replace(' ','+',$postbody);
		$keys = C('RSA_KEYS');
		$private_key = ROOT.$keys[$platcode]['PRIVATE_KEY'];
		$rsa = new RsaService();
		$decrypt = $rsa->rsaDecrypt($request_str,$private_key);
		//解析出请求过来的数据
		return $decrypt;
	}

	//白名单和doman过滤 预留
    private function _request_check()
    {
    	$referer = $_SERVER['HTTP_REFERER'];
    	$ip = get_real_ip();
    	if(!in_array($ip,$this->_allow_ips)){
    		return '您不在ip白名单内';
    	}
    	if($referer){
    		$refererhost = parse_url($referer);
    		//获取来源地址的主域名
    		$host = strtolower($referer['host']);
    		if(!in_array($host,$this->_domain_list)){
    			return '您不在域名白名单内';
    		}
    	}
    	return 1;
    }

}