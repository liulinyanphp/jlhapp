<?php
namespace Home\Controller;
use Think\Controller;
use Common\Service\UtilService;
use Common\Service\RsaService;
use Common\Service\Bitcoin;
class HuobiController extends Controller {

	const TOKEN = 'HUOBI_API';
    const PLATCODE = 'huobi';
    
    //第三方平台请求的接口demo
    /*
     * @parames platfrom=>哪个平台请求过来的 1为火币
	 * @parames uid=>平台用户id
	 * @parames act=>接口的操作方法
	 * 
    */
    public function sendAction(){
        //时间戳
        $timeStamp = time();
        //随机数
        $randomStr = $this->_createRandomStr();
        //生成签名
        $signature = $this->_cszb_create_sign($timeStamp,$randomStr);
        //api接口的地址
        $apiurl = C('PUBLIC_APIS');
        //平台编码
        $platcode = self::PLATCODE;

        //RSA共钥对发送的实体数据加密
        //$postbody = $this->_send_address();
        //$postbody = $this->_send_bank();
        //$postbody = $this->_send_purchase_coin();

        //$postbody = $this->_withdraw_cash_apply();
        $postbody = $this->testWithdraw();
        
		$content = base64_encode(json_encode($postbody));
		$content = $this->_ras_postbody_create($platcode,$content);
    	$request = array(
    		'platform'=>$platcode,
    		'time'=> $timeStamp,
    		'sign'=>$signature,
    		'content' => urlencode($content),
    		'random'=>$randomStr,
        );

    	print_r($request);
        $res = $this->_send_info_bypost($apiurl,$request);
        print_r(json_decode($res));
    }

    //火币请求我们的地址接口数据
    private function _send_address()
    {
        $postbody = array(
            'userId'=>'1006',
            'act'=>'GetAddress',
            'platformCode'=>'huobi',
            'currencyName'=>'usdt'
        );
        return $postbody;
    }


    //提现申请
    private function _withdraw_cash_apply()
    {
        /**
         * trade_no		    string		交易单号
         * uid				string		用户id
         * plat_code		string		平台编码        校验是否存在该平台
         * real_name		string		用户姓名
         * bank  			string		银行类型
         * branch_bank		string		开户支行
         * bank_card_no 	string		银行卡号
         * currency_code    string      提现币种
         * sell_num		    string		提现个数        校验个数小数点后四位
         * curr_price		string		提现单价
         * apply_amount     string      提现金额
         */
        $postbody = array(
            'uid'=>1,
            'act'=>'WithdrawCashApply',
            'code'=>'usdt',
            'postdata' => array(
                'tradeId' =>'tixian123400001',
                'userId' => '1006',
                'platformCode' => 'huobi',
                'userName' => '张三',
                'bankName' => '中国银行',
                'branchBankName' => '开户支行',
                'bankCardNo' => '6222000022220000222',
                'applyNum' => '2.0000',
                'unitPrice' => '6.10',
                'currencyName' => 'USDT',
                'totalAmount' => '12.20'
            )
        );
        return $postbody;
    }

    public function  testWithdraw(){
        $postbody = array(
            "act"=>"WithdrawCoin",
            "tradeId"=>"3446357",
            "platformCode"=>"huobi",
            "userId"=>"109043",
            "currencyName"=>"USDT",
            "senderAddress"=>"mvCnNBzLcpC1DWAuqCGRkR18RmMmNdanbN",
//            "receiverAddress"=>"12fdbggjrbyt645676958989098",
            //              "receiverAddress"=>"jg78978",
            "receiverAddress"=>"mog7SXPDLh7SgA4ErafjuNvqKseAq86RM6",


            "applyWithdrawNum"=>"10.000000001",
            "serviceChargeNum"=>"1.000000001",
            "releaseNum"=>"9.00000000"
        );


        return $postbody;

    }



