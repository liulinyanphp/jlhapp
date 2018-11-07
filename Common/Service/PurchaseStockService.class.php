<?php
/**
 * Created by PhpStorm.
 * User: tang
 * Date: 2018/4/23
 * Time: 上午11:16
 */

namespace Common\Service;

use Common\Utils\SeqNoUtils;
use Common\Utils\OmniUtils;
use Common\Utils\ExloggerUtils;

use Common\Exception\BankSystemErrorCode;
use Common\Utils\ExceptionUtils;
use Common\Service\BusAddressBalanceService;
use Common\Service\AddressService;
class PurchaseStockService
{

    /**
     * 创建进货单并确认
     * @param
     * currency_code    币种
     * purchase_num     进货数量
     * curr_price       进货价格
     * sender_addr      发出地址
     * receive_addr     收货地址
     * total_amount     总金额
     * tx_hash          交易哈希
     * operator         操作人
     * @return
     */
    public function purchase_create($param)
    {
        ExloggerUtils::log('purchase_create入参：'. json_encode($param), 'info');

        try {

            //入参    币种  进货数量    进货价格    发出地址    收货地址    总金额     tx_hash
            $currency_code = $param['currency_code'];
            $purchase_num = $param['purchase_num'];
            $curr_price = $param['curr_price'];
            $sender_addr = $param['sender_addr'];
            $receive_addr = $param['receive_addr'];
            $total_amount = $param['total_amount'];
            $tx_hash = isset($param['tx_hash']) ? $param['tx_hash'] : '' ;
            $operator = $param['operator'];//取session

            //入参校验
            $tmp_amount = $purchase_num * $curr_price;
            if ($total_amount < ($tmp_amount - 10) || $total_amount > ($tmp_amount + 10)) {
                ExceptionUtils::throwsEx(BankSystemErrorCode::$PURCHASE_TOTAL_AMOUNT_ERROR);
            }

            //查询链上交易信息，是否收到币
            if($tx_hash !=''){
                $res = OmniUtils::getTransaction($tx_hash);
                if($res == ''){
                    ExceptionUtils::throwsEx(BankSystemErrorCode::$PURCHASE_TX_NOT_EXIST_ERROR);
                }
                if ($res['valid'] != true){
                    ExceptionUtils::throwsEx(BankSystemErrorCode::$PURCHASE_TX_INVALID_ERROR);
                }

                if ($res['amount'] != $purchase_num) {//
                    ExceptionUtils::throwsEx(BankSystemErrorCode::$PURCHASE_TOTAL_NUM_ERROR);
                }
                if ($res['referenceaddress'] != $receive_addr) {
                    ExceptionUtils::throwsEx(BankSystemErrorCode::$PURCHASE_RECEIVE_ADDR_ERROR);
                }
            }

            $bus_address_info = AddressService::find_bus_address($receive_addr);

            //运营地址币数量增加
            $s = new BusAddressBalanceService();
            $s->bus_coin_add(array(
                'plat_code' => $bus_address_info['plat_code'],
                'bus_addr' => $receive_addr,
                'currency_code' => $bus_address_info['currency_code'],
                'add_num' => $purchase_num,
                'last_modified_by' => $operator,
                'mark' => '进货'
            ));

            self::purchase_add(array(
                'currency_code' => $currency_code,
                'purchase_num' => $purchase_num,
                'curr_price' => $curr_price,
                'operator' => $operator,
                'purchase_type' => 1,
                'sender_addr' => $sender_addr,
                'receive_addr' => $receive_addr,
                'total_amount' => $total_amount,
                'tx_hash' => $tx_hash,
            ));

            return array('result' => 0, 'res_info' => 'ok', 'result_rows' => array());
        } catch (\Exception $e) {
            $output = array(
                'result' => $e->getCode(),
                'res_info' => $e->getMessage(),
                'result_rows' => array()
            );
            ExloggerUtils::log('purchase_create错误信息：'. json_encode($output), 'error');
            return $output;
        }


    }

    /**
     * 添加一条进货单
     */
    public function purchase_add($param){
        $purchaseM = M('TPurchaseStock');
        $purchaseM->create();
        $purchase_id = SeqNoUtils::seq_no_gen('PUR');
        $purchaseM->purchase_id = $purchase_id;
        $purchaseM->currency_code = $param['currency_code'];
        $purchaseM->purchase_num = $param['purchase_num'];
        $purchaseM->remain_num = $param['purchase_num'];
        $purchaseM->curr_price = $param['curr_price'];
        $purchaseM->operator_name = $param['operator'];
        $purchaseM->purchase_type = $param['purchase_type'];
        $purchaseM->sender_addr = $param['sender_addr'];
        $purchaseM->receive_addr = $param['receive_addr'];
        $purchaseM->total_amount = $param['total_amount'];
        $purchaseM->tx_hash = $param['tx_hash'];
        $purchaseM->created_by = $param['operator'];
        $purchaseM->last_modified_by = $param['operator'];
        $save = $purchaseM->add();

        if ($save) {
            //写log表
            $w =array(
                'purchase_id' => array('eq',$purchase_id)
            );
            $data = $purchaseM->where($w)->find();
            unset($data['id']);
            $purchaseM = M('TPurchaseStockLog');
            $purchaseM->data($data)->add();

        } else {
            E("创建进货单失败", -1);
        }
        return $purchase_id;
    }

