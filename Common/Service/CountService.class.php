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

class CountService{


    /**
     * 更新提币记录
     * @param $data
     */
    public function update($data){

        $CountM =  M('TCount');

        if(!empty($data['id'])){

            $CountM->where('id='.$data['id'])->save($data);
        }else{

            ExloggerUtils::log('count service update error.msg：id can not be empty','error');
            ExceptionUtils::throwsEx(DepositCoinError::$INNER_DB_EXCEPTION);
        }

    }

    /**
     * 条件查询
     * @param $cparam
     */
    public function query_by_condition($param){

        $CountM =  M('TCount');

        $w = array();

        $currencyCode = $param['currencyCode'];
        if(!empty($currencyCode) ){
            $w['currency_code'] = array('eq',$currencyCode);
        }

        $w['is_deleted'] = array('eq',0);

        return $CountM->where($w)->order('created_date desc')->find();
    }

}
?>