<?php
/**
 * Created by PhpStorm.
 * User: tang
 * Date: 2018/4/24
 * Time: 下午5:30
 */

namespace Common\Service;

use Think\Cache\Driver\Eaccelerator;
use Common\Conf\BaseConfig;
use Common\Utils\CommonUtils;
use Common\Utils\SeqNoUtils;
use Think\Cache\Driver\Redis;
use Common\Utils\ExloggerUtils;

class PresentRecordService
{

    //入参映射
    protected $_map = array(
        'tradeId' => 'trade_no',
        'userId' => 'uid',
        'platformCode' => 'plat_code',
        'userName' => 'real_name',
        'bankName' => 'bank',
        'branchBankName' => 'branch_bank',
        'bankCardNo' => 'bank_card_no',
        'currencyName' => 'currency_code',
        'applyNum' => 'sell_num',
        'unitPrice' => 'curr_price',
        'totalAmount' => 'apply_amount',
        'time' => 'apply_time'
    );

    /**
     * 提现单状态流转
     * 处理中
     * 已完成
     *
     * 已驳回
     */


    /**
     * `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
     * `trade_no` varchar(128) NOT NULL DEFAULT '' COMMENT '交易号',
     * `uid` varchar(128) NOT NULL DEFAULT '' COMMENT '购买币的用户id',
     * `status` varchar(20) NOT NULL DEFAULT '' COMMENT '基础状态',
     * `plat_code` varchar(20) NOT NULL DEFAULT '' COMMENT '平台编码',
     * `real_name` varchar(32) NOT NULL DEFAULT '' COMMENT '用户真实姓名',
     * `bank` varchar(30) NOT NULL DEFAULT '' COMMENT '开户行',
     * `branch_bank` varchar(30) NOT NULL DEFAULT '' COMMENT '开户支行',
     * `bank_card_no` varchar(30) NOT NULL DEFAULT '' COMMENT '银行卡号',
     * `currency_code` varchar(30) NOT NULL DEFAULT '' COMMENT '币种编码',
     * `sell_num` decimal(20,8) NOT NULL DEFAULT '0.00000000' COMMENT '售数量',
     * `curr_price` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '售卖价格',
     * `apply_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '提现金额',
     * `fee_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '手续费',
     * `real_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '到账金额',
     * `pay_bank` varchar(30) DEFAULT '' COMMENT '打款银行名称',
     * `pay_bank_card_no` varchar(30) DEFAULT '' COMMENT '打款银行卡号',
     * `pay_amout` decimal(20,2) DEFAULT '0.00' COMMENT '打款金额',
     * `apply_time` timestamp NULL DEFAULT NULL COMMENT '申请时间',
     * `bank_trans_no` varchar(64) DEFAULT '' COMMENT '银行流水号',
     * `payment_time` timestamp NULL DEFAULT NULL COMMENT '到账时间',
     * `operator_id` int(10) unsigned DEFAULT NULL COMMENT '操作人id',
     * `operator_name` varchar(30) DEFAULT '' COMMENT '操作人员姓名',
     * `created_by` varchar(50) DEFAULT NULL COMMENT '创建人',
     * `last_modified_by` varchar(50) DEFAULT NULL COMMENT '更新人',
     * `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
     * `last_modified_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
     * `is_recovery`
     */


