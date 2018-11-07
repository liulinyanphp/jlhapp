<?php
/**
 * Created by PhpStorm.
 * User: tang
 * Date: 2018/5/23
 * Time: 上午11:09
 */

namespace  Common\Exception;
class BankSystemErrorCode{

    //purchase_stock
    public static $PARAM__EMPTY_ERROR			         = array(60000001,'参数不能为空！');
    public static $PURCHASE_TOTAL_AMOUNT_ERROR			 = array(60000002,'进货总金额有误，请再核实');
    public static $PURCHASE_TX_NOT_EXIST_ERROR			 = array(60000003,'交易单不存在，请再核实！');
    public static $PURCHASE_TX_INVALID_ERROR			 = array(60000004,'交易无效，请再核实');
    public static $PURCHASE_TOTAL_NUM_ERROR			     = array(60000005,'进货数量有误，请再核实');
    public static $PURCHASE_RECEIVE_ADDR_ERROR			 = array(60000006,'收货地址有误');
    public static $PURCHASE_TRADE_NO_EXIST_ERROR		 = array(60000007,'该交易单已经扣减过库存');
    public static $PURCHASE_NUM_NOT_ENOUGTH_ERROR		 = array(60000008,'剩余数量不足');


    public static $xxxxxxxxxxxxxxxxx			         = array(60000005,'xxxxxxxxxxxxxxxxx');


    //hot_to_cold
    public static $HOTTOCOLD_ADDRESS_INVALID_ERROR		 = array(60000101,'不是有效的比特币地址');
    public static $HOTTOCOLD_ADDRESS_NOT_EXIST_ERROR     = array(60000102,'运营地址不存在');
    public static $HOTTOCOLD_UTXO_NOT_EXIST_ERROR        = array(60000103,'运营地址utxo不足');





}