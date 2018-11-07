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
use Common\Utils\ResultHandleUtils;
use Common\Utils\SeqNoUtils;


use Think\Exception;

class WithdrawCoinTransactionService{


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

        $TransactioM =  M('TWithdrawCoinTransaction');

        return $TransactioM->where($where)->count();
    }


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

        $TransactioM =  M('TWithdrawCoinTransaction');

        $data = $TransactioM->field($field)->where($where)->page($pageNow .','. $limitRows)->select();

        return $data;
    }


    /**
     * 更新交易信息
     * @param $data
     */
    public function update($data){

        $TransactioM =  M('TWithdrawCoinTransaction');

        if(!empty($data['id'])){

            $TransactioM->where('id='.$data['id'])->save($data);
        }else{
            ExloggerUtils::log('withdraw coin transaction service update error.msg：id can not be empty','error');
            ExceptionUtils::throwsEx(WithdrawCoinError::$INNER_DB_EXCEPTION);
        }


    }



    /**
     *   条件查询
     * @param $cparam
     */
    public function query_by_condition($param){
        $TransactioM =  M('TWithdrawCoinTransaction');

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

        return $TransactioM->where($w)->order('created_date desc')->find();
    }


    /**
     * 新增交易待发送记录
     * @param $record
     */
    public function insert($record)
    {

        $param =  array();
        $param['batch_no'] = $record['batch_no'];
        $param['trade_no'] = $record['trade_no'];
        $param['uid'] = $record['uid'];
        $param['plat_code'] = $record['plat_code'];
        $param['status'] = BaseConfig::TransactionStateCreated;
        $param['user_addr'] = $record['user_addr'];
        $param['receive_addr'] = $record['receive_addr'];
        $param['apply_num'] =CommonUtils::covertNum($record['apply_num'],4);
        $param['real_num'] = CommonUtils::covertNum($record['real_num'],8);
        $param['fee_num'] = CommonUtils::covertNum($record['fee_num'],8);
        $param['currency_code'] = $record['currency_code'];

        $param['created_by'] = 'REVIEW_JOB';
        $param['last_modified_by'] = 'REVIEW_JOB';
        $param['created_date'] = date('Y-m-d H:i:s',time());
        $param['last_modified_date'] = date('Y-m-d H:i:s',time());


        M('TWithdrawCoinTransaction')->data($param)->add();
    }





}
?>