    /**创建提现单对远端平台接口         走事务     WithdrawCashApply
     * @param $param
     * trade_no            string        交易单号
     * uid                string        用户id
     * plat_code        string        平台编码        校验是否存在该平台
     * real_name        string        用户姓名
     * bank            string        银行类型
     * branch_bank        string        开户支行
     * bank_card_no    string        银行卡号
     * currency_code    string      提现币种
     * sell_num            string        提现个数        校验个数小数点后四位
     * curr_price        string        提现单价
     * apply_amount     string      提现金额
     * #apply_time        string        申请时间（暂时不用）
     * @return
     */
    public function present_record_create($out_param)
    {
        ExloggerUtils::log('present_record_create入参：'. json_encode($out_param), 'info');

        $trans = M();
        $trans->startTrans();   // 开启事务

        try {

            $param = CommonUtils::keyMap($out_param, $this->_map);

            //校验同一个平台 同样的单号只能有一条记录
            $present_recordM = M("TPresentRecord");
            $w = array(
                'trade_no' => array('eq', $param['trade_no']),
                'plat_code' => array('eq', $param['plat_code']),
                '_logic' => 'AND'
            );
            $ret = $present_recordM->where($w)->find();
            if (!empty($ret)) {
                E('提现单已存在', -1);
            }

            //校验是否有还未完成的单
            $w = array(
                'uid' => array('eq', $param['uid']),
                'status' => array('eq', BaseConfig::WithdrawDepositSubmitted),
                '_logic' => 'AND'
            );
            $ret = $present_recordM->where($w)->find();
            if (!empty($ret)) {
                E('该用户还有未完成的提现单', -1);
            }

            //校验    提现单价    查询汇率    不一致报错
            $RateM = new RateService();
            $resinfo = $RateM->flush_rate_info(array(
                'currencyName' => $param['currency_code'],
                'platformCode' => $param['plat_code']
            ));
            \Think\Log::write('present_record_create查询汇率结果：' . json_encode($resinfo), 'INFO');
            $currency_code = strtoupper($param['currency_code']);
            $res = $resinfo[$currency_code][0];
            if (empty($res['withdraw_price']) || $param['curr_price'] != $res['withdraw_price']) {
                E('汇率失效', -1);
            }

            //校验数字
            $param['sell_num'] = self::check_number($param['sell_num'], 8);
            $param['curr_price'] = self::check_number($param['curr_price'], 2);
            $param['apply_amount'] = self::check_number($param['apply_amount'], 2);

            //校验    提现个数*单价 = 总额
            $num_result = bcdiv($param['apply_amount'], $param['curr_price'], 8);
            if ($num_result != $param['sell_num']) {
                E('金额计算结果不相符', -1);
            }

            //校验    用户可用剩余数量是否充足
            //执行    冻结用户申请提现的币数量
            $s = new UserAddressBalanceService();
            $s->user_coin_freeze(array(
                'plat_code' => $param['plat_code'],
                'uid' => $param['uid'],
                'currency_code' => $param['currency_code'],
                'freeze_num' => $param['sell_num'],
                'freeze_type' => BaseConfig::FreezeTypeWithdrawDeposit,
                'mark' => '用户提现冻结',
                'trade_no' => $param['trade_no'],
                'last_modified_by' => $param['plat_code']
            ));

            //执行    写入数据库
            $present_recordM = M("TPresentRecord");
            $present_recordM->trade_no = $param['trade_no'];
            $present_recordM->uid = $param['uid'];
            $present_recordM->status = BaseConfig::WithdrawDepositSubmitted;
            $present_recordM->plat_code = $param['plat_code'];
            $present_recordM->real_name = $param['real_name'];
            $present_recordM->bank = $param['bank'];
            $present_recordM->branch_bank = $param['branch_bank'];
            $present_recordM->bank_card_no = $param['bank_card_no'];
            $present_recordM->currency_code = $param['currency_code'];
            $present_recordM->sell_num = $param['sell_num'];
            $present_recordM->curr_price = $param['curr_price'];
            $present_recordM->apply_amount = $param['apply_amount'];
            $present_recordM->apply_time = date('Y-m-d H:i:s', time());
            $present_recordM->created_by = $param['plat_code'];
            $present_recordM->last_modified_by = $param['plat_code'];
            $present_recordM->real_amount = $param['apply_amount'];
            $save = $present_recordM->add();
            if (!$save) {
                E('创建提现单失败,更新失败', -1);
            }

            $trans->commit();
            return array('status' => 0, 'message' => 'success', 'data' => array());

        } catch (\Exception $e) {
            $trans->rollback();
            $output = array(
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
                'data' => array()
            );
            \Think\Log::write('WithdrawCashApply接口错误：' . json_encode($output), 'ERR');
            return $output;
        }
    }


    /**业务人员驳回提现单
     * @param $param
     * plat_code    平台编号
     * trade_no     提现单号
     * mark         驳回原因
     * operator     操作人
     * @return
     */
    public function present_record_reject($param)
    {
        $trans = M();
        $trans->startTrans();   // 开启事务

        try {

            //校验提现单状态
            $PresentRecordM = M('TPresentRecord');
            $w = array(
                'plat_code' => array('eq', $param['plat_code']),
                'trade_no' => array('eq', $param['trade_no']),
                '_logic' => 'AND'
            );
            $ret = $PresentRecordM->where($w)->find();
            if (empty($ret)) {
                E('单号不存在', -1);
            }
            if ($ret['status'] != BaseConfig::WithdrawDepositSubmitted) {
                E('提现单状态异常', -1);
            }

            //数量解冻
            $s = new UserAddressBalanceService();
            $s->user_coin_unfreeze(array(
                'plat_code' => $ret['plat_code'],
                'uid' => $ret['uid'],
                'currency_code' => $ret['currency_code'],
                'unfreeze_num' => $ret['sell_num'],
                'mark' => '提现单驳回解冻数量',
                'trade_no' => $ret['trade_no'],
                'last_modified_by' => $param['operator']
            ));

            //提现单流转状态
            $data = array(
                'status' => BaseConfig::WithdrawDepositRejected
            );
            $save = $PresentRecordM->where('id =' . $ret['id'])->save($data);
            if ($save != 1) {
                E('驳回提现单失败，更新失败', -1);
            }

            $trans->commit();
            return array('result' => 0, 'res_info' => 'success', 'result_rows' => array());

        } catch (\Exception $e) {
            $trans->rollback();
            $output = array(
                'result' => $e->getCode(),
                'res_info' => $e->getMessage(),
                'result_rows' => array()
            );
            \Think\Log::write('present_record_reject接口错误：' . json_encode($output), 'ERR');
            return $output;
        }

    }

