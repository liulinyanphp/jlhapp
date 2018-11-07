<?php

/** 提币模块枚举
 * Class WithdrawCoinConfig
 */
namespace Common\Conf;
class BaseConfig{


    /**
     * 提币状态
     */
    const ApprovalStateSubmitted = "SUBMITTED";//提币申请已提交
    const ApprovalStateReviewd = "REVIEWED";//提币申请已审批
    const ApprovalStateHandled = "HANDLED";//提币申请已处理
    const ApprovalStateFinished = "WITHDRAW_SUC";//提币申请已完成
    const ApprovalStateCancel = "WITHDRAW_CANCEL";//提币申请已取消
    const ApprovalStateFailed = "WITHDRAW_FAIL";//提币申请失败
    const ApprovalStateManual = "MANUAL";//提币申请需人工审核




    /**
     * 提币状态
     */
    public static $approval_state = array(
        self::ApprovalStateSubmitted  => '提交',
        self::ApprovalStateReviewd => '审批',
        self::ApprovalStateHandled  => '处理',
        self::ApprovalStateFinished  => '完成',
        self::ApprovalStateCancel  => '取消',
        self::ApprovalStateFailed  => '失败',
        self::ApprovalStateManual  => '人工审核'
    );


    /**
     * 提现状态流转
     * SUBMITTED 提现单提交，待处理
     * FINISHED  已打款给用户，提现完成
     * REJECTED  提现单驳回
     * COLLECTED 已回收至进货表
     */
    const WithdrawDepositSubmitted = "SUBMITTED";//提现单提交，待处理
    const WithdrawDepositFinished = "FINISHED";//已打款给用户，提现完成
    const WithdrawDepositRejected = "REJECTED";//提现单驳回
    const WithdrawDepositCollected = "COLLECTED";//已回收至进货表


    /**
     * 定义一个提现状态数组 方便后台操作人员审核用
     */
    public static $withdraw_deposit_state = array(
        self::WithdrawDepositSubmitted => '已提交,待处理',
        self::WithdrawDepositFinished => '已打款,已完成',
        self::WithdrawDepositRejected => '已驳回',
        self::WithdrawDepositCollected => '已回收'
    );


    /**
     * 冻结类型
     */
    const FreezeTypeWithdrawCoin = "WITHDRAW_COIN";//提币冻结类型
    const FreezeTypeWithdrawDeposit = "WITHDRAW_DEPOSIT";//提现冻结类型
    const FreezeTypeSendTrans = "SEND_TRANS";//提现冻结类型

    /**
     * 运营地址冻结类型
     */
    const FreezeTypeHotToCold = "HOT_TO_COLD";//热转冷类型



    /**
     * 解冻类型类型
     */
    const UnFreezeTypeCacelWithdrawCoin = "WITHDRAW_CANCEL";//提币解冻类型
    const UnFreezeTypeFailedWithdraw = "WITHDRAW_FAILED";//提币失败解冻类型


    /**
     * 交易状态
     */
    const TransactionStateCreated = "CREATED";//交易已新建
    const TransactionStatSending = "SENDING";//交易发送中
    const TransactionStateSuc = "SEND_SUC";//交易发送成功
    const TransactionStateFail = "SEND_FAIL";//交易发送失败
    const TransactionStateNotified = "NOTIFIED";//提币交易已通知平台
    const TransactionStateManual = "MANUAL";//交易发送需人工审核




    /**
     * 交易状态
     */
    public static $transaction_state = array(
        self::TransactionStateCreated  => '新建',
        self::TransactionStatSending => '发送中',
        self::TransactionStateSuc  => '发送成功',
        self::TransactionStateFail  => '发送失败',
        self::TransactionStateManual  => '人工审核'

    );

    /**
     * 充币状态
     */
    const  DepositStateCreated = "CREATED";//充币记录已新建
    const  DepositStateHandled = "HANDLED";//充币已处理
    const  DepositStateUnvalid = "UNVALID";//充币无效
    const  DepositStateNotified= "NOTIFIED";//充币记录已通知平台

    /**
     * 定义一个充币状态数组,方便后台操作人员查询
     */
    public static $DepositStateSearch = array(
        self::DepositStateCreated=> '已新建',
        self::DepositStateHandled=> '已处理'
    );

    const TetherToken = "USDT";//泰达币
    /**
     * 币种列表
     */
    public static $tokens= array(
        self::TetherToken => '泰达币',
    );


    /**
     * 平台常量
     */
    const PlatFormHXYL = 'HXYL';//恒兴娱乐

    public static $platform_list = array(
        self::PlatFormHXYL => '恒兴娱乐'
    );

    const AddressTypeH = 'H';
    const AddressTypeC = 'C';


    public static $addres_type_list = array(
        self::AddressTypeH => 'H',
        self::AddressTypeC => 'C'
    );

    /**
     * 热钱包转冷钱包大小阈值
     */
    const HotToCold_max_limit_balance = 'HotToCold_max_limit_balance';
    const HotToCold_min_limit_balance = 'HotToCold_min_limit_balance';

    /**
     * 购买状态
     */
    const PurchaseCoinSubmit = "SUBMITTED";//购买提交，待处理

    /**
     * 充值订单状态流转
     * SUBMITTED  订单已提交,待处理
     * FINISHED   订单已经审核完成
     * REJECTED   订单因不满足条件而被驳回
     */
    const PurchaseCoinRecordSubmitted = 'SUBMITTED';
    const PurchaseCoinRecordFinished  = 'FINISHED';
    const PurchaseCoinRecordRejected  = 'REJECTED';
    const PurchaseCoinRecordEmpty = 'ALL';

    /**
     * 定义一个数组 方便后台中文列表展示用
     */
    //
    public static $PurchaseCoinRecordStatus = array(
        self::PurchaseCoinRecordEmpty     => '请选择状态',
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
     * 1地址已分配
     * 0地址未分配
     */
    const AddressIsUsed = '1';
    const AddressUnUse  = '0';
    const BusAddressUnDeleted = 0;
    const BusAddressIsDeleted = 1;

    /**
     * 定义一个数组,方便后台操作人员查询用
     */
    public static $AddressUseStatusSearch = array(
        self::AddressIsUsed => '已分配',
        self::AddressUnUse =>  '未分配'
    );
    /**
     * 定义一个数组,方便后台操作人员查询用
     */
    public static $BusAddressIsDeletedSearch = array(
        self::BusAddressUnDeleted => '有效',
        self::BusAddressIsDeleted => '无效'
    );



    /**
     * 进货单类型
     * 1外部购买
     * 2库存中转入
     * 7所有类型
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