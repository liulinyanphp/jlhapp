<?php
/**
 * Created by PhpStorm.
 * User: tang
 * Date: 2018/4/24
 * Time: 下午9:27
 */
namespace Common\Service;
use Common\Service\PurchaseStockService;
use \Org\Util\Exception;
use \Org\Util\Result;
use Common\Utils\OmniUtils;
use Common\Service\AddressService;
class UserAddressBalanceService{

    /**查询用户可用币数量     总数-冻结数
     * @param $param
     * plat_code        平台编号
     * uid              用户id
     * currency_code    币种编号
     * @return array
     * available_num
     */
    public function query_user_coin_available_num($param){

        $return = array('available_num' => 0.00000000);

        $useraddressbalanceM = M('TUserAddressBalance');
        $w['uid'] = array('eq', $param['uid']);
        $w['currency_code'] = array('eq', $param['currency_code']);
        $w['plat_code'] = array('eq', $param['plat_code']);
        $w['_logic'] = 'AND';
        $ret = $useraddressbalanceM->where($w)->find();
        if($ret){
            $return['available_num'] = $ret['user_total_num'] - $ret['freeze_num'];
            $return['id'] = $ret['id'];
            $return['freeze_num'] = $ret['freeze_num'];
        }else{
            E('查询用户信息失败',-1);
        }

        return $return;
    }

    /**
     * param
     * trade_no
     */
    private function check_freeze_record_exist($param){
        $TFreezeRecordM = M('TFreezeRecord');
        $w = array(
          'trade_no' => array('eq',$param['trade_no'])
        );
        return $TFreezeRecordM->where($w)->order('created_date desc')->find();
    }

    /**用户币冻结
     * @param $param
     * plat_code        平台编号
     * uid              用户id
     * currency_code    币种编号
     * freeze_num       冻结数量
     * freeze_type      冻结类型
     * mark             备注      (可选)
     * trade_no         交易单号
     * last_modified_by 操作人
     * @return bool
     */
    public function user_coin_freeze($param){

        if(!array_key_exists('mark', $param)){
            $param['mark'] = '';
        }
        //校验该单号是否有过冻结记录
        $freeze_record = self::check_freeze_record_exist(array(
            'trade_no' => $param['trade_no']
        ));
        if(!empty($freeze_record)){
            E('冻结失败，该单号已有冻结记录',-1);
        }

        //校验可用数量是否充足
        $query_param = array(
            'plat_code' => $param['plat_code'],
            'uid' => $param['uid'],
            'currency_code' => $param['currency_code']
        );
        $ret = self::query_user_coin_available_num($query_param);
        if($param['freeze_num'] > $ret['available_num']){
            E('冻结数量失败，可用数量不足',-1);
        }

        $useraddressbalanceM = M('TUserAddressBalance');
        $data['freeze_num'] = bcadd($param['freeze_num'], $ret['freeze_num'], 8);
        $data['last_freeze_type'] = $param['freeze_type'];
        $data['mark'] = $param['mark'];
        $data['last_modified_by'] = $param['last_modified_by'];
        $save = $useraddressbalanceM->where('id='.$ret['id'])->save($data);

        if($save != 1){
            E('冻结数量失败，更新失败',-1);
        }else{
            //写冻结记录
            $TFreezeRecordM = M('TFreezeRecord');
            $data = array(
                'freeze_num' => $param['freeze_num'],
                'trade_no' => $param['trade_no'],
                'freeze_type' => $param['freeze_type'],
                'operate' => 'freeze',
                'created_by' => $param['last_modified_by'],
                'last_modified_by' => $param['last_modified_by'],
                'mark' => $param['mark']
            );
            $TFreezeRecordM->data($data)->add();

            //写log表
            $data = $useraddressbalanceM->where('id='.$ret['id'])->find();
            unset($data['id']);
            $purchaseM = M('TUserAddressBalanceLog');
            $purchaseM->data($data)->add();
        }

        return true;
    }

