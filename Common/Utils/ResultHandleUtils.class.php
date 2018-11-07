<?php
namespace Common\Utils;

class ResultHandleUtils {


    /**
     * 内部接口错误返回
     * @param $errorCode
     * @return array
     */
    public static function makeErrorRet($errorCode){

        return array('result'=>$errorCode[0],'res_info'=>$errorCode[1],'result_rows'=>array());
    }

    /**内部接口正确返回
     * @param $errorCode
     * @param array $resultRows
     * @return array
     */
    public static function makeSucRet(){

        return array('result'=>'0','res_info'=>'ok', 'result_rows'=>array());
    }

    /**
     * 构造外部接口错误返回
     * @param $errorCode
     * @return array
     */
    public static function makeOutbizErrorRet($errorCode){

        return array('status'=>$errorCode[0],'message'=>$errorCode[1],'data'=>array());
    }


    public static function makeOutbizErrorTipRet($errorCode, $tips){

        return array('status'=>$errorCode[0],'message'=>$tips.$errorCode[1],'data'=>array());
    }

    /**
     * 构造外部接口正确返回
     * @param array $data
     * @return array
     */
    public static function makeOutbizSucRet($data=array()){

        return array('status'=>'0','message'=>'success', 'data'=>$data);
    }


    public static function makeOutbizExRet($e){

        return array('status'=>$e->getCode(),'message'=>$e->getMessage(),'data'=>array());
    }


}