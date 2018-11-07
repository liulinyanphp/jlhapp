<?php
/**
 * Created by PhpStorm.
 * User: tang
 * Date: 2018/4/24
 * Time: 下午10:37
 */

namespace  Common\Service;

class BankSystemService{


    public function query_user_coin_available_num($param){
        try{
            $s = new UserAddressBalanceService();
            $res = $s->query_user_coin_available_num($param);
            return array('result' => 0,'res_info' => 'ok','result_rows' => $res);

        }catch(\Exception $e) {
            $output['result'] = $e->getCode();
            $output['res_info'] = $e->getMessage();
            print_r($output);
            return $output;
        }

    }


    public function update_test($param){

        $trans = M();
        $trans->startTrans();   // 开启事务
        try{
            $s = new UserAddressBalanceService();
            $res = $s->test_transaction($param);
            $trans->commit();
            return array('result' => 0,'res_info' => 'ok','result_rows' => $res);

        }catch(\Exception $e) {
            $trans->rollback();
            $output['result'] = $e->getCode();
            $output['res_info'] = $e->getMessage();
            print_r($output);
            return $output;
        }

    }

}