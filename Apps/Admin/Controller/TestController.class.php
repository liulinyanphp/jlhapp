<?php
/**
 * Created by PhpStorm.
 * User: tang
 * Date: 2018/4/23
 * Time: 下午9:03
 */

namespace Admin\Controller;

use Think\Controller;
use Common\Service\DataService;
use Common\Service\UtilService;
use Org\Util\Rbac;
use Common\Service\PurchaseStockService;
use Common\Service\BankSystemService;
use Common\Service\UserAddressBalanceService;
use Common\utils\SeqNoUtils;
use Common\Service\PresentRecordService;

use Common\Conf\BaseConfig;

use Common\Utils\CommonUtils;

use Common\Utils\OmniUtils;

use Common\Service\OmniService;


use Common\Utils\ExceptionUtils;

use Common\Exception\TransactionErrorCode;

use Common\Utils\ExloggerUtils;

use Common\Shell\RecoveryCoinToPurchaseJob;

use Common\Shell\UserAddrCoinCollectJob;

use Common\Shell\UserAddrCoinCollectStatusJob;

use Common\Shell\HotToColdJob;

use Common\Service\AddressService;

use Common\Utils\SysOptionUtils;



class TestController extends Controller {


    public function testAction()
    {


        $r = bcdiv("27", "6.30", 8);
        die();



        $html = file_get_contents('https://www.feixiaohao.com/');

//        $myfile = fopen("/Users/tang/project/test/newfile.txt", "w");// or die("Unable to open file!");
//        fwrite($myfile, $html);
//        die();

        //$a = 'data-cny=';
        //$b = 'data-btc=';
        //$pattern = "/^".$a.".*".$b."$/";
        //$html ='<a href="/currencies/bitcoin/#markets" target="_blank" class="price" data-usd="6675" data-cny="43233" data-btc="1">¥43,233</a>';

        //$html ='<a href=/currencies/bitcoin/#markets target=_blank class=price data-usd=6669 data-cny=43196 data-btc=1>¥43,196</a>';

        $part = '/<a href=\/currencies\/bitcoin\/#markets target=_blank class=price (.*?)<\/a>/ism';
        preg_match($part, $html, $match);

        print_r($match);


        $s = strip_tags($match[0]);
        echo $s;
        $tmp=str_replace(',','',$s);
        $tmp=str_replace('¥','',$tmp);
        echo $tmp;

        echo $html;die();

        $sing = md5('collectstatusJob');
        die();

//        $omniClient = OmniService::getInstance();
//
//        $ret = $omniClient->estimatefee(2);
//        die();

        //测试交易状态查询脚本
        $s = new UserAddrCoinCollectStatusJob();
        $s->check_collect_finish();
        die();


//        SysOptionUtils::set_option('HotToCold_max_limit_balance', 5010, 'sys');
//        SysOptionUtils::set_option('HotToCold_min_limit_balance', 4990, 'sys');
//        die();


//        $ret = OmniService::getInstance()->getnewaddress();
//        die();

        //测试热转冷脚本
        $s = new HotToColdJob();
        $s->collect_coin_form_hot_to_cold(array());
        die();


//        $ret =  OmniService::getInstance()->listunspent(2, 9999999, array('mgvHWHEybkNobHvDPuEDDgbKengmwYcWzX'));
//        print_r($ret);
//        die();

        //归集脚本测试

//        $s = new UserAddrCoinCollectJob();
//        $r = $s->collect_usdt_to_wallet(array());die();

        //回收脚本测试

//        $s = new RecoveryCoinToPurchase();
//        $r = $s->recovery_coin_to_purchase(array());die();



//        $CoinCollectRecordM = M('TCoinCollectRecord');
//        $CoinCollectRecordM->uid = '1002';
//        $CoinCollectRecordM->plat_code = 'HXYL';
//        $CoinCollectRecordM->currency_code = 'USDT';
//        $CoinCollectRecordM->send_addr = 'mhQSk7YQ1Y4E73H6S8FnuHfgZdKA7QnU7g';
//        $CoinCollectRecordM->receive_addr = 'msVAsJLggzTj5j4uaCNfzwmoQkFQWAAEN6';
//        $CoinCollectRecordM->amount = '1000.00000000';
//        $CoinCollectRecordM->trans_type = 'collect';
//        $CoinCollectRecordM->status = 'CollectTransSend';
//        $CoinCollectRecordM->tx_hash = '46ad157216aa8fb1c6435e18f45a6a6e7d424c79444f53ca3ffdc2a0de13eb27';
////                    $CoinCollectRecordM->operator_id = $row['uid'];
////                    $CoinCollectRecordM->operator_name = $row['uid'];
//        $CoinCollectRecordM->created_by = 'collect_usdt_to_wallet';
//        $CoinCollectRecordM->last_modified_by = 'collect_usdt_to_wallet';
//        $save = $CoinCollectRecordM->add();
//        print_r($save);
//        die();
//
//
//        $trans = M();
//        $trans->startTrans();
//
//        $userAddressBalanceM = M('TUserAddressBalance');
//
//        $user_balance = $userAddressBalanceM->lock(true)->where('id=1002')->find();
//        echo  $userAddressBalanceM->_sql();
//
//
//
//        //print_r($sql);
//        echo '<br>';
//        $trans->commit();
//        die();


//        $ret = OmniService::getInstance()->omni_gettransaction('b0e61c6106261bf652aceec6cf07c8efd9444d6be27ed655adc0fa3dc91d7e7f');
//        print_r($ret);
//        echo '<br>';
//        die();

//
//        $ret = OmniService::getInstance()->omni_getbalance('miKJF14iWYrmkbC8rkYRj6VVP4LYMf2Ta1',2147484815);
//        print_r($ret);
//        echo '<br>';
//        die();


//        $ret = OmniService::getInstance()->omni_getallbalancesforaddress('mhQSk7YQ1Y4E73H6S8FnuHfgZdKA7QnU7g');
//        print_r($ret);
//        echo '<br>';
//        die();



//        $ret = OmniService::getInstance()->getnewaddress();
//        print_r($ret);
//        echo '<br>';
//        die();


//        $ret = OmniService::getInstance()->getaccount('msVAsJLggzTj5j4uaCNfzwmoQkFQWAAEN6');
//        print_r($ret);
//        echo '<br>';
//
//
//        $ret = OmniService::getInstance()->listunspent(0,99999,array('miKJF14iWYrmkbC8rkYRj6VVP4LYMf2Ta1'));
//        print_r($ret);
//        echo '<br>';die();

//
//

//        //$ret = OmniService::getInstance()->omni_getbalance('msVAsJLggzTj5j4uaCNfzwmoQkFQWAAEN6', 2147484815);
//        $ret = OmniUtils::getTokenBalance('msVAsJLggzTj5j4uaCNfzwmoQkFQWAAEN6', 2147484815);
//        print_r($ret);
//        echo '<br>';die();

//
//
//        $ret = OmniService::getInstance()->getbalance('',2);
//        print_r($ret);
//        echo '<br>';
//        die();
//
//
//
//
//
//        $ret = OmniUtils::getBestBlockhash();
//        print_r($ret);
//        die();



//        $ret = OmniService::getInstance()->listunspent(0,99999,array('msVAsJLggzTj5j4uaCNfzwmoQkFQWAAEN6'));
//        print_r($ret);
//        echo '<br>';die();



//        $ret = OmniUtils::getTransInfo('46ad157216aa8fb1c6435e18f45a6a6e7d424c79444f53ca3ffdc2a0de13eb27');
//        print_r($ret);
//        echo '<br>';
//        die();



//        $ret = OmniService::getInstance()->omni_gettransaction('46ad157216aa8fb1c6435e18f45a6a6e7d424c79444f53ca3ffdc2a0de13eb27');
//        print_r($ret);
//        echo '<br>';die();



//        $ret1 = OmniService::getInstance()->listunspent(0,99999,array('mhQSk7YQ1Y4E73H6S8FnuHfgZdKA7QnU7g'));
//        print_r($ret1);
//        echo '<br>';
//        $utxoArr = array();
//        array_push($utxoArr, $ret1['result_rows'][0]);
//
//
//        $ret2 = OmniService::getInstance()->listunspent(0,99999,array('msVAsJLggzTj5j4uaCNfzwmoQkFQWAAEN6'));
//        print_r($ret2);
//        echo '<br>';
//        array_push($utxoArr, $ret2['result_rows'][0]);
//
//
//        $feeutxoarr = array();
//        foreach($utxoArr as $utxo){
//            $res123['txid']=$utxo['txid'];
//            $res123['vout']=$utxo['vout'];
//            $res123['scriptPubKey']=$utxo['scriptPubKey'];
//            $res123['value']=$utxo['amount'];
//            array_push($feeutxoarr, $res123);
//        }
//
//


        $propertyid = 2147484815;
        $btc = 0;


        $hotaddress = 'msVAsJLggzTj5j4uaCNfzwmoQkFQWAAEN6';

        $useraddress = 'mhQSk7YQ1Y4E73H6S8FnuHfgZdKA7QnU7g';

        $receiveaddress = 'mjQvv7GGgcH1VJsdPw3pBHt26aB9kKEr44';

        $useraddress2 = 'mgvHWHEybkNobHvDPuEDDgbKengmwYcWzX';

        $useraddress3 = 'moFben74zLKd1NT6syAEANRHNLmpooJjU4';


        $ret111 = OmniService::getInstance()->omni_getbalance($hotaddress,2147484815);
        print_r($ret111);
        echo '<br>';

        $ret222 = OmniService::getInstance()->omni_getbalance($useraddress,2147484815);
        print_r($ret222);
        echo '<br>';

        $ret = OmniService::getInstance()->omni_getbalance($useraddress3,2147484815);

        $amount = '10';

        $ret = OmniService::getInstance()->listunspent(2,99999,array($hotaddress));

        $utxoArr = array();
        array_push($utxoArr, $ret['result_rows'][1]);


//        $utxoArr = array(
//            array(
//                'txid' => '436e2822616ebe9782ea32ac364ca7c97d282f943842258ce11dd061b2e7fff5',
//                'vout' => 2,
//                'scriptPubKey' => '76a914834b2c4dd6bb98580faa291a4cb9e400a5b3d6c688ac',
//                'amount' => 0.00000546
//            )
//        );



        try{
            $txhash = OmniUtils::sendTransaction($hotaddress, $useraddress, $propertyid, $amount, $utxoArr);

            print_r('txhash:'.$txhash);

        }catch (\Exception $e){
            $output = array(
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
                'data' => array()
            );
            echo '<br>';
            print_r($output);
        }

        echo '<br>';
        print_r('txhash：'.$txhash);

        die();

//
//
//        $data = M('TUserAddressBalance')->select();
//
//        $tt1 = $data[0]['user_total_num'];
//        $tt = bcadd($tt1,$tt1,8);
//
//        echo $tt;die();
//
//
//        $left =  1231231.12312345;
//        $right = 1231231.23232209;
//
//
//        //print_r($add1 + $add2);echo '<br>';
//
//
//        echo bcadd($left.'',$right.'',8);
//
//        die();
//

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
        $s = new PresentRecordService();
        $s->present_record_pay_notice(array(
            'plat_code' => 'HXYL',
            'trade_no' => 'tradeno201805250001',
            'pay_bank' => '招商银行',
            'pay_bank_card_no' => '6217007200001989',
            'pay_amout' => '12.86',
            'bank_trans_no' => '123123123123131123123',
            'payment_time' => date(time()),
            'operator_id' => '0123',
            'operator_name' => 'cob',
        ));die();


//        /**业务人员驳回提现单
//         * @param $param
//         * plat_code    平台编号
//         * trade_no     提现单号
//         * mark         驳回原因
//         * operator     操作人
//         * @return
//         */
//        $s = new PresentRecordService();
//        $s->present_record_reject(array(
//            'plat_code' => 'huobi',
//            'trade_no' => 'tixian123400001',
//            'mark' => '审核未通过驳回提现单',
//            'operator' => 'cob'
//        ));die();
//
//
//        /**
//         * plat_code        平台编号
//         * uid              用户id
//         * currency_code    币种编号
//         * add_num          增加的币数量
//         * last_modified_by 操作人
//         * mark             备注
//         */
//        $s = new UserAddressBalanceService();
//        $s->user_coin_add(array(
//            'plat_code' => 'HXYL',
//            'uid' => '90001',
//            'currency_code' => 'USDT',
//            'add_num' => '100.00000000',
//            'last_modified_by' => 'cob',
//            'mark' => '用户赢币'
//        ));die();
//
//
//
//
//        echo 'qweqweqweqweqe';
//        /**
//         * currency_code    币种
//         * purchase_num     进货数量
//         * curr_price       进货价格
//         * sender_addr      发出地址
//         * receive_addr     收货地址
//         * total_amount     总金额
//         * tx_hash          交易哈希
//         * operator         操作人
//         */
//
//        $s = new PurchaseStockService();
//        $s->purchase_create(array(
//            'currency_code' => 'USDT',
//            'purchase_num' => '100',
//            'curr_price' => '6.29',
//            'sender_addr' => 'qwosjhdfuyoqwieudherq',
//            'receive_addr' => 'aksjhdgkajshdfgasdfjg',
//            'total_amount' => '629',
//            'tx_hash' => '',
//            'operator' => 'cob'
//
//
//        ));
//
//
//
//        die();
//
//
//        $a = CommonUtils::covertNum('1231321',4);
//
//        var_dump($a);
//        echo $a;
//
//
//        die();
//
//
//
//        $num = '1231.62543623';
//        $r = sprintf("%.2f",$num);
//        echo $r;
//        echo '<br>';
//
//        $a = floor($num);
//        echo $a;
//        echo '<br>';
//
//        echo sprintf("%.2f",substr(sprintf("%.3f", $num), 0, -2));
//        echo '<br>';
//
//
//        die();
//
//
//
//        print_r("---------start<br>\n");


        /**
         * trade_no		    string		交易单号
         * uid				string		用户id
         * plat_code		string		平台编码        校验是否存在该平台
         * real_name		string		用户姓名
         * bank  			string		银行类型
         * branch_bank		string		开户支行
         * bank_card_no 	string		银行卡号
         * currency_code    string      提现币种
         * sell_num		    string		提现个数        校验个数小数点后四位
         * curr_price		string		提现单价
         * apply_amount     string      提现金额
         * apply_time		int 		申请时间
         */
        $s = new PresentRecordService();
        $s->present_record_create(array(
            'tradeId' => 'tradeno201805250001',
            'userId' => '10001',
            'platformCode' => 'HXYL',
            'userName' => '张三',
            'bankName' => '中国银行',
            'branchBankName' => '中国银行北京支行',
            'bankCardNo' => '6222222200002222000',
            'currencyName' => 'USDT',
            'applyNum' => 1.00000000,
            'unitPrice' => '6.10',
            'totalAmount' => '6.10',
            'apply_time' => 1527242103,
        ));die();



        /**
         * plat_code        平台编号
         * uid              用户id
         * currency_code    币种编号
         * freeze_num       冻结数量
         * freeze_type      冻结类型
         * mark             备注
         */

        $s = new UserAddressBalanceService();

        $s->user_coin_freeze(array(


            'plat_code' => 'huobi',
            'uid' => 'huobi001',
            'currency_code' => 'usdt',
            'freeze_num' => 2.00000000,
            'freeze_type' => BaseConfig::FreezeTypeWithdrawDeposit,
            'mark' => '用户提现冻结',
            'trade_no' => 'tradeno121210000005',
            'last_modified_by' => 'cob123'
        ));


        /**
         * plat_code        平台编号
         * uid              用户id
         * currency_code    币种编号
         * unfreeze_num     解冻数量
         * mark             备注
         * trade_no         交易单号
         */
//        $s->user_coin_unfreeze(array(
//            'plat_code' => 'huobi',
//            'uid' => 'huobi001',
//            'currency_code' => 'usdt',
//            'unfreeze_num' => 2.00000000,
//            'mark' => '交易单取消解冻',
//            'trade_no' => 'tradeno121210000004',
//            'last_modified_by' => 'cob123'
//        ));die();


        /**
         * plat_code        平台编号
         * uid              用户id
         * currency_code    币种编号
         * deduct_num       扣除数量
         */
        $s->user_coin_deduct(array(
            'plat_code' => 'huobi',
            'uid' => 'huobi001',
            'currency_code' => 'usdt',
            'deduct_num' => 2.00000000,
            'mark' => '用户提现扣减数量',
            'trade_no' => 'tradeno121210000005',
            'last_modified_by' => 'cob123'
        ));


        print_r("<br>---------end<br>\n");

        die();

        print_r("---------start<br>\n");
        $s = new PurchaseStockService();

        $ret = $s->get_top_cost_price(array('currency_code'=>'usdt'));
        print_r($ret);
        print_r("<br>");

        $ret = $s->get_remain_num(array('currency_code'=>'usdt'));
        print_r($ret);
        print_r("<br>");



//        $s->update_deduct_num(array(
//            'id' => 1,
//            'remain_num' => 110,
//            'trade_id' => 2332332333,
//            'last_modified_by' => 'coooooooo'
//        ),M('TPurchaseStock'));


        print_r("<br>---------end<br>\n");

        print_r("<br>---------end123123<br>\n");


        \Think\Log::record('测试日志信息123123123','INFO');


        $s = new SeqNoUtils();
        $str = $s->seq_no_gen('PUR');
        echo $str;

    }

    public function testuserAction(){

        $param = array();
        $param['remain_num'] = 222.22;
        $param['id'] = 1001;
        $s = new BankSystemService();
        $s->update_test($param);

    }

    /**
     * 预估交易费
     * @param $signedHexStr
     * @return int|stringsssssssss
     */
    public static function estimateFee($signedHexStr){

        $nblock = 6;
        $omniClient = OmniService::getInstance();

        $ret = $omniClient->estimatefee($nblock);

        if($ret['result']== '0' && !empty($ret['result_rows'])){

            $estimateFee = number_format($ret['result_rows'], 8);

            $byteNum = strlen($signedHexStr)/2;

            $w = bcdiv($estimateFee.'' ,'1000',11);

            return bcmul($w.'', $byteNum.'',8);
        }else{
            return 0;
        }

    }


}