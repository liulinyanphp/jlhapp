<?php
/**
 * 提币业务处理
 *
 */

namespace Common\Service;

use Common\Conf\BaseConfig;
use Common\Utils\ExceptionUtils;
use Common\Exception\WithdrawCoinError;
use Common\Utils\OmniUtils;
use Common\Utils\CommonUtils;
use Common\Utils\SeqNoUtils;


use Think\Exception;

class WithdrawCoinService{


    /**
     * 分页查询
     * @param string $field
     * @param array $where
     * @param string $pageNow
     * @param string $limitRows
     * @return mixed
     */
    public function getRecordPagerlist($field='*',$where=array(),$pageNow='1',$limitRows='10')
    {

        $WithdrawCoinRecordM =  M('TWithdrawCoinRecord');

        $data = $WithdrawCoinRecordM->field($field)->where($where)->page($pageNow .','. $limitRows)->select();

        return $data;
    }


    /**
     * 统计符合条件的记录总数
     * @param string $field
     * @param array $where
     * @param string $pageNow
     * @param string $limitRows
     * @return mixed
     */
    public function getRecordCount($where=array())
    {

        $WithdrawCoinRecordM =  M('TWithdrawCoinRecord');

        return $WithdrawCoinRecordM->where($where)->count();
    }

    /**
     * 申请提币
     * @param $content
     */
    public function withdraw($content)
    {

        //check info
        $this->check_info($content);

        //check avaliale coin
        $param = array();
        $param['uid']=$content['userId'];
        $param['currency_code'] =$content['currencyName'];
        $param['plat_code'] = $content['platformCode'];
        $param['applyWithdrawNum'] = $content['applyWithdrawNum'];
        $this->check_avaliable($param);

        $trans = M();
        $trans->startTrans();   // 开启事务

        //冻结用户账户对应额度
        $param['freeze_num'] = CommonUtils::covertNum($content['applyWithdrawNum'],4);
        $param['freeze_type'] = BaseConfig::FreezeTypeWithdrawCoin;
        $param['mark'] ="提币冻结";
        $param['trade_no']=$content['tradeId'];
        $param['last_modified_by']=$content['userId'];

        UserAddressBalanceService::user_coin_freeze($param);

        //create withdraw apply
        $this->insert($content);

        $trans->commit(); //提交事务
    }

    /**
     * 取消提币申请
     * @param $content
     */
    public function cancel_withdraw_apply($content){

        $trans = M();
        $trans->startTrans();   // 开启事务

        $platcode = $content['platformCode'];
        if(empty($platcode)){

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "platformCode");
        }

