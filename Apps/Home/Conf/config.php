<?php
return array(


	//'PUBLIC_APIS'=>'http://usdt.com/home/api/hander',
    'PUBLIC_APIS'=>'http://usdt.app:9980/home/api/handle',

	'TRAD_CHECK_FROM_URL' => 'https://www.omniexplorer.info',
	'TRAD_CHECK_URL' => 'https://api.omniexplorer.info/v1/transaction/tx/',

	//数据缓存设置
    'DATA_CACHE_TYPE' => 'Redis',
    'DATA_CACHE_PREFIX' => "cszb_api",
    'MEMCACHE_HOST' => '127.0.0.1',
    'DATA_CACHE_TIME'=>3600,
    'MEMCACHE_PORT' => 11211,
    'log_path'=>ROOT.'/Logs/',


    /**用户购买币的订单状态定义**/
    'PURCHASE_COIN_STATUS'=>array(
        'SUBMITTED'=>'已付款,待确认'
    ),


    /**API初始化错误信息**/
    'API_PARAM_IS_EMPTY'=>                 array('100000001','接口获取的post数据为空'),
    'API_PLATCODE_IS_NOT_FIND'=>           array('100000002','未找到平台code对应的授权码'),
    'API_SIGN_IS_ERROR'=>                  array('100000003','接口签名认证失败'),
    'API_PARAM_SAFE_CHECK_IS_ERROR'=>      array('100000004','参数加密认证失败'),
    'ADDRESS_CHECK_IS_ERROR' =>            array('100000005','地址数量校验错误,请仔细核对'),

    'BATCH_CREATE_ADDRESS_NUM' => 1000,









    /**地址的错误信息配置**/
    'ADDRESS_NOT_ENOUGH'=>                  array('10001003','系统没有足够的地址供平台使用了,请联系平台方！'),
    'ADDRESS_PARAM_IS_ERROR'=>              array('10001004','参数错误'),
    'ADDRESS_CREATE_IS_ERROR' =>            array('10001005','批量生成地址错误'),
    'ADDRESS_NUM_IS_ENOUGH'=>               array('10001010','地址充足,本次添加不做任何操作'),
    'ADDRESS_NUM_IS_NOT_EMPTY'=>            array('10001011','地址数目不能为空'),

    'PLAT_CODE_IS_NULL' =>                  array('10000006','平台编码未传'),
    'CURRENCY_CODE_IS_NULL' =>              array('10000007','币种编码未传'),
    'BUYER_IS_NOT_FIND'=>                   array('10000008','用户信息出错购买失败'),
    'PLAT_RATE_IS_NULL'=>                   array('10000009','该平台对应的币种未进行汇率设置,平台购买货币失败'),


    /**银行卡的错误信息配置*/
    'BANK_CARD_IS_EMPTY'=>                  array('20001003','没有找到平台提供的银行卡信息'),

    /**用户购买币的错误信息配置*/
    'PURCHASECOIN_NOT_ENOUGH'=>             array('30001003','库存不足,购买失败！'),
    'PURCHASECOIN_RATE_IS_ERROR'=>          array('30001004','汇率和我们平台设置的不匹配,购买失败'),
    'PURCHASECOIN_NUM_IS_ERROR'=>           array('30001005','传递的购买数量和计算出来的数量不一致,购买失败'),
    'PURCHASECOIN_HAVE_UNFINISH_ORDER'=>    array('30001006','您还有未完全成交的订单,购买失败！'),
    'PURCHASECOIN_NOT_FIND_ORDER'=>         array('30001007','找不到交易单'),
    'PURCHASECOIN_STATUS_IS_ERROR'=>        array('30001008','订单流转状态异常,请核对后再操作'),
    'PURCHASECOIN_AMOUNT_IS_ERROR'=>        array('30001009','请您核对转账金额，再做操作'),
    'PURCHASECOIN_PARAME_IS_ERROR'=>        array('30001010','获取交易单相关信息所传参数错误'),
    'PURCHASECOIN_INFO_IS_EXIT'=>           array('30001011','交易单已经推送过了,请勿重复推送'),
    'PURCHASECOIN_IS_NOT_EXIT'=>            array('30001012','交易单不存在')




);