    /**
     * `real_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '到账金额',
     * `pay_bank` varchar(30) DEFAULT '' COMMENT '打款银行名称',
     * `pay_bank_card_no` varchar(30) DEFAULT '' COMMENT '打款银行卡号',
     * `pay_amout` decimal(20,2) DEFAULT '0.00' COMMENT '打款金额',
     * `apply_time` timestamp NULL DEFAULT NULL COMMENT '申请时间',
     * `bank_trans_no` varchar(64) DEFAULT '' COMMENT '银行流水号',
     * `payment_time` timestamp NULL DEFAULT NULL COMMENT '到账时间',
     * `operator_id` int(10) unsigned DEFAULT NULL COMMENT '操作人id',
     */

    /**业务人员已打款通知远端平台        走事务
     * @param $param
     * plat_code        平台编号
     * trade_no         交易号
     * pay_bank         打款银行名称
     * pay_bank_card_no 打款银行卡号
     * pay_amout        打款金额
     * bank_trans_no    银行流水号
     * payment_time     到账时间            到账时间不太懂
     * operator_id      操作人员id
     * operator_name    操作人员姓名
     *
     * @return array
     */
    public function present_record_pay_notice($param)
    {
        ExloggerUtils::log('present_record_pay_notice入参：'. json_encode($param), 'info');

        $trans = M();
        $trans->startTrans();   // 开启事务

        try {

            //查询交易单信息
            $ret = self::query_present_by_trade_no(array(
                'plat_code' => $param['plat_code'],
                'trade_no' => $param['trade_no']
            ));
            if (empty($ret)) {
                E('交易单' . $param['trade_no'] . '不存在', -1);
            }
            if ($ret['status'] != BaseConfig::WithdrawDepositSubmitted) {
                E('该交易单状态异常', -1);
            }

//            //银行卡号输入有误
//            $s = new BankCarService();
//            $ret = $s->get_bankcard_info($param['pay_bank_card_no']);
//            if (empty($ret)) {
//                E('银行卡输入有误', -1);
//            }

            //扣用户的币数量           这里会有风险  如果已经打款 但是扣币失败   可能的原因是历史的冻结和解冻数量未匹配所导致
            $s = new UserAddressBalanceService();
            $s->user_coin_deduct(array(
                'plat_code' => $ret['plat_code'],
                'uid' => $ret['uid'],
                'currency_code' => $ret['currency_code'],
                'deduct_num' => $ret['sell_num'],
                'mark' => '扣除用户的币数量',
                'trade_no' => $param['trade_no'],
                'last_modified_by' => $param['operator_name']
            ));
            //todo 通知远端平台已打款

            //流转提币单状态
            $present_recordM = M("TPresentRecord");
            $data = array();
            $data['status'] = BaseConfig::WithdrawDepositFinished;
            $data['pay_bank'] = $param['pay_bank'];
            $data['pay_bank_card_no'] = $param['pay_bank_card_no'];
            $data['pay_amout'] = $param['pay_amout'];
            $data['bank_trans_no'] = $param['bank_trans_no'];
            $data['operator_id'] = $param['operator_id'];
            $data['operator_name'] = $param['operator_name'];
            $data['last_modified_by'] = $param['operator_name'];
            $data['payment_time'] = $param['payment_time'];

            $save = $present_recordM->where('id =' . $ret['id'])->save($data);
            if ($save != 1) {
                E('完结提现单失败，更新失败', -1);
            }

            $trans->commit();
            return array('result' => 0, 'res_info' => 'success', 'result_rows' => array());

        } catch (\Exception $e) {
            $trans->rollback();
            $output = array(
                'result' => $e->getCode(),
                'res_info' => $e->getMessage(),
                'result_rows' => array()
            );
            \Think\Log::write('present_record_pay_notice接口错误：' . json_encode($output), 'ERR');
            return $output;
        }
    }

    /**查询交易单信息
     * @param $param
     * plat_code
     * trade_no     交易单号
     * @return array
     */
    public function query_present_by_trade_no($param)
    {
        $present_recordM = M("TPresentRecord");
        $w['plat_code'] = array('eq', $param['plat_code']);
        $w['trade_no'] = array('eq', $param['trade_no']);
        $w['_logic'] = 'AND';
        return $present_recordM->where($w)->find();
    }


