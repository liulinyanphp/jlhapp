<?php

namespace Common\Utils;
class ExceptionUtils {


    /**
     * 根据错误码抛异常
     * @param $errorCode
     * @throws \Think\Exception
     */
    public static function throwsEx($errorCode) {

         E($errorCode[1], $errorCode[0]);
    }

    /**抛带提示的异常
     * @param $errorCode
     * @param $tips
     * @throws \Think\Exception
     */
    public static function throwsExWithTips($errorCode, $tips) {

         E($tips.$errorCode[1], $errorCode[0]);
    }

}