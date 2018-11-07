<?php
/**
 * Created by PhpStorm.
 * User: tang
 * Date: 2018/4/27
 * Time: 下午11:42
 */

namespace Common\Utils;


class SeqNoUtils{

    const SH = 'SH';
    const PUR = 'PUR';
    const TX = 'TX';

    private static $m_types = array(
        self::SH    => 1, //提币批次号
        self::PUR   => 2, //进货单号
        self::TX    => 3, //热转冷归集交易号
    );

    public static function seq_no_gen($type){

        if (!array_key_exists($type, self::$m_types)) {
            E('不存在的type',-1);
        }

        $id_num = 1;
        //先查一下有没有这个type，如果没有就新增一条，m_seq_no设为一

        $seqnoM = M('TSeqNo');
        $w['type'] = array('eq', $type);
        $ret = $seqnoM->where($w)->find();
        if(empty($ret)){
            $data = array(
                'type' => $type,
                'total_num' => $id_num
            );
            $seqnoM->data($data)->add();
        }else{
            //如果存在，取出total_num 并+1，
            $id_num = $ret['total_num'] + 1;
            $date = array(
                'total_num' => $id_num
            );
            $seqnoM->where($w)->save($date);
        }

        $date = date("Ymd");
        return sprintf("%s%s%07d", $type, $date, $id_num % 10000000);
    }

}