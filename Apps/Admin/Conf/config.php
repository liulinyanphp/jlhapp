<?php
return array(
	'ADMIN_LEFT_MENU' => array(
        'Rbac' => array(
            'name'=>'权限管理','mod'=>'Rbac','icon'=>'am-icon-users','url'=>'rbac/node_list',
            'list'=>array( 
                array('name' => '角色列表', 'url' => 'rbac/role_list','act'=>'role_list'),
                array('name' => '权限列表', 'url' => 'rbac/node_list','act'=>'node_list'),
                array('name' => '用户列表', 'url' => 'rbac/user_list','act'=>'user_list')
            )),
        'Advertising' =>array(
            'name'=>'广告管理','mod'=>'Advertising','icon'=>'am-icon-gears','url'=>'',
            'list'=>array(
                array('name'=>'类型列表','url'=>'Advertising/AdTypeList','act'=>'AdTypeList'),
                array('name'=>'广告列表','url'=>'Advertising/list','act'=>'list')
                //array('name'=>'新增广告','url'=>'Advertising/add','act'=>'add')
            )
        ),

        'Headline'=>array(
            'name'=>'头条信息管理','mod'=>'Headline','icon'=>'am-icon-money','url'=>'',
            'list'=>array(
                array('name'=>'头条信息列表','url'=>'headline/list','act'=>'list'),
                array('name'=>'新增头条信息','url'=>'headline/add','act'=>'add'),
                array('name'=>'信息举报列表','url'=>'headline/report','act'=>'report')
            )
        ),
        'Config'=>array(
            'name'=>'配置管理','mod'=>'Config','icon'=>'am-icon-money','url'=>'',
            'list'=>array(
                array('name'=>'关键词列表','url'=>'config/keywordsList','act'=>'keywordsList'),
                //array('name'=>'添加关键词','url'=>'config/keyWordsAdd','act'=>'keyWordsAdd'),
                array('name'=>'评级级别列表','url'=>'config/ratingList','act'=>'tokenList'),
                //array('name'=>'新增评级级别','url'=>'config/ratingAdd','act'=>'ratingAdd'),

                array('name'=>'token分配列表','url'=>'config/tokenList','act'=>'tokenList'),
                //array('name'=>'新增token分配','url'=>'config/distributeAdd','act'=>'distributeAdd'),

                array('name'=>'投资机构列表','url'=>'config/investmentList','act'=>'investmentList'),
                //array('name'=>'添加投资机构','url'=>'config/investmentAdd','act'=>'investmentAdd'),

                array('name'=>'众筹渠道列表','url'=>'config/channelList','act'=>'channelList'),
                //array('name'=>'添加众筹渠道','url'=>'config/channelAdd','act'=>'channelAdd'),

                array('name'=>'竞品配置列表','url'=>'config/competitorList','act'=>'competitorList'),
                //array('name'=>'添加竞品信息','url'=>'config/competitorAdd','act'=>'competitorAdd'),

                array('name'=>'行业分析列表','url'=>'config/analysisList','act'=>'analysisList'),
                //array('name'=>'添加行业分析','url'=>'config/analysisAdd','act'=>'analysisAdd'),

                array('name'=>'众筹单位列表','url'=>'config/crowdunitList','act'=>'tokenList'),
                //array('name'=>'新增众筹单位','url'=>'config/crowdunitAdd','act'=>'tokenAdd'),

                array('name'=>'推送消息配置列表','url'=>'config/pushcfgList','act'=>'pushcfgList'),
                //array('name'=>'新增推送消息','url'=>'config/pushcfgAdd','act'=>'pushcfgAdd'),

                array('name'=>'新手帮助列表','url'=>'config/helpList','act'=>'helpList')
            )
        ),
        'Project'=>array(
            'name'=>'项目管理','mod'=>'Project','icon'=>'am-icon-money','url'=>'',
            'list'=>array(
                array('name'=>'项目列表','url'=>'project/list','act'=>'list'),
                array('name'=>'添加项目','url'=>'project/add','act'=>'add'),
                array('name'=>'推荐项目','url'=>'project/recommend','act'=>'rcommend'),
                array('name'=>'价格获取配置列表','url'=>'project/priceConfig','act'=>'priceConfig')
            )
        ),
        'Investment'=>array(
            'name'=>'投研信息管理','mod'=>'Investment','icon'=>'am-icon-money','url'=>'',
            'list'=>array(
                array('name'=>'投研信息列表','url'=>'investment/list','act'=>'list'),
                //array('name'=>'新增投研信息','url'=>'investment/add','act'=>'add'),
                array('name'=>'投研举报列表','url'=>'investment/report','act'=>'report')
            )
        ),
        'Datamsg'=>array(
            'name'=>'数据管理','mod'=>'Datamsg','icon'=>'am-icon-money','url'=>'',
            'list'=>array(
                array('name'=>'系统信息推送列表','url'=>'datamsg/sysPushlist','act'=>'sysPushlist'),
                array('name'=>'项目信息推送列表','url'=>'datamsg/proPushList','act'=>'proPushList'),
                array('name'=>'Token价格列表','url'=>'datamsg/priceList','priceList'),
                array('name'=>'项目关注列表','url'=>'datamsg/proFloolwList','proFloolwList'),
                array('name'=>'用户投资记录列表','url'=>'datamsg/investRecord','act'=>'investRecord'),
                array('name'=>'用户投资流水列表','url'=>'datamsg/investRecordLog','act'=>'investRecordLog')
            )
        ),
//       	'Bankcard'=>array(
//        	'name'=>'银行卡管理','mod'=>'Bankcard','icon'=>'am-icon-money','url'=>'',
//        	'list'=>array(
//        		array('name'=>'银行卡查询列表','url'=>'bankcard/list','act'=>'list'),
//        		array('name'=>'新增银行卡','url'=>'bankcard/add','act'=>'add'),
//                array('name'=>'银行卡操作日志','url'=>'bankcard/loglist','act'=>'loglist')
//        	)
//        ),
//        'Notice'=>array(
//            'name'=>'公告管理','mod'=>'Notice','icon'=>'am-icon-money','url'=>'',
//            'list'=>array(
//                array('name'=>'公告列表','url'=>'Notice/list','act'=>'list'),
//                array('name'=>'新增公告','url'=>'Notice/add','act'=>'add')
//            )
//        ),
        'Address'=>array(
            'name'=>'地址管理','mod'=>'Address','icon'=>'am-icon-users','url'=>'',
            'list'=>array(
                array('name'=>'地址列表','url'=>'address/list','act'=>'list')
            ))
    ),


    //进货单的类型
    'PURCHASE_TYPE'=>array('1'=>'外部购买','2'=>'从库存中转入'),

    'PURCHASE_ADD_OK'=> '进货单录入成功',
    'PRESENT_CHECK_OK'=>'提现单处理成功',


    //后台数字验证
    'ADMIN_NUMBER_CHECK'=>array(
        'ERROR_PURCHASE_NUM_NEED_NUMBER'=>array('700010001','进货数量必须是数字','isPositiveNumber'),
        'ERROR_CURR_PRICE_NEED_NUMBER'=>array('700010002','进货价格必须是数字','isPositiveNumber'),
        'ERROR_TOTAL_AMOUNT_NEED_NUMBER'=>array('700010003','进货总金额必须是数字','isPositiveNumber'),
        'ERROR_PAY_AMOUT_NEED_NUMBER'=>array('80001001','打款金额必须是数字','isPositiveNumber')
    ),

    //进货单添加失败
    'ERROR_PURCAHSE_STOCK_ADD_ERR' => array('700010009','进货单公用Server处添加逻辑出错'),


    'ERROR_PURCHASE_NUM_IS_EMPTY'=>array('700010005','进货数量不能为空'),
    'ERROR_CURR_PRICE_IS_EMPTY'=>array('700010006','进货价格不能为空'),
    'ERROR_TOTAL_AMOUNT_IS_EMPTY'=>array('700010007','进货总金额不能为空'),
    'ERROR_SENDER_ADDR_IS_EMPTY'=>array('700010008','进货地址不能为空'),
    //提现审核

    'ERROR_BANK_TRANS_NO_IS_EMPTY'=>array('80001002','银行流水不能为空'),
    'ERROR_PAYMENT_TIME_IS_EMPTY'=>array('80001003','到账时间不能为空'),
    'ERROR_PAY_AMOUNT_IS_EMPTY'=>array('80001004','打款金额不能为空'),
    'ERROR_PAY_AMOUT_IS_NEQ'=>array('80001005','提现金额和打款金额不相等呢'),
    'ERROR_PRESENT_CHECK_ERR' => array('8001006','提现单审核异常'),



    //以下是后台状态配置

    /**********  充值订单状态流转  **********/
    //SUBMITTED  订单提交
    //FINISHED   订单已经审核完成
    //REJECTED   订单因不满足条件而被驳回
    'PurchaseCoinRecordSubmitted' => 'SUBMITTED',
    'PurchaseCoinRecordFinished'  => 'FINISHED',
    'PurchaseCoinRecordRejected'  => 'REJECTED',

    //定义一个数组 方便后台中文展示用
    'PurchaseCoinRecordStatus' => array(
        'SUBMITTED' => '已提交',
        'FINISHED'  => '已处理',
        'REJECTED'  => '已驳回'
    ),



    'OPEN_CLOSE_BTN'=>array('open','close'),
    //后台编辑的报错
    'PRO_DB_ERROR'=>array(
        'protb'=>array('50000001','项目表sql出错了'),
        'pro_parame'=>array('50000002','项目表参数出错了'),
        'logtb'=>array('50000011','项目日志表sql出错了')
    ),
    'NOTICE_DB_ERROR'=>array(
        'noticetb'=>array('60000001','公告表sql出错了'),
        'notice_parame'=>array('60000011','公告参数出错了')
    ),
    'ERROR_NO_CHANGE'=>array(
        'notice_edit'=>array('600000002','公告信息未做任何修改')
    ),
    'ADDRESS_DB_ERROR'=>array(
        'address_empty'=>array('70000001','地址获取失败')
    )
);