    /**
     * 查询近5单进货单成本中最高的一条
     * @param
     * currency_code    币种
     * @return
     * res_top_price
     */
    public function get_top_cost_price($param)
    {
        $res_top_price = 0.00;

        //$sql = 'SELECT * FROM cszb_bank_system.t_purchase_stock where remain_num > 0 order by created_date desc limit 5';
        $purchaseM = M('TPurchaseStock');
        $w['currency_code'] = array('eq', $param['currency_code']);
        $w['remain_num'] = array('gt', 0);
        $res = $purchaseM->where($w)->page(0 . ',' . 5)->order('created_date desc')->select();

        foreach ($res as $row) {
            if ($row['curr_price'] > $res_top_price) {
                $res_top_price = $row['curr_price'];
            }
        }

        return array('res_top_price' => $res_top_price);
    }

    /**
     * 扣减结算平台库存数量
     * @param
     * deduct_num       扣币数量
     * currency_code    币种
     * trade_no         交易单号
     * operator         操作人
     * @return
     */
    public function deduct_num($param)
    {
        //校验该交易单号是否存在
        $purchaselogM = M('TPurchaseStockLog');
        $w = array(
            'trade_no' => array('eq', $param['trade_no'])
        );
        $ret_list = $purchaselogM->where($w)->select();
        if (count($ret_list) > 0) {
            ExceptionUtils::throwsEx(BankSystemErrorCode::$PURCHASE_TRADE_NO_EXIST_ERROR);
        }

        //校验数量
        $numRes = self::get_remain_num(array('currency_code' => $param['currency_code']));
        if ($param['deduct_num'] > $numRes) {
            ExceptionUtils::throwsEx(BankSystemErrorCode::$PURCHASE_NUM_NOT_ENOUGTH_ERROR);
        }

        $tmp_deduct_num = $param['deduct_num'];
        while ($tmp_deduct_num > 0) {
            //查出remain不为0最老的一条
            $purchaseM = M('TPurchaseStock');
            $w = array(
                'currency_code' => array('eq', $param['currency_code']),
                'remain_num' => array('gt', 0),
                '_logic' => 'AND'
            );
            $ret = $purchaseM->where($w)->order('created_date asc')->find();

            if ($tmp_deduct_num > $ret['remain_num']) {
                //扣除数量update remain = 0
                self::update_deduct_num(array(
                    'id' => $ret['id'],
                    'remain_num' => 0,
                    'trade_no' => $param['trade_no'],
                    'last_modified_by' => $param['operator']
                ), $purchaseM);

                $tmp_deduct_num = bcsub($tmp_deduct_num, $ret['remain_num'], 8);

            } else {
                //扣除数量update remain = $ret['remain_num']-$tmp_deduct_num
                self::update_deduct_num(array(
                    'id' => $ret['id'],
                    'remain_num' => $ret['remain_num'] - $tmp_deduct_num,
                    'trade_no' => $param['trade_no'],
                    'last_modified_by' => $param['operator']
                ), $purchaseM);

                break;
            }

        }

        return true;
    }

    /**内部sql封装，不要调用
     * @param $param
     * @param $purchaseM
     */
    private function update_deduct_num($param, $purchaseM)
    {

        $data['remain_num'] = $param['remain_num'];
        $data['trade_no'] = $param['trade_no'];
        $data['last_modified_by'] = $param['last_modified_by'];
        $data['last_modified_date'] = date('Y-m-d H:i:s', time());
        $save = $purchaseM->where('id=' . $param['id'])->save($data);
        if ($save) {
            //写log表
            $data = $purchaseM->where('id=' . $param['id'])->find();
            $purchaseM = M('TPurchaseStockLog');
            unset($data['id']);
            $purchaseM->data($data)->add();
        } else {
            //exception
            E('update_deduct_num更新失败', -1);
        }
    }

    /**
     * 查询待销售的剩余总数量
     * @param
     * currency_code    币种
     * @return
     * total_num              该币种属于结算平台的剩余数量
     */
    public function get_remain_num($param)
    {
        $return = array('total_num' => 0);

        $purchaseM = M('TPurchaseStock');
        $w['currency_code'] = array('eq', $param['currency_code']);
        $w['remain_num'] = array('gt', 0);
        $w['_logic'] = 'AND';
        $ret_list = $purchaseM->where($w)->order('created_date desc')->select();

        foreach ($ret_list as $row) {
            $return['total_num'] = bcadd($return['total_num'], $row['remain_num'],8);
        }

        return $return;
    }

}