    /**用户币解冻  解冻用于交易单取消或者驳回
     * @param $param
     * plat_code        平台编号
     * uid              用户id
     * currency_code    币种编号
     * unfreeze_num     解冻数量
     * mark             备注
     * trade_no         交易单号
     * last_modified_by 操作人
     * @return bool
     */
    public function user_coin_unfreeze($param){
        if(!array_key_exists('mark', $param)){
            $param['mark'] = '';
        }
        //校验该单号是否有过冻结记录
        $freeze_record = self::check_freeze_record_exist(array(
            'trade_no' => $param['trade_no']
        ));
        if(empty($freeze_record)){
            E('解冻失败，该单号不存在冻结记录',-1);
        }
        if($freeze_record['operate'] != 'freeze'){
            E('解冻失败，该单号已存在解冻记录',-1);
        }

        //校验可解冻数量是否充足，否则报错
        $ret = self::check_unfreeze(array(
            'plat_code' => $param['plat_code'],
            'uid' => $param['uid'],
            'currency_code' => $param['currency_code'],
            'unfreeze_num' => $param['unfreeze_num']
        ));

        $useraddressbalanceM = M('TUserAddressBalance');
        $data = array(
            'freeze_num' => bcsub($ret['freeze_num'], $param['unfreeze_num'], 8),
            'mark' => $param['mark'],
            'last_modified_by' => $param['last_modified_by']
        );
        $save = $useraddressbalanceM->where('id='.$ret['id'])->save($data);
        if($save != 1){
            E('解冻数量失败，更新失败',-1);
        }else{
            //写解冻记录
            $TFreezeRecordM = M('TFreezeRecord');
            $data = array(
                'freeze_num' => $param['unfreeze_num'],
                'trade_no' => $param['trade_no'],
                'freeze_type' => $freeze_record['freeze_type'],
                'operate' => 'unfreeze',
                'created_by' => $param['last_modified_by'],
                'last_modified_by' => $param['last_modified_by'],
                'mark' => $param['mark']
            );
            $TFreezeRecordM->data($data)->add();

            //写log
            $data = $useraddressbalanceM->where('id='.$ret['id'])->find();
            unset($data['id']);
            $useraddressbalancelogM = M('TUserAddressBalanceLog');
            $useraddressbalancelogM->data($data)->add();
        }

        return true;
    }

    /**用户币扣减    一定要走事务 调这个方法前对应的用户币数量肯定要做冻结     todo 注意！！！这个方法只用于用户提币、提现场景扣减用户的币，其他扣币操作请再单另写
     * @param $param
     * plat_code        平台编号
     * uid              用户id
     * currency_code    币种编号
     * deduct_num       扣除数量
     * mark             备注
     * trade_no         交易单号
     * last_modified_by 操作人
     * @return bool
     */
    public function user_coin_deduct($param){

        if(!array_key_exists('mark', $param)){
            $param['mark'] = '';
        }
        //校验该单号是否有过冻结记录
        $freeze_record = self::check_freeze_record_exist(array(
            'trade_no' => $param['trade_no']
        ));
        if(empty($freeze_record)){
            E('数量扣减失败，该单号不存在冻结记录',-1);
        }
        if($freeze_record['operate'] != 'freeze'){
            E('数量扣减失败，该单号已存在解冻记录',-1);
        }

        $ret = self::check_unfreeze(array(
            'plat_code' => $param['plat_code'],
            'uid' => $param['uid'],
            'currency_code' => $param['currency_code'],
            'unfreeze_num' => $param['deduct_num']
        ));

        //解冻并扣币
        $useraddressbalanceM = M('TUserAddressBalance');
        $balance_ret = $useraddressbalanceM->lock(true)->where('id='.$ret['id'])->find();
        $data = array(
            'user_total_num' => bcsub($balance_ret['user_total_num'], $param['deduct_num'], 8),
            'freeze_num' => bcsub($balance_ret['freeze_num'], $param['deduct_num'], 8),
            'mark' => $param['mark'],
            'last_modified_by' => $param['last_modified_by']
        );
        $save = $useraddressbalanceM->where('id='.$balance_ret['id'])->save($data);
        if($save != 1){
            E('扣除用户币数量失败，更新失败',-1);
        }else{
            //写解冻记录
            $TFreezeRecordM = M('TFreezeRecord');
            $data = array(
                'freeze_num' => $param['deduct_num'],
                'trade_no' => $param['trade_no'],
                'freeze_type' => $freeze_record['freeze_type'],
                'operate' => 'unfreeze',
                'created_by' => $param['last_modified_by'],
                'last_modified_by' => $param['last_modified_by'],
                'mark' => $param['mark']
            );
            $TFreezeRecordM->data($data)->add();

            //写log
            $data = $useraddressbalanceM->where('id='.$ret['id'])->find();
            unset($data['id']);
            $useraddressbalancelogM = M('TUserAddressBalanceLog');
            $useraddressbalancelogM->data($data)->add();
        }

        return true;
    }

