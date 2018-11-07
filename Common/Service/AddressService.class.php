<?php
/**
 * addby : lly
 * date : 2018-09-19 19:17
 * used : 地址相关的服务
 */
namespace Common\Service;

use Think\Model;
use Common\Service\OmniService;
use Common\Service\RsaService;
//引入公用的结果处理类
use \Org\Util\Result;
//引入公用的异常抛出类
use \Org\Util\Exception;
//引用公用的日志生成类
use Common\Utils\ExloggerUtils;
class AddressService
{
    /**
     * 地址接口
     * @param array $data 传过来的数据 userId,platcode,'currencyName'
     * @return string address
     */
    public function _get_address($data)
    {
        //根据$data的数据获取一个未使用过的地址,返回
        $w['user_id'] = (int)$data['userId'];
        $w['plat_code'] = $platcode = $data['platcode'];
        $w['currency_code'] = strtoupper($data['currencyName']);
        //先查看该用户是否已经有地址了
        $address = $this->_check_address($w);
        if(empty($address)){
            unset($w['user_id']);
            $w['is_used'] = 0;
            $w['user_id'] = 0;
            $trans = M();
            $trans->startTrans();
            try{
                $address = $this->_assignment_address($data['userId'],$platcode,$w);
                $this->_insert_user_address_balance($data,$address);
            }catch(\Exception $e) {
                $trans->rollback();
                return Result::apiResultFail($e);
            }
            $trans->commit();
            $w = '';
            //把分配之后的地址缓存起来
            S('adr_'.$address,$data['userId'],3600*24*365);
        }
        $param_address = array('address'=>$address);
        return Result::apiResultSuccess($param_address);
    }

    /**
     * 获取用户是否有地址
    **/
    private function _check_address($w=array())
    {
        $addressM = M('TAddress');
        $address = $addressM->where($w)->getField('address');
        return $address;
    }
    /**
     * 为用户分配地址
     * 后面使用redis处理高并发的情况
     @ parame $uid 用户id
     @ parame $platcode 平台编码,主要是最后更新人那里要用
     @ parame $w 查询地址的条件
     @ return string $address 返回地址
    **/
    private function _assignment_address($uid,$platcode,$w=array())
    {
        $addressM = M('TAddress');
        $res = $addressM->field('id,address')->where($w)->find();
        if(!empty($res)){
            $address = $res['address'];
            $res['is_used'] = 1;
            $res['user_id'] = $uid;
            $res['last_modified_by'] = strtolower('sys_'.$platcode.'_api');
            $addressM->data($res)->save();
            return $address;
        }else{
            //E('系统没有足够的地址供平台使用了,请联系平台方',-1);
            Exception::throwsErrorMsg(C('ADDRESS_NOT_ENOUGH'));
        }
    }

    public function _insert_user_address_balance($data,$address)
    {
        $param['uid'] = $data['userId'];
        $param['plat_code'] = $data['platcode'];
        $param['user_addr'] = $address;
        $param['currency_code'] = strtoupper($data['currencyName']);
        $ck_w = $param;
        $param['created_by'] = 'sys'.$data['platcode'].'_api';
        $param['last_modified_by'] = 'sys_'.$data['platcode'].'_api';
        $param['mark'] = '平台调用获取地址接口自动生成用户地址余额记录';
        if( M('TUserAddressBalance')->where($ck_w)->count() <1 ){
            M('TUserAddressBalance')->data($param)->add();
        }
        if(M('TUserAddressBalanceLog')->where($ck_w)->count() < 1 )
        {
            M('TUserAddressBalanceLog')->data($param)->add();
        }
    }

    /*
     * 批量生成地址接口
     * @param
     $param = array(
        'plat_code'=>'平台code',
        'currency_code'=>'币的编码',
        'num' => 10  生成地址的个数
        'created_by'=> '谁生成的 (自动脚本sys 还是用户后台调用生成)'
     )
    */
    public static function batch_create_address($param)
    {
        $res['status'] = 0;
        $res['message'] = 'ok';
        $array_key = array('plat_code','currency_code','num','created_by');
        $param_key = array_keys($param);
        try{
            if(!empty(array_diff($array_key, $param_key ))) {
                Exception::throwsErrorMsg(C('ADDRESS_PARAM_IS_ERROR'));
            }
            $plat_code = strtoupper($param['plat_code']);
            $num = $param['num'];
            $insert_data['plat_code'] = $plat_code;
            $insert_data['currency_code'] = $param['currency_code'];
            $insert_data['created_by'] = $param['created_by'];
            //对密钥进行加密
            $address_key = C("ADDRESS_KEY.$plat_code");
            $public_key = ROOT.$address_key['ADDRESS_PUBKEY'];
            //公钥加密
            $bitcoin = OmniService::getInstance();
            $rsa = new RsaService();
            $res['status'] = 0;
            $str = '';
            $addressM = D('TAddress');
            for($i=1;$i<=$num;$i++)
            {
                $tmpdata = $insert_data;
                $address = $bitcoin->getnewaddress();
                if($address['res_info'] != 'ok')
                {
                    $str .= "第".$i."个地址创建失败; ";
                    continue;
                }
                $address = $address['result_rows'];
                //密钥
                $tmpkey = $bitcoin->dumpprivkey($address);
                $keyv = 'cszb'.$tmpkey['result_rows'];
                if( $keyv == 'cszb')
                {
                    $str .="第".$i."个地址获取密钥失败; ";
                    continue;
                }
                $encrypt = $rsa->rsaEncrypt($keyv,$public_key);
                $tmpdata['address'] = $address;
                $tmpdata['privkey'] = $encrypt;
                $addressM->data($tmpdata)->add();
            }
            if(!empty($str))
            {
                $res = C('ADDRESS_CREATE_IS_ERROR');
                $res[1] = $str;
                ExloggerUtils::log($str,'error');
                Exception::throwsErrorMsg($res);
            }
        }catch(\Exception $e){
            return Result::innerResultFail($e);
        }
        return Result::innerResultSuccess();
    }

