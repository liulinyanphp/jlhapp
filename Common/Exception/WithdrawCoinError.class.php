<?php

namespace  Common\Exception;
/**
 *
 * 异常code  前四位为业务模块编码，后四位为异常code编码
 * Class WithdrawCoinError
 */
class WithdrawCoinError {

    public static $PARAM__EMPTY_ERROR			         = array(40000001,'参数不能为空！');
    public static $USER__INFO_ERROR						 = array(40000002,'用户信息错误！');
    public static $ADDR_NOT_VALID_ERROR					 = array(40000003,'不是有效的地址！');
    public static $TRADE_ID_VALID_ERROR					 = array(40000004,'交易号有误！');
    public static $WITH_DRAW_NUM_ERROR					 = array(40000005,'提币数量有误！');
    public static $CURRENCY_TYPE_ERROR					 = array(40000006,'币种信息有误！');
    public static $OUT_OF_BALANCE_ERROR					 = array(40000007,'提币数量超出总余额！');
    public static $USER_BALANCE_ACCOUNT_ERROR			 = array(40000008,'账户余额信息有误！');
    public static $WITHDRAW_ROCORD_NOT_FOUND_ERROR		 = array(40000009,'提币申请记录不存在！');
    public static $WITHDRAW_APPLY_NOT_CANCEL_ERROR		 = array(40000010,'提币申请撤销失败！');
    public static $PARAM_NOT_VALID_ERROR		         = array(40000011,'格式有误！');
    public static $WITHDRAW_NUM_ERROR		             = array(40000012,'提币数量有误！');
    public static $WITHDRAW_FEE_ERROR		             = array(40000013,'提币手续费有误！');
    public static $WITHDRAW_NUM_NOT_ALLOWED_ERROR		 = array(40000014,'提币数量不能少于200个！');
    public static $WITHDRAW_RECORD_NOT_FOUND_ERROR		 = array(40000015,'交易单不存在！');
    public static $DATA_EXCEPTION		                 = array(40000016,'数据异常！');


    public static $INNER_DB_EXCEPTION		             = array(40000030,'数据库异常！');

















}