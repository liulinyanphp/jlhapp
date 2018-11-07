<?php
/**
 * addby : lly
 * date : 2018-04-24 00:28
 * used : 用户充值购买的服务
 * desc : 供api,和admin后端调用
 */
namespace Common\Service;
use Think\Model;
use Common\Service\PurchaseStockService;
use Common\Service\RateService;
use Common\Service\UserAddressBalanceService;
use Common\Service\AddressService;
use \Org\Util\Result;
use \Org\Util\Exception;
use Common\Utils\ExloggerUtils;
use \Common\Conf\BaseConfig;

class PurchaseCoinService
{
    //定义我们表中的所需的字段
    protected $_map = array(
         'tradeId' =>'trade_no',
         'platformCode' =>'plat_code',
         'userId'=>'uid',
         'receiverAddress'=>'user_addr',
         'tradeStatus'=>'status',
         'referenceNo'=>'reference_no',
         'tradeAmount'=>'amount',
         'unitPrice'=>'curr_price',
         'tradeNum'=>'num',
         'currencyName'=>'currency_code',
         'bankCardNo' => 'bank_card_no',
         'bankName' => 'bank_name',
         'bankCardHolder' => 'bank_card_holder',
         'bankTransNo' => 'bank_trans_no',
         'payTime'=>'payment_time',
         'bankTransNo'=>'bank_trans_no',
         'operationDesc'=>'operation_desc'
    );
    public function purchase_coin($data)
    {
        try{
            //先检查这个订单是否存在,存在则不能再推过来啦
            $order_count = $this->_getPurchaseInfo($data);
            if($order_count>0)
            {
                Exception::throwsErrorMsg(C('PURCHASECOIN_INFO_IS_EXIT'));
            }
            //后面如果$data中的code传的是名称的话,再进行转 $data['code'] = change($code);
            //查询库存
            $param['currency_code'] = $data['currencyName'];
            $PurchaseStockService = new PurchaseStockService();
            $stock_data = $PurchaseStockService->get_remain_num($param);
            if( $stock_data['total_num'] < $data['tradeNum'] )
            {
                Exception::throwsErrorMsg(C('PURCHASECOIN_NOT_ENOUGH'));
            }
            //查询价格是否正确，获取当前的汇率
            $RateM = new RateService();
            $resinfo = $RateM->flush_rate_info($data);
            $currency_code = strtoupper($data['currencyName']);
            $res = $resinfo[$currency_code][0];
            if($data['unitPrice'] != $res['deposit_price']){
                Exception::throwsErrorMsg(C('PURCHASECOIN_RATE_IS_ERROR'));
            }
            //看看数量对不对
            if(bcdiv($data['tradeAmount'],$res['deposit_price'],8) != $data['tradeNum'])
            {
                Exception::throwsErrorMsg(C('PURCHASECOIN_NUM_IS_ERROR'));
            }
            $PurchaseCoinM = M('TPurchaseCoinRecord');
            //看看该用户是否有为完成的订单（等待付款的订单）
            $w['uid']= $data['userId'];
            $w['status']= BaseConfig::PurchaseCoinRecordSubmitted;
            $w['plat_code'] = $data['platformCode'];
            $w['currency_code'] = $data['currencyName'];
            //记录购买信息
            $data = $this->_key_map($data);
            $check_un_confirm = $PurchaseCoinM->where($w)->find();
            if(!empty($check_un_confirm)){
                Exception::throwsErrorMsg(C('PURCHASECOIN_HAVE_UNFINISH_ORDER'));
            }
            $data['status'] = BaseConfig::PurchaseCoinRecordSubmitted;
            $data['created_by'] = 'sys_api';
            $data['last_modified_by'] = 'sys_api';
            $PurchaseCoinM->data($data)->add();
        }catch(\Exception $e) {
            return Result::apiResultFail($e);
        }
        return Result::apiResultSuccess();
    }

