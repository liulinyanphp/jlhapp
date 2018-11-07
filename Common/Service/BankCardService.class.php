<?php
/**
 * addby : lly
 * date : 2018-04-23 11:25
 * used : 银行卡的服务
 */
namespace Common\Service;

use Think\Model;
use Org\Util\Exception;
use Org\Util\Result;
use Common\Utils\ExloggerUtils;
class BankCardService
{

    public function __Construct()
    {
        $bank_cars = S('bank_card_info');
        if(empty($bank_cars))
        {
            $bank_cars = $this->_get_bankcar_list();
            S('bank_card_info',$bank_cars,3600*24);
        }
    }

    /**
     * 获取所有激活的银行卡信息列表
    */
    private function _get_bankcar_list()
    {
        $bankM = M('TBankCardInfo');
        $w['status'] = array('eq',1);
        $field = 'bank_name,branch_bank,bank_addr,bank_card_no,bank_card_holder';
        $bank_carinfo = $bankM->field($field)->where($w)->select();
        return $bank_carinfo;
    }

    /**
     * 银行卡获取接口
     * @param array $data 传过来的数据 uid,bankname
     * @return array bank_card_no 银行卡信息
     */
    public function get_bank_car($data=array()) {
        //根据$data的数据获取一个未使用过的地址,返回
        $redis_car_info = S('bank_card_info');
        $banknames = array_column($redis_car_info,'bank_name');
        $res = array();
        if(isset($data['bankName']) && $data['bankName']!=''){
            $index = array_search($data['bankName'],$banknames);
            if($index !== false)
            {
                $res = $redis_car_info[$index];
            }
        }
        //如果接口传过来的银行名称没有找到的话就随机一个
        if(empty($res)){
            $rand_index = array_rand($redis_car_info);
            $res = $redis_car_info[$rand_index];
        }
        try {
            if (empty($res))
            {
                //记录错误日志
                ExloggerUtils::log(C('BANK_CARD_IS_EMPTY.1'),'error');
                Exception::throwsErrorMsg(C('BANK_CARD_IS_EMPTY'));
            }
        }
        catch(\Exception $e)
        {
            return Result::apiResultFail($e);
        }
        return Result::apiResultSuccess($res);
    }
}