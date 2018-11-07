<?php
/**
 * 后台状态码配置
 */
namespace Common\Conf;
class StatusConf{

    /**
     * 充值订单状态流转
     * SUBMITTED  订单提交
     * FINISHED   订单已经审核完成
     * REJECTED   订单因不满足条件而被驳回
     */
    const PurchaseCoinRecordSubmitted = 'SUBMITTED';
    const PurchaseCoinRecordFinished  = 'FINISHED';
    const PurchaseCoinRecordRejected  = 'REJECTED';

    /**
     * 定义一个数组 方便后台中文列表展示用
     */
    //
    public static $PurchaseCoinRecordStatus = array(
        self::PurchaseCoinRecordSubmitted => '已提交',
        self::PurchaseCoinRecordFinished  => '已处理',
        self::PurchaseCoinRecordRejected  => '已驳回'
    );

    /**
     * 定义一个数组 方便后台操作人员审核用
     */
    public static $PurchaseCoinRecordStatusCheck = array(
        self::PurchaseCoinRecordFinished => '通过',
        self::PurchaseCoinRecordRejected => '驳回'
    );





    /**
     * 地址分配状态
     */
    const AddressIsUsed = '1';
    const AddressUnUse  = '0';
    const AddressIsUsedEmpty = '7';

    /**
     * 定义一个数组,方便后台操作人员查询用
     */
    public static $AddressUseStatusSearch = array(
        self::AddressIsUsedEmpty => '请选择状态',
        self::AddressIsUsed => '已分配',
        self::AddressUnUse =>  '未分配'
    );


    /**
     * 经货单类型
     */
    const PurchaseTypeFromBuy = '1';
    const PurchaseTypeFromTurn = '2';
    const PurchaseTypeEmpty = '7';

    /**
     * 定义一个数组,方便后台操作人员查询
     */
    public static $PurchaseTypeSearch = array(
        self::PurchaseTypeEmpty => '请选择类型',
        self::PurchaseTypeFromBuy => '外部购买',
        self::PurchaseTypeFromTurn => '库存中转入'
    );

}
    //进货单的类型
    //const 'PURCHASE_TYPE' = array('1'=>'外部购买','2'=>'从库存中转入');

//    'PURCHASE_ADD_OK'=> '进货单录入成功',
//    'PRESENT_CHECK_OK'=>'提现单处理成功',


    //后台数字验证
//    'ADMIN_NUMBER_CHECK'=>array(
//        'ERROR_PURCHASE_NUM_NEED_NUMBER'=>array('700010001','进货数量必须是数字','isPositiveNumber'),
//        'ERROR_CURR_PRICE_NEED_NUMBER'=>array('700010002','进货价格必须是数字','isPositiveNumber'),
//        'ERROR_TOTAL_AMOUNT_NEED_NUMBER'=>array('700010003','进货总金额必须是数字','isPositiveNumber'),
//        'ERROR_PAY_AMOUT_NEED_NUMBER'=>array('80001001','打款金额必须是数字','isPositiveNumber')
//    ),

    //进货单添加失败
//    'ERROR_PURCAHSE_STOCK_ADD_ERR' => array('700010009','进货单公用Server处添加逻辑出错'),
//
//
//    'ERROR_PURCHASE_NUM_IS_EMPTY'=>array('700010005','进货数量不能为空'),
//    'ERROR_CURR_PRICE_IS_EMPTY'=>array('700010006','进货价格不能为空'),
//    'ERROR_TOTAL_AMOUNT_IS_EMPTY'=>array('700010007','进货总金额不能为空'),
//    'ERROR_SENDER_ADDR_IS_EMPTY'=>array('700010008','进货地址不能为空'),
    //提现审核

//    'ERROR_BANK_TRANS_NO_IS_EMPTY'=>array('80001002','银行流水不能为空'),
//    'ERROR_PAYMENT_TIME_IS_EMPTY'=>array('80001003','到账时间不能为空'),
//    'ERROR_PAY_AMOUNT_IS_EMPTY'=>array('80001004','打款金额不能为空'),
//    'ERROR_PAY_AMOUT_IS_NEQ'=>array('80001005','提现金额和打款金额不相等呢'),
//    'ERROR_PRESENT_CHECK_ERR' => array('8001006','提现单审核异常'),



//以下是后台状态配置