    /**
     * @param $data
     * @return array
     */
    private function _key_map($data)
    {
        $key_arr = $this->_map;
        $return = array();
        foreach ($data as $key => $value) {
            if(isset($key_arr[$key])){
                $return[$key_arr[$key]] = $value;
            }
        }
        return $return;
    }


    /*后台工作人员,定时脚本方法*/
    public function transfer_coin($postdata=array(),$is_auto=0,$minute=30)
    {
        $PurchaseCoinM = M('TPurchaseCoinRecord');
        if($is_auto){
            $w['TIMESTAMPDIFF(MINUTE,created_date,NOW())'] = array('gt',$minute);
            $w['status'] = array('eq','WAIT_TO_PAY');       
            $data = array(
                    'status'=>C('BUY_COIN_DEFAULT_STATUS'),
                    'operator_name'=>'sys_api',
                    'operation_desc'=>'订单超过30分钟未做付款确认,系统自动关闭'
            );
            $PurchaseCoinM->where($w)->setField($data);
            ExloggerUtils::log('接口接受的数据：'.$PurchaseCoinM->_sql(),'info');
            return Result::innerResultSuccess();
        }

        //在整个划账中要确保订单的有效性和重复点击审核划账的情况
        $tradeNo = $postdata['trade_no'];
        $check_w['trade_no'] = $tradeNo;
        $purchaseCoinInfo = $this->get_tradInfo($check_w);
        try{
            if(empty($purchaseCoinInfo))
            {
                Exception::throwsErrorMsg(C('PURCHASECOIN_NOT_FIND_ORDER'));
            }
            if($purchaseCoinInfo['status'] != BaseConfig::PurchaseCoinRecordSubmitted)
            {
                Exception::throwsErrorMsg(C('PURCHASECOIN_STATUS_IS_ERROR'));
            }
            //最后核对一次,用户提交的或者传递过来的银行卡信息和收到的金额
            if($purchaseCoinInfo['amount'] != $postdata['amount'])
            {
                Exception::throwsErrorMsg(C('PURCHASECOIN_AMOUNT_IS_ERROR'));
            }
            //如果不是自动脚本的话,那就是人为的操作或者是接口请求
            $update_w['trade_no'] = $postdata['trade_no'];
            //定义事务所需的参数
            $param = array(
                'uid'=>$purchaseCoinInfo['uid'],
                'num'=>$purchaseCoinInfo['num'],
                'plat_code'=>$purchaseCoinInfo['plat_code'],
                'trade_no'=>$postdata['trade_no'],
                'currency_code' => $purchaseCoinInfo['currency_code'],
                'mark'=>'用户付款，后台人员点击审核之后,自动划帐'
            );
            $userAddressBanlance = new UserAddressBalanceService();
            $result = $userAddressBanlance->coin_to_user($param);
        }catch(\Exception $e)
        {
            ExloggerUtils::log($e->getMessage(),'error');
            return Result::innerResultFail($e);
        }
        return Result::innerResultSuccess($result);
    }


    /*
     * 获取充值订单接口
    */
    public function get_tradInfo($where=array())
    {
        if(empty($where)){
            return '';
        }
        return M('TPurchaseCoinRecord')->where($where)->find();
    }

