<?php
/**
 * addby : lly
 * date : 2018-04-23 11:25
 * used : 银行卡的服务
 */
namespace Common\Service;
use Think\Model;
class BankCarService
{

    public function __Construct()
    {
        $bank_cars = S('bank_car_info');
        if(empty($bank_cars))
        {
            $bank_cars = $this->_get_bankcar_list();
            //$plat_code = array(1=>'HUOBI',2=>'OKEX');
            S('bank_car_info',$bank_cars,3600*24);
        }
    }

    /**
     * 获取所有激活的银行卡信息列表
    */
    private function _get_bankcar_list()
    {
        $bankM = M('TBankCarinfo');
        $w['status'] = array('eq',1);
        //要用缓存还不能针对用户的银行名称来查,而是所有
        /*if(isset($data['bankname']) && $data['bankname']!='')
        {
            $w['bank_name'] = array('eq',$data['bankname']);
        }*/
        $field = 'bank_name,branch_bank,bank_addr,bank_card_no,bank_card_holder';
        $bank_carinfo = $bankM->field($field)->where($w)->select();
        return $bank_carinfo;
    }

    /**
     * 银行卡获取接口
     * @param $data 传过来的数据 uid,bankname
     * return bank_card_no 银行卡号
     */
    public function _get_bank_car($data) {
        //根据$data的数据获取一个未使用过的地址,返回
        $redis_car_info = S('bank_car_info');
        $banknames = array_column($redis_car_info,'bank_name');
        $res = array();
        if(isset($data['bankname']) && $data['bankname']!=''){
            $index = array_search($data['bankname'],$banknames);
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
        return $res;
    }

    /**
     * @param $cardno
     */
    public function get_bankcard_info($cardno){
        $bankM = M('TBankCarinfo');
        $w['status'] = array('eq',1);
        $w['bank_card_no'] = $cardno;
        return $bankM->where($w)->find();
    }
}