    /**校验是否可解冻
     * @param $param
     * @return mixed
     */
    private function check_unfreeze($param){
        //校验可解冻数量是否充足，否则报错
        $useraddressbalanceM = M('TUserAddressBalance');
        $w['plat_code'] = array('eq', $param['plat_code']);
        $w['uid'] = array('eq', $param['uid']);
        $w['currency_code'] = array('eq', $param['currency_code']);
        $w['_logic'] = 'AND';
        $ret = $useraddressbalanceM->where($w)->find();
        if($ret){
            if($param['unfreeze_num'] > $ret['freeze_num']){
                E('解冻数量失败，解冻数量超过冻结数量',-1);
            }
        }else{
            E('查询用户信息失败',-1);
        }
        return $ret;
    }

    /**
     *测试事务及回滚
     */
    public  function test_transaction($param){

        $purchaseM = M('TPurchaseStock');
        $data['remain_num'] = $param['remain_num'];
        $data['last_modified_by'] = 'ewqewqewqewq';
        $data['last_modified_date'] = date('Y-m-d H:i:s',time());
        $save = $purchaseM->where('id=' . $param['id'])->save($data);

        E('报错报错报错',-1);

        return true;

    }

    /**
     *   账户查询
     * @param $cparam
     */
    public function query_by_condition($param){
        $useraddressbalanceM = M('TUserAddressBalance');

        $w = array();

        $platcode = $param['platformCode'];
        if(!empty($platcode)){
            $w['plat_code'] = array('eq',$platcode);

        }

        $userAddr = $param['userId'];
        if(!empty($userAddr)){
            $w['uid'] = array('eq',$userAddr);

        }

        $userAddr = $param['userAddress'];
        if(!empty($userAddr)){
            $w['user_addr'] = array('eq',$userAddr);

        }

        $currencyCode = $param['currencyCode'];
        if(!empty($currencyCode)){
            $w['currency_code'] = array('eq',$currencyCode);

        }


        return $useraddressbalanceM->where($w)->order('created_date desc')->find();
    }

    /**
     *
     * @param $param
     * array(
            'uid'=>'111',
            'num'=>'100.00',
            'plat_code'=>'huobi',
            'currency_code' = 'usdt',
            'trade_no'=>'huobi0001',
            'mark' =>''.   说明划账的原因
        )
     * @return array
     */
    public function coin_to_user($param=array())
    {
        //划帐的个数
        $num = $param['num'];
        //币种编码
        $currency_code = $param['currency_code'];
        //首先获取币还是否足够
        $PurchaseStockService = new PurchaseStockService();
        $stock_w['currency_code'] = $currency_code;
        $res_total_num = $PurchaseStockService->get_remain_num($stock_w);
        if($res_total_num['total_num'] < $num)
        {
            Exception::throwsErrorMsg(C('PURCHASECOIN_NOT_ENOUGH'));
        }
        //如果充足的话,就进行划转
        $userAddressBalanceM = M('TUserAddressBalance');
        //用户的id
        $uid = $param['uid'];
        $w['uid'] = array('eq',$uid);
        //平台code
        $w['plat_code'] = array('eq',$param['plat_code']);
        //币种编码
        $w['currency_code'] = array('eq',$currency_code);
        $data = $userAddressBalanceM->where($w)->find();
        $data['user_total_num'] = bcadd($data['user_total_num'],$num,8);
        $data['mark'] = $param['mark'];
        $data['created_by'] = session("username") ? session('username') : 'sys_api';
        $data['last_modified_by'] = session("username") ? session('username') : 'sys_api';
        $trans = M();
        $trans->startTrans();   // 开启事务
        try{
            $stock_param = array(
                'deduct_num'=>$num,
                'currency_code'=>$currency_code,
                'trade_no'=>$param['trade_no'],
                'operator'=>session("username") ? session('username') : 'sys_api'
            );
            //扣除库存
            $PurchaseStockService->deduct_num($stock_param);
            //划转记录
            $trans->table('t_user_address_balance')->data($data)->save();
            //添加转账记录
            $logdata = $data;
            unset($logdata['id']);
            $trans->table('t_user_address_balance_log')->add($logdata);
        }catch(\Exception $e) {
            $trans->rollback();
            return Result::innerResultFail($e);
        }
        $trans->commit();
        return Result::innerResultSuccess();
    }