    /*
     * 平台购买币处理
     */
    public function insert_into_tb($postdata)
    {
        try{
            if(!isset($postdata['platformCode']))
            {
                Exception::throwsErrorMsg(C('PLAT_CODE_IS_NULL'));
            }
            if(!isset($postdata['currencyName'])) {
                Exception::throwsErrorMsg(C('CURRENCY_CODE_IS_NULL'));
            }
            $addressM = new AddressService();
            //获取用信息是否正确
            $user_count = $addressM->check_address_info($postdata['platformCode'],$postdata['currencyName'],$postdata['userId'],$postdata['userAdress']);
            if($user_count<1)
            {
                Exception::throwsErrorMsg(C('BUYER_IS_NOT_FIND'));
            }
            //平台购买币,拿取我们后台设置的最高充值购买率
            $RateM = new RateService();
            $rateInfo = $RateM->get_purchase_rate($postdata['platformCode'],$postdata['currencyName']);
            if(empty($rateInfo)) {
                Exception::throwsErrorMsg(C('PLAT_RATE_IS_NULL'));
            }
            $rate_value = $rateInfo['deposit_price'];
            $data['plat_code'] = $postdata['platformCode'];
            $data['uid'] = (int)$postdata['userId'];
            $data['user_addr'] = $postdata['userAdress'];
            $data['status'] = '已处理';
            $data['curr_price'] = $rate_value;
            $data['num'] = bcdiv($postdata['buyNum'],1,8);
            $data['total_amount'] = bcmul($postdata['buyNum'],$rate_value,2);
            $data['currency_code'] = $postdata['currencyName'];
            $data['trade_no'] = $postdata['tradeId'];
            $data['created_by'] = 'sys_api';
            $data['last_modified_by'] = 'sys_api';
            //定义事务所需的参数
            $param = array(
                'uid'=>$data['uid'],
                'num'=>$data['num'],
                'plat_code'=>$data['plat_code'],
                'trade_no'=>$postdata['tradeId'],
                'currency_code' => $data['currency_code'],
                'mark'=>"平台购买币,自动划转".$data['num'].'到对应的用户地址上',
            );
            $userAddressBanlance = new UserAddressBalanceService();
            $res = $userAddressBanlance->coin_to_user($param);
            ExloggerUtils::log('平台购买币的处理结果是'.json_encode($res),'info');
            if($res['status']=='0' || $res['result']=='0') {
                M('TPlatPurchaseCoinRecord')->data($data)->add();
            }else{
                $result = array($res['result'],$res['res_info']);
                Exception::throwsErrorMsg($result);
            }
        }catch(\Exception $e) {
            ExloggerUtils::log('平台购买币的处理结果错误'.json_encode($res),'error');
           return Result::apiResultFail($e);
        }
        return Result::apiResultSuccess();
    }


    //减少
    public function duct_into_tb($postdata)
    {
        try
        {
            if (!isset($postdata['platformCode'])) {
                Exception::throwsErrorMsg(C('PLAT_CODE_IS_NULL'));
            }
            if (!isset($postdata['currencyName'])) {
                Exception::throwsErrorMsg(C('CURRENCY_CODE_IS_NULL'));
            }

            $addressM = new AddressService();
            //获取用信息是否正确
            $user_count = $addressM->check_address_info($postdata['platformCode'], $postdata['currencyName'], $postdata['userId'], $postdata['userAdress']);
            if ($user_count < 1) {
                Exception::throwsErrorMsg(C('BUYER_IS_NOT_FIND'));
            }
            //平台购买币,拿取我们后台设置的最高充值购买率
            $RateM = new RateService();
            $rateInfo = $RateM->get_purchase_rate($postdata['platformCode'], $postdata['currencyName']);
            if (empty($rateInfo)) {
                Exception::throwsErrorMsg(C('PLAT_RATE_IS_NULL'));
            }
            //收币的汇率
            $rate_value = $rateInfo['withdraw_price'];
            $data['plat_code'] = $postdata['platformCode'];
            $data['uid'] = (int)$postdata['userId'];
            $data['user_addr'] = $postdata['userAdress'];
            $data['status'] = '待回收';
            $data['num'] = bcdiv($postdata['deductNum'], 1, 8);
            $data['currency_code'] = $postdata['currencyName'];
            $data['trade_no'] = $postdata['tradeId'];
            $data['created_by'] = 'sys_api';
            $data['last_modified_by'] = 'sys_api';
            $param = array(
                'uid' => $data['uid'],
                'num' => $data['num'],
                'plat_code' => $data['plat_code'],
                'currency_code' => $data['currency_code'],
                'address' => $data['user_addr'],
                'trade_no' => $postdata['tradeId'],
                'mark' => "平台请求代扣用户" . $data['num'] . "个币"
            );
            $userAddressBanlance = new UserAddressBalanceService();
            $res = $userAddressBanlance->duct_coin_from_user($param);
            ExloggerUtils::log('平台带扣币的处理结果是'.json_encode($res),'info');
            if ($res['res_info'] == 'ok') {
                M('TPlatCollectCoinRecord')->data($data)->add();
            } else {
                $result = array($res['status'], $res['message']);
                Exception::throwsErrorMsg($result);
            }
        }catch(\Exception $e) {
            ExloggerUtils::log($e->getMessage(),'error');
            return Result::apiResultFail($e);
        }
        return Result::apiResultSuccess();
    }

