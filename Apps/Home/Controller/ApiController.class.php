<?php
/**
 * addby : lly
 * date : 2018-03-19 9:58
 * used : 对外api接口类
 **/
namespace Home\Controller;
use Common\Conf\BaseConfig;
use Common\Service\OmniService;
//引入地址服务类
use Common\Service\AddressService;
//引入银行服务类
use Common\Service\BankCardService;
//引入用户购买充值服务类
use Common\Service\PurchaseCoinService;
use Common\Service\WithdrawCoinService;
use Common\Utils\ResultHandleUtils;
//公用的日志写入类
use Common\Utils\ExloggerUtils;
use Common\Service\PresentRecordService;
use Common\Utils\ExceptionUtils;
use Common\Exception\WithdrawCoinError;
use Common\Exception\DepositCoinError;
use Common\Service\DepositCoinService;

class ApiController extends ApiBaseController {
//class ApiController extends Controller{


    private $_domain_list = array('usdt.com');
    private $_allow_ips = array('127.0.0.1');

    //已经公开的接口列表
    private $_actions = array(
        'GetAddress','GetBankCard','PurchaseCoin','WithdrawCoin','CancelWithdrawCoin',
        'PlatformBuyCoin','DeductCoin','WithdrawCashApply','GetPurchaseCoin',
        'QueryPlatformTransferIn','QueryPlatformTransferOut','QueryUserTransferIn','QueryUserTransferOut',
        'QueryUserRecharge','QueryUserWithdraw'
    );

    public static $bitcoin;

    public $api_result = array(
        'status'=>0,
        'message'=>'',
        'data'=>''
    );

    public function __construct()
    {
        parent::__construct();
        self::$bitcoin = OmniService::getInstance();
    }

    /**
     * 唯一的一个暴露出来的api接口
     */
    public function handleAction()
    {
        //$allpost = $global_postbody;
        $postdata = $this->api_content();
        $act_name = isset($postdata['act'])? $postdata['act']:'';
        if(in_array($act_name,$this->_actions))
        {
            $act_name = '_cszb_'.$act_name;
            $this->$act_name($postdata);
        }else{
            $res = array('status'=>-1,'message'=>'接口不存在');
            echo json_encode($res);
        }
    }

    /*
     * 平台购买币接口
     * PlatformBuyCoin
     */
    private function _cszb_PlatformBuyCoin($postdata)
    {
        ExloggerUtils::log('平台购买币接口接受的数据：'.json_encode($postdata,JSON_UNESCAPED_UNICODE),'info');
        $purchaseService = new PurchaseCoinService();
        $result = $purchaseService->insert_into_tb($postdata);
        ExloggerUtils::log('平台购买币接口返回的数据：'.json_encode($result,JSON_UNESCAPED_UNICODE),'info');
        $this->ajaxReturn($result);
    }

    /*
     * 平台出售币  我们代扣币接口
     */
    private function _cszb_DeductCoin($postdata)
    {
        ExloggerUtils::log('平台扣币接口接受的数据：'.json_encode($postdata,JSON_UNESCAPED_UNICODE),'info');
        $purchaseService = new PurchaseCoinService();
        $result = $purchaseService->duct_into_tb($postdata);
        ExloggerUtils::log('平台扣币接口返回的数据：'.json_encode($result,JSON_UNESCAPED_UNICODE),'info');
        $this->ajaxReturn($result);
    }

    /**
     * 平台获取订单信息
     */
    private function _cszb_QueryUserRecharge($postdata)
    {
        ExloggerUtils::log('接口接受的数据：'.json_encode($postdata,JSON_UNESCAPED_UNICODE),'info');
        $purchaseService = new PurchaseCoinService();
        $result = $purchaseService->get_purchaseInfo($postdata);
        ExloggerUtils::log('接口返回的数据：'.json_encode($result,JSON_UNESCAPED_UNICODE),'info');
        $this->ajaxReturn($result);
    }

    /**
     * 平台获取购币订单信息
     */
    private function _cszb_QueryPlatformTransferIn($postdata)
    {
        ExloggerUtils::log('接口接受的数据：'.json_encode($postdata,JSON_UNESCAPED_UNICODE),'info');
        $purchaseService = new PurchaseCoinService();
        $result = $purchaseService->get_PlatformTransferIn($postdata);
        ExloggerUtils::log('接口返回的数据：'.json_encode($result,JSON_UNESCAPED_UNICODE),'info');
        $this->ajaxReturn($result);
    }

