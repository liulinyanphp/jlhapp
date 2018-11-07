<?php

namespace Common\Utils;

class CommonUtils {


    /**
     * 验证地址是不是有效
     * @param $address
     * @return bool|mixed
     */
    public static function validateAddress($address) {
        if(isset($address)){
            if(strlen($address) != 34){
                return flase;
            }

            if(substr($address,1) != "1"){
                return flase;
            }
            return OmniUtils::validateBitcoinAddress($address);


        }

        return false;
    }

    /**
     * 判断字符串是不是数字
     * @param $numStr
     * @return bool
     */
    public static function  isNumber($numStr){

        return empty($numSrt) ? false : is_numeric($numStr);
    }


    /**
     * 判断字符串是不是正数
     * @param $numStr
     * @return bool
     */
    public static function  isPositiveNumber($numStr){
        
        return empty($numStr) ? false : (is_numeric($numStr) && strpos($numStr,'-')===false);
    }

    /**
     * 将字符串数字转换成指定精度的小数
     * @param $numStr
     * @param $accuracy 精确到小数点后几位
     */
    public static function covertNum($numStr, $accuracy=0){

        return empty($numStr) ? $numStr : bcdiv($numStr,1,$accuracy);
    }

    /**入参映射
     * @param $data
     * @param $map
     * @return array
     */
    public static function keyMap($data,$map)
    {
        $key_arr = $map;
        $return = array();
        foreach ($data as $key => $value) {
            if(isset($key_arr[$key])){
                $return[$key_arr[$key]] = $value;
            }
        }
        return $return;
    }



}