        $userAddr = $content['userId'];
        if(empty($userAddr)){

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "userId");
        }

        $currencyCode = $content['currencyName'];
        if(empty($currencyCode)){

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "currencyName");
        }

        $tradeNo = $content['tradeId'];
        if(empty($tradeNo)){

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "tradeId");
        }

        //查询提币记录
        $ret =   $this->query_by_condition($content);

        if(empty($ret)){

            ExceptionUtils::throwsEx(WithdrawCoinError::$WITHDRAW_ROCORD_NOT_FOUND_ERROR);
        }elseif($ret['status'] != BaseConfig::ApprovalStateSubmitted){

            ExceptionUtils::throwsEx(WithdrawCoinError::$WITHDRAW_APPLY_NOT_CANCEL_ERROR);
        }

        //取消提币申请
        $ret['status']=BaseConfig::ApprovalStateCancel;
        $this->update($ret);

        //解冻
        $param = array();
        $param['uid'] = $content['userId'];
        $param['plat_code'] = $content['platformCode'];
        $param['currency_code'] = $content['currencyName'];
        $param['unfreeze_num'] =  $ret['apply_num'];
        $param['trade_no']=$content['tradeId'];
        $param['mark']="用户撤销提币申请";
        $param['last_modified_by']=$content['userId'];

        UserAddressBalanceService::user_coin_unfreeze($param);

        $trans->commit(); //提交事务
    }

    /**
     * 更新提币记录
     * @param $data
     */
    public function update($data){

        $WithdrawCoinRecordM =  M('TWithdrawCoinRecord');

        if(!empty($data['id'])){

            $WithdrawCoinRecordM->where('id='.$data['id'])->save($data);
        }else{

            ExloggerUtils::log('withdraw coin service update error.msg：id can not be empty','error');
            ExceptionUtils::throwsEx(WithdrawCoinError::$INNER_DB_EXCEPTION);
        }
    }


    /**check可用数量
     * @param $param
     * @throws Exception
     */
    private static function  check_avaliable($param){

        $ret = UserAddressBalanceService::query_user_coin_available_num($param);
        if(null != $ret ){

            if(bccomp(strval($ret['available_num']), strval($param['applyWithdrawNum']), 4) == -1){
                //提币数量超出可用余额
                ExceptionUtils::throwsEx(WithdrawCoinError::$OUT_OF_BALANCE_ERROR);
            }
        }else{

            ExceptionUtils::throwsEx(WithdrawCoinError::$USER_BALANCE_ACCOUNT_ERROR);
        }
    }

    /**
     *    条件查询
     * @param $cparam
     */
    public function query_by_condition($param){
        $WithdrawCoinRecordM =  M('TWithdrawCoinRecord');

        $w = array();

        $platcode = $param['platformCode'];
        if(!empty($platcode) ){
            $w['plat_code'] = array('eq',$platcode);
        }

        $userAddr = $param['userId'];
        if(!empty($userAddr)){
            $w['uid'] = array('eq',$userAddr);

        }

        $currencyCode = $param['currencyName'];
        if(!empty($userAddr)){
            $w['currency_code'] = array('eq',$currencyCode);

        }

        $tradeNo = $param['tradeId'];
        if(!empty($tradeNo)){
            $w['trade_no'] = array('eq',$tradeNo);

        }

        $batchNo =  $param['batchNo'];
        if(!empty($batchNo)){
            $w['batch_no'] = array('eq',$batchNo);

        }

        return $WithdrawCoinRecordM->where($w)->order('created_date desc')->find();
    }


    /**
     * 新增用户申请提币记录
     * @param $data
     */
    private function insert($content)
    {

        $param =  array();
        $param['uid'] = $content['userId'];
        $param['plat_code'] = $content['platformCode'];
        $param['status'] = BaseConfig::ApprovalStateSubmitted;
        $param['user_addr'] = $content['senderAddress'];
        $param['trade_no'] = $content['tradeId'];
        $param['receive_addr'] = $content['receiverAddress'];
        $param['apply_num'] =CommonUtils::covertNum($content['applyWithdrawNum'],4);
        $param['real_num'] = CommonUtils::covertNum($content['releaseNum'],8);
        $param['fee_num'] = CommonUtils::covertNum($content['serviceChargeNum'],8);
        $param['currency_code'] = $content['currencyName'];
        $param['apply_time'] = date('Y-m-d H:i:s',time());

        $param['batch_no'] = SeqNoUtils::seq_no_gen('SH');
        $param['created_by'] = 'system';
        $param['last_modified_by'] = 'system';
        $param['created_date'] = date('Y-m-d H:i:s',time());
        $param['last_modified_date'] = date('Y-m-d H:i:s',time());


        M('TWithdrawCoinRecord')->data($param)->add();
    }

    /**
     * 数据校验
     * @param $content
     * @return array
     * @throws Exception
     */
    private function  check_info($content)
    {

        $platCode = $content['platformCode'];
        if (empty($platCode)) {

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "platformCode");
        }

        $userId = $content['userId'];
        if (empty($userId)) {

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "userId");
        }

        $currency_name= $content['currencyName'];
        if(empty($currency_name)){

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "currencyName");
        }

        $tradeId = $content['tradeId'];
        if (empty($tradeId)) {

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "tradeId");
        }

        $userAddr = $content['senderAddress'];
        if (empty($userAddr)) {

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "senderAddress");
        }

        $receiverAddr=$content['receiverAddress'];
        if(empty($receiverAddr)){

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "receiverAddress");
        }

        $applyNum = $content['applyWithdrawNum'];
        if(empty($applyNum)){

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "applyWithdrawNum");
        }

        $feeNum= $content['serviceChargeNum'];
        if(empty($feeNum)){

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "serviceChargeNum");
        }

        $realNum= $content['releaseNum'];
        if(empty($realNum)){

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM__EMPTY_ERROR, "releaseNum");
        }

        //check用户信息
        $UserAddressBalanceService = new UserAddressBalanceService();
        $queryParam['plat_code']=$platCode;
        $queryParam['uid']=$userId;
        $queryParam['user_addr']=$userAddr;
        $res1 = $UserAddressBalanceService::query_by_condition($queryParam);
        if (empty($res1)) {

            ExceptionUtils::throwsEx(WithdrawCoinError::$USER__INFO_ERROR);
        }

        //check币种信息
        $currencyM =  M('TCurrency');
        $w = array();
        $w['currency_code'] = array('eq',$currency_name);
        $currencyRet =  $currencyM->where($w)->select();
        if(empty($currencyRet)){

            ExceptionUtils::throwsEx(WithdrawCoinError::$CURRENCY_TYPE_ERROR);
        }

        //check用户地址
        $flag = OmniUtils::validateBitcoinAddress($userAddr);
        if(!$flag){

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$ADDR_NOT_VALID_ERROR, $userAddr);
        }

        //check提币地址
        $flagS = OmniUtils::validateBitcoinAddress($receiverAddr);
        if(!$flagS){

            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$ADDR_NOT_VALID_ERROR, $receiverAddr);
        }

        //每个平台的交易编号都只能申请一次，不允许重复申请
        $param = array();
        $param["platformCode"]=$content['platformCode'];
        $param["tradeId"]=$content['tradeId'];
        $res2= $this->query_by_condition($param);
        if (!empty($res2)) {

            ExceptionUtils::throwsEx(WithdrawCoinError::$TRADE_ID_VALID_ERROR);
        }

        //正数校验
        if(!CommonUtils::isPositiveNumber($applyNum)){
            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM_NOT_VALID_ERROR, "applyWithdrawNum");
        }

        //正数校验
        if(!CommonUtils::isPositiveNumber($feeNum)){
            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM_NOT_VALID_ERROR, "feeNum");
        }

        //正数校验
        if(!CommonUtils::isPositiveNumber($realNum)){
            ExceptionUtils::throwsExWithTips(WithdrawCoinError::$PARAM_NOT_VALID_ERROR, "releaseNum");
        }

        //check数量不得少于200个
        if(bccomp(strval($applyNum), '200.0000', 4) == -1){

            ExceptionUtils::throwsEx(WithdrawCoinError::$WITHDRAW_NUM_NOT_ALLOWED_ERROR);
        }

        //check数量关系  申请提币数量 = 实际到账数量+手续费数量
        $addNum = bcadd(strval($feeNum), strval($realNum), 8);
        if( bccomp(strval($addNum), strval($applyNum), 8) != 0){

            ExceptionUtils::throwsEx(WithdrawCoinError::$WITHDRAW_NUM_ERROR);
        }

        //check当前提币手续费
        $param = array();
        $param['plat_code']=$platCode;
        $param['currency_code']=$currency_name;
        $param['gas_type']='2';
        $param['amount']=$applyNum;
        $param['accuracy']='4';
        $fee = GasService::get_gas_info($param);
        if($fee == 0 || bccomp(strval($fee), strval($feeNum), 4) != 0){

            ExceptionUtils::throwsEx(WithdrawCoinError::$WITHDRAW_FEE_ERROR);
        }

    }


}
?>