    /**
     * 平台获取扣币订单信息
     */
    private function _cszb_QueryPlatformTransferOut($postdata)
    {
        ExloggerUtils::log('接口接受的数据：'.json_encode($postdata,JSON_UNESCAPED_UNICODE),'info');
        $purchaseService = new PurchaseCoinService();
        $result = $purchaseService->get_PlatformTransferOut($postdata);
        ExloggerUtils::log('接口返回的数据：'.json_encode($result,JSON_UNESCAPED_UNICODE),'info');
        $this->ajaxReturn($result);
    }

    /**
     * 平台查询充币信息接口
     */
    private function _cszb_QueryUserTransferIn($postdata)
    {
        try{

            if(empty($postdata['hashAddress'])){
                ExceptionUtils::throwsExWithTips(DepositCoinError::$PARAM__EMPTY_ERROR, "hashAddress");

            }

            if( empty($postdata['platformCode'])){
                ExceptionUtils::throwsExWithTips(DepositCoinError::$PARAM__EMPTY_ERROR, "platformCode");

            }

            $DepositCoinS= new DepositCoinService();

            $param = array();
            $param['platformCode'] = $postdata['platformCode'];
            $param['txHash'] = $postdata['hashAddress'];

            $res = $DepositCoinS-> query_by_condition($param);

            $ret = array();
            if(!empty($res)){

                $ret['hashAddress'] =$res['tx_hash'];
                $ret['senderAddress'] =$res['sending_addr'];
                $ret['receiverAddress'] =$res['user_addr'];
                $ret['amount'] =$res['amount'];
                $ret['userId'] =$res['uid'];
                $ret['currencyName'] =$res['currency_code'];
                $ret['time'] =$res['created_date'];


                if($res['status'] == BaseConfig::DepositStateCreated ){

                    $ret['status'] ='pending';
                }

                if($res['status'] == BaseConfig::DepositStateHandled || $res['status'] == BaseConfig::DepositStateNotified){

                    $ret['status'] ='success';
                }

                if($res['status'] == BaseConfig::DepositStateUnvalid){

                    $ret['status'] ='fail';
                }

            }


            $res = ResultHandleUtils::makeOutbizSucRet($ret);
            ExloggerUtils::log('query deposit record successful. res：'.json_encode($ret),'info');
            $this->ajaxReturn($res);
        }catch(\Exception $e) {

            ExloggerUtils::log('query deposit record error. tno：,errorMsg:'.$e->getMessage(),'error');
            $this->ajaxReturn(ResultHandleUtils::makeOutbizExRet($e));
        }
    }

    /**
     * 平台查询提币信息接口
     */
    private function _cszb_QueryUserTransferOut($postdata)
    {
        try{

            if(empty($postdata['tradeId'])){
                ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "tradeId");

            }

            if( empty($postdata['platformCode'])){
                ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "platformCode");

            }

            $WithdrawCoinService = new WithdrawCoinService();

            $param = array();
            $param['platformCode'] = $postdata['platformCode'];
            $param['tradeId'] = $postdata['tradeId'];

            $res = $WithdrawCoinService-> query_by_condition($param);

            $ret = array();
            if(!empty($res)){

                $ret['hashAddress'] ='';
                $ret['senderAddress'] =$res['user_addr'];
                $ret['receiverAddress'] =$res['receive_addr'];
                $ret['amount'] =$res['apply_num'];
                $ret['userId'] =$res['uid'];
                $ret['currencyName'] =$res['currency_code'];
                $ret['time'] =$res['created_date'];


                if($res['status'] == BaseConfig::ApprovalStateSubmitted || $res['status'] ==ApprovalStateReviewd || $res['status'] == BaseConfig::ApprovalStateHandled){

                    $ret['status'] ='pending';
                }

                if($res['status'] == BaseConfig::ApprovalStateFinished){

                    $ret['status'] ='success';
                }

                if($res['status'] == BaseConfig::ApprovalStateFailed){

                    $ret['status'] ='fail';
                }

                if($res['status'] == BaseConfig::ApprovalStateCancel){

                    $ret['status'] ='canceled';
                }

            }