    //火币请求我们的银行卡接口数据
    /*
    * uid=>谁要获取我们的银行卡地址：即谁要购买
    * act=>和我们约定的获取银行卡方法
    * bankname=>客户的银行卡名称（如果能提供的话,我们能友好的匹配）
    * code=>告诉我们你是因为要买什么才来获取我们的银行卡地址
    */
    private function _send_bank()
    {
        $postbody = array('uid'=>1,'act'=>'GetBankCar','bankname'=>'招商银行','code'=>'usdt');
        return $postbody;
    }

    //火币请求我们的用户充值接口数据
    /*
     * @parames => array(
        'uid'=>'1',                          //购买币的用户id
        'trade_id'=>'huobi00000001',         //交易号
        'plat_code'=>'huobi',                //平台编码
        
        'user_addr'=>'address',              //用户收币地址,
        'status'=>'未付款',                   //基础状态
        'bank_card_no'=>'122332',            //银行卡号
        'bank_name'=>'银行名称',              //银行名称
        'bank_card_holder'=>'持卡人',         //银行卡持有人姓名
        'reference_no'=>'充值参考号',          //充值参考号
        'amount'=>'1000',                    //充值金额
        'curr_price'=>'6.42',                //兑换价格
        'num'=>'10.45',                      //折合币个数
        'currency_name'=>'usdt',             //币种名称
        'bank_trans_no'=>'12e21321312',      //银行流水号
        'payment_time'=>'',                  //到账时间
        'operator_id'=>'',                   //操作人id
        'operator_name'=>'sys_huobi_api',    //操作人员姓名
        'create_by'=>'sys_huobi_api',        //创建人
        'last_modified_by'=>'sys_huobi_api', //更新人
     )
     * 把订单信息传过来,我们进行核对,如果价格和数量没问题,我们就入库,等待我们的工作人员工人审核钱是否到账，如果到账,
       则会把代币划转到个人的余额里面,并且通知平台进行该用户的余额变更
    */
    private function _send_purchase_coin()
    {
        $postbody = array(
            'uid'=>1,
            'act'=>'PurchaseCoin',
            'code'=>'usdt',
            'postdata' => array(
                't_id' =>'P001',
                'p_code' =>'huobi',
                'u_addr'=>'address',
                'status'=>'WAIT_TO_PAY',
                'card_no'=>'122332',
                'bankname'=>'银行名称',
                'card_holder'=>'持卡人',
                'referenceNo'=>'6789',
                'amount'=>'100',
                'currPrice'=>'6.42',
                'num'=>'15.57632398',
                'currencyName'=>'currency_name'
            )
        );
        return $postbody;
    }





    //随机生成字符串
    private function _createRandomStr($length = 8) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return "hb".$str;
    }

    /**
     * @param $timeStamp 时间戳
     * @param $randomStr 随机字符串
     * @return string 返回签名
     */
    private function _cszb_create_sign($timeStamp,$randomStr){
        $arr['timeStamp'] = $timeStamp;
        $arr['randomStr'] = $randomStr;
        $arr['token'] = self::TOKEN;
        //按照首字母大小写顺序排序
        sort($arr,SORT_STRING);
        //拼接成字符串
        $str = implode($arr);
        //进行加密
        $signature = sha1($str);
        $signature = md5($signature);
        //转换成大写
        $signature = strtoupper($signature);
        return $signature;
    }

    /*
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
    private function _send_info_bypost($url,$postdata='',$timeout = 30)
    {
    	if(empty($url)) return ;
        //$postdata = http_build_query($postdata);
        $postdata = json_encode($postdata);
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
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		$result = curl_exec($curl);
		if($result === false){
		    return curl_errno($curl);
		}
		return $result;
	}

	/*
	 * @parame $postbody array 要传参的内容体
	*/
	private function _ras_postbody_create($platcode,$send_data)
	{
		$keys = C('RSA_KEYS');
		$public_key = ROOT.$keys[$platcode]['PUBLIC_KEY'];
		//公钥加密
		$rsa = new RsaService();
		$encrypt = $rsa->rsaEncrypt($send_data,$public_key);
		return $encrypt;
	}
}

 