    /**回收用户提现的币到进货表         定时任务     暂定一天一次
     * @param $param
     * @return array
     */
    public function recovery_coin_to_purchase($param)
    {
        $output = array(
            'result' => 0,
            'res_info' => 'ok',
            'result_rows' => array()
        );

        //默认USDT    后面可以当参数传进来
        $currency_code = 'USDT';
        //todo 小于100条不执行
        //扫描提现表中所有已完成并且未回收的记录       捞所有     单应该不多 todo 后面加上分页
        $present_recordM = M("TPresentRecord");
        $w = array(
            'status' => array('eq', BaseConfig::WithdrawDepositFinished),
            'recovery_purchase_id' => array('eq', ''),
            'currency_code' => array('eq', $currency_code),
            '_logic' => 'AND'
        );
        $res_list = $present_recordM->field('id,trade_no,sell_num,curr_price,apply_amount')->where($w)->select();
        if (count($res_list) <= 0) {
            return $output;
        }

        $id_array = array();
        $total_num = 0;
        $total_amount = 0;

        //取出之后做统计
        foreach ($res_list as $row) {
            $total_num = bcadd($total_num, $row['sell_num'], 8);
            $total_amount = bcadd($total_amount, $row['apply_amount'], 2);
            array_push($id_array, $row['id']);
        }

        //计算均价  todo 这里考虑要不要相同单价建同一条进货单
        $avg_price = bcdiv($total_amount, $total_num, 2);

        $trans = M();
        $trans->startTrans();   // 开启事务

        try {
            //创建进货单
            $s = new PurchaseStockService();
            $purchase_id = $s->purchase_add(array(
                'currency_code' => $currency_code,
                'purchase_num' => $total_num,
                'curr_price' => $avg_price,
                'operator' => 'recovery_coin_to_purchase',
                'purchase_type' => 2,
                'sender_addr' => '',
                'receive_addr' => '',
                'total_amount' => $total_amount,
                'tx_hash' => '',
            ));

            //修改提现单状态
            $present_recordM = M("TPresentRecord");
            $w = array(
                'id' => array('in', $id_array)
            );
            $data = array(
                'status' => BaseConfig::WithdrawDepositFinished,
                'recovery_purchase_id' => $purchase_id
            );
            $present_recordM->where($w)->save($data);

            $trans->commit();
            return $output;
        } catch (\Exception $e) {
            $trans->rollback();
            $output = array(
                'result' => $e->getCode(),
                'res_info' => $e->getMessage(),
                'result_rows' => array()
            );
            \Think\Log::write('脚本执行失败：' . json_encode($output), 'ERR');
            return $output;
        }
    }


    /**校验数字格式
     * @param $num
     */
    private function check_number($num, $decimal)
    {
        if (!is_numeric($num)) {
            E('数字格式错误：' . $num, -1);
        }

        return CommonUtils::covertNum($num, $decimal);

    }



//    /**
//     * redis锁
//     */
//    protected function excute()
//    {
//        $redis_lock_key = 'erpshell' . 'SaleStoreStockSendSynErpShellService';
//        $redis = RuntimeResource::instance ()->getSaleStoreRedisMaster ();
//
//        $value = $redis->setnx ( $redis_lock_key, '1' );
//        if ($value) {
//            try {
//                $redis->expire($redis_lock_key,60*120);
//                $this->dowork ();
//            } catch ( Exception $e ) {
//                SaleStoreLogUtil::logBizInfo ( 11111, "销售出库推送异常, msg ->" . $e->getMessage () );
//            }
//            $redis->delete ( $redis_lock_key );
//        }
//    }

    /**提现单对外查询接口
     * @param $out_param
     */
    public function get_withdraw_deposite($out_param)
    {

        try {

            $param = CommonUtils::keyMap($out_param, $this->_map);

            $present_recordM = M("TPresentRecord");
            $w = array(
                'trade_no' => $param['trade_no'],
                //'plat_code' => $param['plat_code']
            );
            $ret = $present_recordM->where($w)->find();
            if (empty($ret)) {
                //单号不存在
                E('提现单不存在', -1);
            }

            if($ret['status'] == BaseConfig::WithdrawDepositCollected){
                $ret['status'] = BaseConfig::WithdrawDepositRejected;
            }

            $result = array(
                'tradeId' => $ret['trade_no'],
                'tradeStatus' => $ret['status']
            );
            return array('status' => 0, 'message' => 'success', 'data' => array($result));
        } catch (\Exception $e) {
            $output = array(
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
                'data' => array()
            );
            \Think\Log::write('get_withdraw_deposite查询失败：' . json_encode($output), 'ERR');
            return $output;
        }

    }


}