    /**
     * desc  平台获取订单信息接口
     * @param array
     * @return array res
     */
    public function get_purchaseInfo($postdata)
    {
        try{
            if(isset($postdata['platcode']) && isset($postdata['tradeId']))
            {
                $field = 'trade_no as tradeId,status';
                $w['plat_code'] = $postdata['platcode'];
                $w['trade_no'] = $postdata['tradeId'];
                $data = M('TPurchaseCoinRecord')->field($field)->where($w)->find();
                if(empty($data))
                {
                    Exception::throwsErrorMsg(C('PURCHASECOIN_IS_NOT_EXIT'));
                }
            }else{
                Exception::throwsErrorMsg(C('PURCHASECOIN_PARAME_IS_ERROR'));
            }
        }catch(\Exception $e) {
            ExloggerUtils::log($e->getMessage(),'error');
            return Result::apiResultFail($e);
        }
        return Result::apiResultSuccess($data);
    }

    /**
     * desc 添加之前的检查
     */
    private function _getPurchaseInfo($postdata)
    {
        $field = 'trade_no,status';
        $w['plat_code'] = $postdata['platcode'];
        $w['trade_no'] = $postdata['tradeId'];
        return M('TPurchaseCoinRecord')->where($w)->count();
    }

    /**
     * desc 平台获取平台购币订单信息接口
     * @param array
     * @return array res
     */
    public function get_PlatformTransferIn($postdata)
    {
        try {
            if (isset($postdata['platcode']) && isset($postdata['tradeId'])){
                $field = 'trade_no as tradeId,uid as userId,user_addr as userAddress,total_amount as amount,
                created_date as time,status,currency_code as currencyName';
                $w['plat_code'] = array('eq', $postdata[platcode]);
                $w['trade_no'] = array('eq', $postdata['tradeId']);
                $data = M('TPlatPurchaseCoinRecord')->field($field)->where($w)->find();
                if (empty($data)) {
                    Exception::throwsErrorMsg(C('PURCHASECOIN_IS_NOT_EXIT'));
                }
                $data['status'] =  $data['status'] == '已处理' ? 'success' : 'fail';
            }else{
                Exception::throwsErrorMsg(C('PURCHASECOIN_PARAME_IS_ERROR'));
            }
        }catch(\Exception $e) {
            ExloggerUtils::log($e->getMessage(),'error');
            return Result::apiResultFail($e);
        }
        return Result::apiResultSuccess($data);
    }



    /**
     * desc 平台获取平台代扣币订单信息接口
     * @param array
     * @return array res
     */
    public function get_PlatformTransferOut($postdata)
    {
        try {
            if (isset($postdata['platcode']) && isset($postdata['tradeId'])) {
                $field = 'trade_no as tradeId,uid as userId,user_addr as userAddress,num as amount,
                created_date as time,status,currency_code as currencyName';
                $w['plat_code'] = array('eq', $postdata[platcode]);
                $w['trade_no'] = array('eq', $postdata['tradeId']);
                $data = M('TPlatCollectCoinRecord')->field($field)->where($w)->find();
                if (empty($data)) {
                    Exception::throwsErrorMsg(C('PURCHASECOIN_IS_NOT_EXIT'));
                }
            } else {
                Exception::throwsErrorMsg(C('PURCHASECOIN_PARAME_IS_ERROR'));
            }
        } catch (\Exception $e) {
            ExloggerUtils::log($e->getMessage(), 'error');
            return Result::apiResultFail($e);
        }
        return Result::apiResultSuccess($data);
    }
}