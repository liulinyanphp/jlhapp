<?php
/**
 * addby : lly
 * date : 2018-04-25 10:25
 * used : 汇率的服务
 */
namespace Common\Service;
use Think\Model;
class RateService
{
    /**
     * 获取最新的汇率信息
     * @param
     * currencyName    币种 
     * platformCode    平台code
     * @return
     * array              汇率的信息
     */
    private function _get_purchase_rate($param)
    {
        $w['is_used'] = array('eq',1);
        if(isset($param['platformCode'])){
            $w['plat_code'] = array('eq',$param['platformCode']);
        }
        if(isset($param['currencyName'])){
            $w['currency_code'] = array('eq',strtoupper($param['currencyName']));
        }
        $purchase_rate_info = M('TRate')->where($w)->order('last_modified_date desc,id desc')->select();
        return $purchase_rate_info;

    }

    //更新汇率的缓存信息
    public function flush_rate_info($param)
    {
        $data = $this->_get_purchase_rate($param);
        return $this->_check_key_array($data);
        //S('purchase_rate_info',$purchase_rate_info,3600*24);
    }

    //转换数组
    private function _check_key_array($data)
    {
        $currencys = array_column($data,'currency_code');
        $res = array();
        foreach ($data as $key => $value) {
            $currency_code = $value['currency_code'];
            $res[$currency_code][] = $value;
        }
        return $res;
    }

    /*
     * 获取最高的充值汇率
     * @param plat_code 平台编码
     * @param currency_code 币种编码
     */
    public function get_purchase_rate($plat_code,$currency_code)
    {
        $w['is_used'] = array('eq',1);
        $w['plat_code'] = $plat_code;
        $w['currency_code'] = $currency_code;
        return M('TRate')->where($w)->order('deposit_price desc')->find();
    }

}