    /*
     * 确认用户的地址信息
     * @param platCode 平台编码
     * @param currencyCode 币种编码
     * @param uid  分配的用户id
     * @param address 地址
     */
    public function check_address_info($platCode,$currencyCode,$uid,$address)
    {
        $param['user_id'] = $uid;
        $param['plat_code'] = $platCode;
        $param['address'] = $address;
        $param['currency_code'] = strtoupper($currencyCode);
        $count = M('TAddress')->where($param)->count();
        return $count;
    }

    /*
     * @param array address
     * @param plat_code 平台编码
     * @return array key
     * desc 根据一个地址数组,获取一组密钥
     */
    public static function  get_key_for_address($address=array(),$plat_code='PUB')
    {
        try{
            if(!is_array($address) || empty($address))
            {
                Exception::throwsErrorMsg(C('ADDRESS_PARAM_IS_ERROR'));
            }
            $addressM = M('TAddress');
            $w['address'] = array('in',$address);
            $data = $addressM->field('address,privkey')->where($w)->select();
            if(empty($data) || count($address) != count($data)){
                Exception::throwsErrorMsg(C('ADDRESS_CHECK_IS_ERROR'));
            }
            $address_arr = array_column($data,'address');
            $private_key_arr = array_column($data,'privkey');
            $res = array_combine($address_arr,$private_key_arr);
            //如果传了平台code,则拿取对应平台的密钥,否则用公钥
            $keys = C('ADDRESS_KEY');
            $private_key = ROOT.$keys[$plat_code]['ADDRESS_PRIVKEY'];
            $rsa = new RsaService();
            //解析出请求过来的数据
            $return  = array();
            foreach($address as $obj)
            {
                $encrypt = $res[$obj];
                //进行解密
                $decrypt = $rsa->rsaDecrypt($encrypt,$private_key);
                $decrypt =  substr($decrypt,4);
                array_push($return,$decrypt);
            }
        }catch(\Exception $e){
            return Result::innerResultFail($e);
        }
        return Result::innerResultSuccess($return);
    }

    /**
     * 查询地址信息
     * @param $address
     * @return mixed
     */
    public static function  findAddress($address){

        $addressM = M('TAddress');

        $w = array();
        $w['address'] = array('eq',$address);
        $w['is_used'] = array('eq',1);

        return $addressM->where($w)->order('created_date desc')->find();
    }

    /*
     * @param
     * desc 获取未被使用的地址总数
     */
    public static function get_available_address_count()
    {
        $w['is_used'] = array('eq',0);
        $addressM = M('TAddress');
        return $addressM->where($w)->count();
    }

    /*
     * @param
     * desc获取运营钱包地址
     * @return array
     */
    public static function get_bus_address($w=array())
    {
        if(empty($w)){
            $w['id'] = array('gt',0);
        }
        return M('TBusAddress')->where($w)->getField('address',true);
    }

    /**查询指定平台，指定币种，指定类型的运营地址
     * @param $plat_code
     * @param $currency_name
     * @param $addr_type
     * @return mixed
     */
    public static function get_plat_bus_address($plat_code, $currency_name, $addr_type){

        $w['plat_code'] =array('eq',$plat_code);
        $w['currency_code'] =array('eq',$currency_name);
        $w['addr_type'] =array('eq',$addr_type);
        $w['is_used'] =array('eq',1);
        $w['is_deleted'] =array('eq',0);


        return M('TBusAddress')->where($w)->getField('address',true);

    }

    /**
     * @param $address
     * @return mixed
     */
    public static function  find_bus_address($address){

        $addressM = M('TBusAddress');
        $w = array();
        $w['address'] = array('eq',$address);
        $w['is_used'] = array('eq',1);
        return $addressM->where($w)->order('created_date desc')->find();
    }

}