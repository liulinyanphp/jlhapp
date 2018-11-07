<?php

namespace  Common\Exception;
/**
 * 交易异常code
 * 异常code  前四位为业务模块编码，后四位为异常code编码
 * Class WithdrawCoinError
 */
class TransactionErrorCode {

    public static $PARAM_EMPTY_ERROR			         = array(50000001,'参数不能为空！');
    public static $TRANS_FAILD_ERROR			         = array(50000002,'发送交易失败！');
    public static $UTXO_NOT_ENOUGH_ERROR			     = array(50000003,'utxo不足！');
    public static $BALANCDE_NOT_ENOUGH_ERROR			 = array(50000003,'余额不足！');


















}