    /*
     * @param $param
     * array(
            'uid'=>'111',
            'num'=>'100.00',
            'plat_code'=>'huobi',
            'currency_code' = 'usdt',
            'address'=>'address'
            'trade_no'=>'huobi0001',
            'mark' =>''.  说明划账的原因
        )
     * @return array
     */
    public function duct_coin_from_user($param=array())
    {
        if(empty($param)){return $param;}
        //如果充足的话,就进行划转
        $userAddressBalanceM = M('TUserAddressBalance');
        //用户的id
        $uid = $param['uid'];
        $w['uid'] = array('eq',$uid);
        $w['plat_code'] = array('eq',$param['plat_code']);
        $w['currency_code'] = array('eq',$param['currency_code']);
        $w['user_addr'] = array('eq',$param['address']);
        $data = $userAddressBalanceM->where($w)->find();
        if(empty($data)){
            E('没有获取到用户信息',-1);
        }
        if(bcsub($data['user_total_num'],$param['num'])<0)
        {
            E('用户余额不足,扣币失败',-1);
        }
        //如果余额足的话,直接操作表咯
        $data['user_total_num'] = bcsub($data['user_total_num'],$param['num'],8);
        $data['mark'] = $param['mark'];
        $data['created_by'] = session("username") ? session('username') : 'sys_api';
        $data['last_modified_by'] = session("username") ? session('username') : 'sys_api';
        $trans = M();
        $trans->startTrans();   // 开启事务
        try{
            //划转记录
            $trans->table('t_user_address_balance')->data($data)->save();
            //添加转账记录
            $logdata = $data;
            unset($logdata['id']);
            $trans->table('t_user_address_balance_log')->add($logdata);
        }catch(\Exception $e) {
            $trans->rollback();
            $output['result'] = $e->getCode();
            $output['res_info'] = $e->getMessage();
            return $output;
        }
        $trans->commit();
        return array('result' => 0,'res_info' => 'ok');
    }


    /**用户币增加        只有在收币完成的时候去调
     * @param $param
     * plat_code        平台编号
     * uid              用户id
     * currency_code    币种编号
     * add_num          增加的币数量
     * last_modified_by 操作人
     * mark             备注
     * @return bool
     */
    public function user_coin_add($param){

        $useraddressbalanceM = M('TUserAddressBalance');
        $w = array(
            'plat_code' => array('eq', $param['plat_code']),
            'uid' => array('eq', $param['uid']),
            'currency_code' => array('eq', $param['currency_code'])
        );
        $ret = $useraddressbalanceM->where($w)->find();
        if(empty($ret)){
            E('查询用户信息失败',-1);
        }

        $data = array(
            'user_total_num' => bcadd($ret['user_total_num'], $param['add_num'], 8),
            'user_address_num' => bcadd($ret['user_address_num'], $param['add_num'], 8),
            'mark' => $param['mark'],
            'last_modified_by' => $param['last_modified_by']
        );
        $save = $useraddressbalanceM->where('id='.$ret['id'])->save($data);
        if($save != 1){
            E('增加用户币失败，更新失败',-1);
        }else{
            //写log
            $data = $useraddressbalanceM->where('id='.$ret['id'])->find();
            unset($data['id']);
            $useraddressbalancelogM = M('TUserAddressBalanceLog');
            $useraddressbalancelogM->data($data)->add();
        }

        return true;
    }





}