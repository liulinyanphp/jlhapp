<?php
/**
 * addby : lly
 * date : 2018-04-27 15:40
 * used : 平台获取token的服务
 */
namespace Common\Service;
use Think\Model;
class GasService
{
    public static $res = array('error'=>0,'msg'=>'ok');
    /**
     * 获取手续费
     * @param
     * array(
     *   plat_code =>'平台编码''
     *   currency_code=>'币种编码'
     *   gas_type => '手续费类型(1为充值，2为提现)'
     *   amount=>'交易数量'
     *   accuracy=>'需要返回的小数点位数'
     * @return
     * string '数量'
     */
    public static function  get_gas_info($param)
    {
        $array_key = array('plat_code','currency_code','gas_type','amount','accuracy');
        $param_key = array_keys($param);
        $res = self::$res;
        if(!empty(array_diff($array_key,$param_key)))
        {
            $res['error'] = 1;
            $res['msg'] = '参数出错啦';
            return $res;
        }
        $amout = $param['amount'];
        $accuracy = $param['accuracy'];
        unset($param['amount']);
        unset($param['accuracy']);
        $param['is_used'] = array('eq',1);
        $gasinfo =M('TGasConfig')->where($param)->find();
        if(empty($gasinfo)) {

            return  0;
        }
        $percent = $gasinfo['percent'];
        $state_value = $gasinfo['state_value'];
        $num = bcmul($amout,$percent,$accuracy);
        $num = bcdiv($num,100,$accuracy);
        return bcadd($num,$state_value,$accuracy);
    }

}