            $res = ResultHandleUtils::makeOutbizSucRet($ret);
            ExloggerUtils::log('query withdraw record successful. res：'.json_encode($ret),'info');
            $this->ajaxReturn($res);
        }catch(\Exception $e) {

            ExloggerUtils::log('query withdraw record error. tno：,errorMsg:'.$e->getMessage(),'error');
            $this->ajaxReturn(ResultHandleUtils::makeOutbizExRet($e));
        }
    }

    /*
     * 内部为外部提供的私有的获取地址的api接口
    */
    private function _cszb_GetAddress($postdata)
    {
        ExloggerUtils::log('接口接受的数据：'.json_encode($postdata,JSON_UNESCAPED_UNICODE),'info');
        $AddressService = new AddressService();
        $result = $AddressService->_get_address($postdata);
        ExloggerUtils::log('接口返回的数据：'.json_encode($result,JSON_UNESCAPED_UNICODE),'info');
        $this->ajaxReturn($result);
    }


    /**********   第三方平台请求过来的接口  按照_cszb_   + 参数act 写自己的逻辑返回即可  **********/

    /*
     * 内部为外部提供的私有的获取银行卡的api接口
    */
    private function _cszb_GetBankCard($postdata)
    {
        ExloggerUtils::log('接口接受的数据：'.json_encode($postdata,JSON_UNESCAPED_UNICODE),'info');
        $BankCarService = new BankCardService();
        $result = $BankCarService->get_bank_car($postdata);
        ExloggerUtils::log('接口返回的数据：'.json_encode($result,JSON_UNESCAPED_UNICODE),'info');
        $this->ajaxReturn($result);
    }

    private function _cszb_PurchaseCoin($postdata)
    {
        ExloggerUtils::log('接口接受的数据：'.json_encode($postdata,JSON_UNESCAPED_UNICODE),'info');
        $purchaseService = new PurchaseCoinService();
        unset($postdata['act']);
        $result = $purchaseService->purchase_coin($postdata);
        ExloggerUtils::log('接口返回的数据：'.json_encode($result,JSON_UNESCAPED_UNICODE),'info');
        $this->ajaxReturn($result);
    }

    /*
     * 内部为外部提供的私有的买币接口
    */
    private function _cszb_buy_coin($postdata)
    {
        //coming soon to develop buy_coin api
    }

    /**
     * 内部为外部提供的私有的充币接口
     */
    private function _cszb_recharge_coin($postdata)
    {
        //coming soon to develop recharge_coin api
    }

    /**
     * 内部为外币提供的私有的提币接口
     * @param $postdata
     * @return mixed
     */
    private function _cszb_WithdrawCoin($postdata)

    {
        try{
            $WithdrawCoinService = new WithdrawCoinService();
            $WithdrawCoinService-> withdraw($postdata);

            $res = ResultHandleUtils::makeOutbizSucRet();

            ExloggerUtils::log('withdraw coin apply successful. tno：'.$postdata['tradeId'],'info');
            $this->ajaxReturn($res);
        }catch(\Exception $e) {

            ExloggerUtils::log('withdraw coin apply failed. tno：'.$postdata['tradeId'].',errorMsg:'.$e->getMessage(),'info');
            $this->ajaxReturn(ResultHandleUtils::makeOutbizExRet($e));
        }
    }

    /**
     * 取消提币申请（只有提交状态的可以取消）
     * @param $postdata
     */
    private function _cszb_CancelWithdrawCoin($postdata)
    {
        try{

            $WithdrawCoinService = new WithdrawCoinService();
            $WithdrawCoinService-> cancel_withdraw_apply($postdata);

            $res = ResultHandleUtils::makeOutbizSucRet();

            ExloggerUtils::log('cancel withdraw coin apply successful. tno：'.$postdata['tradeId'],'info');

            $this->ajaxReturn($res);
        }catch(\Exception $e) {

            ExloggerUtils::log('cancel withdraw coin apply failed. tno：'.$postdata['tradeId'].',errorMsg:'.$e->getMessage(),'info');
            $this->ajaxReturn(ResultHandleUtils::makeOutbizExRet($e));
        }
    }


    /**
     * 内部为外部提供的私有的提现接口
     */
    private function _cszb_extraction_cash($postdata)
    {
        //coming soon to extraction_cash api
    }

    /**
     * 内部为外部提供的私有的同步币的接口 (用户在平台发生币币兑换用)
     */
    private function _cszb_sync_coin($postdata)
    {
        //coming soon to sync_coin api
    }

    /**
     * 内部为外部提供的私有的币币交易接口
     */
    private function _cszb_createrawtransactionaaa($postdata)
    {
        //coming soon to createrawtransaction api
    }



    /**提现接口
     * @param $postdata
     */
    private function _cszb_WithdrawCashApply($postdata)
    {
        $WithdrawCashApplyService = new PresentRecordService();
        $result = $WithdrawCashApplyService->present_record_create($postdata);
        $this->ajaxReturn($result);
    }

    /**提现查询接口
     * @param $postdata
     */
    private function _cszb_QueryUserWithdraw($postdata)
    {
        $WithdrawCashApplyService = new PresentRecordService();
        $result = $WithdrawCashApplyService->get_withdraw_deposite($postdata);
        $this->ajaxReturn($result);
    }

}