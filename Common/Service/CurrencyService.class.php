<?php
/**
 * 币种业务
 *
 */

namespace Common\Service;




class CurrencyService{


    /**
     * 条件查询
     * @ param array $param
     */
    public function query_by_condition($param){

        $CurrencyM =  M('TCurrency');

        $w = array();

        $currencyCode= $param['currencyCode'];
        if(!empty($platcode) ){
            $w['currency_code'] = array('eq',$currencyCode);
        }

        $propertyId= $param['propertyId'];
        if(!empty($platcode) ){
            $w['property_id'] = array('eq',$propertyId);
        }

        return $CurrencyM->where($w)->order('created_date desc')->find();
    }

    public function getPropertyId($currency_code)
    {
        if($currency_code=='')
        {
            return 31;
        }
        $CurrencyM =  M('TCurrency');
        $w['currency_code'] = array('eq',$currency_code);
        return $CurrencyM->where($w)->getField('property_id');
    }

}
?>