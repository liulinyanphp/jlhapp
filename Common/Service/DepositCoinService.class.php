<?php
/**
 * 提币业务处理
 *
 */

namespace Common\Service;

use Common\Utils\ExceptionUtils;
use Common\Exception\DepositCoinError;
use Common\Utils\ExloggerUtils;



use Think\Exception;

class DepositCoinService{

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

        $DepositCoinRecordM =  M('TDepositCoinRecord');

        $data = $DepositCoinRecordM->field($field)->where($where)->page($pageNow .','. $limitRows)->select();

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

        $DepositCoinRecordM =  M('TDepositCoinRecord');

        return $DepositCoinRecordM->where($where)->count();
    }

    /**
     * 更新提币记录
     * @param $data
     */
    public function update($data){

        $DepositCoinRecordM =  M('TDepositCoinRecord');

        if(!empty($data['id'])){

            $DepositCoinRecordM->where('id='.$data['id'])->save($data);
        }else{

            ExloggerUtils::log('deposit coin service update error.msg：id can not be empty','error');
            ExceptionUtils::throwsEx(DepositCoinError::$INNER_DB_EXCEPTION);
        }

    }

    /**
     *    条件查询
     * @param $cparam
     */
    public function query_by_condition($param){
        $DepositCoinRecordM =  M('TDepositCoinRecord');

        $w = array();

        $txHash = $param['txHash'];
        if(!empty($txHash) ){
            $w['tx_hash'] = array('eq',$txHash);
        }

        $platCode = $param['platformCode'];
        if(!empty($platCode) ){
            $w['plat_code'] = array('eq',$platCode);
        }

        return $DepositCoinRecordM->where($w)->order('created_date desc')->find();
    }


    /**
     * 新增用户充币记录
     * @param $data
     */
    public function insert($content)
    {

        $param =  array();
        $param['uid'] = $content['userId'];
        $param['plat_code'] = $content['platformCode'];
        $param['sending_addr'] = $content['sendingAddress'];
        $param['user_addr'] = $content['referenceAddress'];
        $param['status'] =  $content['status'];
        $param['amount'] = $content['amount'];
        $param['currency_code'] = $content['currencyCode'];
        $param['tx_hash'] = $content['txHash'];
        $param['block_hash'] = $content['blockHash'];
        $param['isolated_block_hash'] = '';
        $param['confirmations'] = $content['confirmations'];
        $param['created_by'] = 'system';
        $param['last_modified_by'] = 'system';
        $param['created_date'] = date('Y-m-d H:i:s',time());
        $param['last_modified_date'] = date('Y-m-d H:i:s',time());


        M('TDepositCoinRecord')->data($param)->add();

    }



}
?>