<?php
/**
 * 提币业务处理
 *
 */

namespace Common\Service;

use Common\Exception\WithdrawCoinError;
use Common\Utils\ExloggerUtils;


//use Think\Exception;
use Common\Utils\ExceptionUtils;
use Common\Utils\HttpUtils;

use \Org\Util\Exception;
use Common\Exception\BankSystemErrorCode;
/**
 * 外部反馈接口
 * Class OutbizNotifyService
 * @package Common\Service
 */
class OutbizNotifyService{


    public static $HXYL_API_URL;

    public function __construct()
    {
        self::$HXYL_API_URL = C('HXYL_API_PATH');
    }

    /**
     * 提币状态反馈接口
     * @param $param
     */
    public function withdrawCoinStateNotify($param)
    {
        try{

            $HXYL_API_URL = C('HXYL_API_PATH');
            $postUrl = $HXYL_API_URL.'/usdt/transfer-out-notify';

            $postData = array('tradeId'=>$param['tradeNo'],'exchangeTradeId'=>$param['txHash'],'platformCode'=>$param['platCode'],'userId'=>$param['userId'],'currencyName'=>$param['curencyCode'],'senderAddress'=>$param['senderAddr'],'receiverAddress'=>$param['receiverAddr'],'applyWithdrawNum'=>$param['applyWithdrawNum'],'serviceChargeNum'=>$param['serviceChargeNum'],'releaseNum'=>$param['releaseNum'],'status'=>$param['status']);
            $postData['sign'] = $this->signNotify($postData);

            return HttpUtils::post($postUrl, $postData,0);

        }catch (\Exception $e){

            ExloggerUtils::log('withdraw coin state notify error,trade_no：'.$param['tradeNo'].",status:".$param['status'].",msg:".$e->getMessage(),'error');
        }
    }

    /**
     * 充币状态反馈接口
     * @param $param
     */
    public function depositCoinStateNotify($param)
    {
        try{

            $HXYL_API_URL = C('HXYL_API_PATH');
            $postUrl = $HXYL_API_URL.'/usdt/transfer-in-notify';

            $postData = array('exchangeTradeId'=>$param['txHash'],'platformCode'=>$param['platCode'],'userId'=>$param['userId'],'currencyName'=>$param['curencyCode'],'senderAddress'=>$param['senderAddr'],'receiverAddress'=>$param['receiverAddr'],'userAmount'=>$param['userAmount'],'changeAmount'=>$param['changeAmount'],'status'=>$param['status']);
            $postData['sign'] = $this->signNotify($postData);

            return HttpUtils::post($postUrl, $postData,0);

        }catch (\Exception $e){

            ExloggerUtils::log('deposit coin state notify error,txHash：'.$param['txHash'].",status:".$param['status'].",msg:".$e->getMessage(),'error');
        }
    }

    /**
     * 余额变更反馈接口
     * @param $param
     */
    public function userBalanceChangeNotify($param)
    {
        try{

            $HXYL_API_URL = C('HXYL_API_PATH');
            $postUrl = $HXYL_API_URL.'/usdt/balance-change-notify';

            $postData = array('exchangeTradeId'=>$param['txHash'],'platformCode'=>$param['platCode'],'userId'=>$param['userId'],'currencyName'=>$param['cunrencyCode'],'senderAddress'=>$param['senderAddr'],'receiverAddress'=>$param['receiverAddr'],'userAmount'=>$param['userAmount'],'changeAmount'=>$param['amount']);
            $postData['sign'] = $this->signNotify($postData);

            return HttpUtils::post($postUrl, $postData,0);

        }catch (\Exception $e){

            ExloggerUtils::log('user balance change notify error,trade_no：'.$param['tradeNo'].",status:".$param['status'].",msg:".$e->getMessage(),'error');
        }
    }

    /**
     * 提币手续费变动通知 /usdt/withdraw-fee-notify
     * @param $fixedFee
     * @param $percentFee
     * @return string
     */
    public function withdrawFeeNotify($fixedFee,$percentFee)
    {
        $postData = array('fixedFee'=>$fixedFee,'percentFee'=>$percentFee,'timestamp'=>date('YmdHis'));
        $postData['sign'] = $this->signNotify($postData);
        $postUrl = self::$HXYL_API_URL.'/usdt/withdraw-fee-notify';
        try{
            return HttpUtils::post($postUrl, $postData,0);
        }catch (\Exception $e){
            ExloggerUtils::log('rechargeNotify:'.$e->getMessage(),'error');
        }
    }

    /**
     * 实时汇率变动通知/coin/exchange-rate-notify/usdt
     * @param string depositPrice
     * @param string withdrawPrice
     *
     */
    public function exchangeRateNotify($depositPrice,$withdrawPrice)
    {
        $postUrl = self::$HXYL_API_URL.'/usdt/exchange-rate-notify';
        try{
            if($depositPrice<6 || $withdrawPrice>8)
            {
                Exception::throwsErrorMsg(C('EXCHANGE_RATE_NOTIFY_VALUE_IS_ERROR'));
            }
            $postData = array('depositValue'=>$depositPrice,'withdrawValue'=>$withdrawPrice,'timestamp'=>date('YmdHis'));
            $postData['sign'] = $this->signNotify($postData);
            return HttpUtils::post($postUrl, $postData,0);
        }catch (\Exception $e){
            ExloggerUtils::log('exchangeRateNotify:'.$e->getMessage(),'error');
        }
    }

    /**
     * 充值通知审核 /usdt/recharge-notify
     * @param  array $parames(
     * tradeId,exchangeTradeId,platformCode,userId,currencyName,
     * receiverAddress,tradeAmount,unitPrice,tradeNum,
     * referenceNo,status
     * )
     * @return string
     */
    public function rechargeNotify($parames)
    {
        $parames['timestamp'] = date('YmdHis');
        $parames['sign'] = $this->signNotify($parames);
        $postUrl = self::$HXYL_API_URL.'/usdt/recharge-notify';
        try{
            return HttpUtils::post($postUrl, $parames,0);
        }catch (\Exception $e){
            ExloggerUtils::log('rechargeNotify:'.$e->getMessage(),'error');
        }
    }

    /** 提现反馈接口  /usdt/withdraw-notify
     * @param array $parames(
     * tradeId,exchangeTradeId,platformCode,userId,currencyName,
     * userName,bankName,branchBankName,bankCardNo,applyNum,unitPrice,
     * totalAmount,status
     * )
     * @return string
     */
    public function withdrawDepositStateNotify($parames)
    {
        try{
            $parames['timestamp'] = date('YmdHis');
            $parames['sign'] = $this->signNotify($parames);
            $postUrl = self::$HXYL_API_URL.'/usdt/withdraw-notify';
            return HttpUtils::post($postUrl, $parames,0);
        }catch (\Exception $e){
            ExloggerUtils::log('withdraw deposit state notify error,trade_no：'.json_encode($parames).",msg:".$e->getMessage(),'error');
        }
    }

    /**
     * @param $param
     * @param string $platCode 横兴娱乐
     * @return $param
     */
    public function signNotify($param,$platCode='hxyl')
    {
        if(empty($param)){
            return $param;
        }
        $token = C("PLAT_TOKEN.$platCode");
        if(empty($token)){
            return $param;
        }
        ksort($param);
        $str = '';
        foreach ($param as $key => $value) {
            if (! empty($value) && 'sign' != $key) {
                $str .= $key. '='. $value. '&';
            }
        }
        $str .= 'key='. $token;
        return strtoupper(md5($str));
    }
}
?>