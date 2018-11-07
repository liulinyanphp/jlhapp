<?php
/**
 * 键值配置表
 * User: tang
 * Date: 2018/5/28
 * Time: 下午6:08
 */
namespace Common\Utils;

class SysOptionUtils{

    /**S设置配置key-value
     * @param $key
     * @param $value
     * @return bool
     */
    public static function set_option($key, $value, $operator){
        $sysoptionM = M('TSysOption');
        $w = array(
            'key' => array('eq', $key)
        );
        $r = $sysoptionM->where($w)->find();
        if(empty($r)){

            $sysoptionM->create();
            $data = array(
                'key' => $key,
                'value' => $value,
                'created_by' => $operator,
                'last_modified_by' => $operator
            );
//            $sysoptionM->create();
//            $sysoptionM->key = $key;
//            $sysoptionM->value = $value;
//            $sysoptionM->created_by = $operator;
//            $sysoptionM->last_modified_by = $operator;

            $id = $sysoptionM->id;
            $save = $sysoptionM->data($data)->add();
            if($save){
                self::log($key);
                return true;
            }else{
                return false;
            }
        }else{
            $data = array(
                'value' => $value,
                'last_modified_by' => $operator
            );
            $save = $sysoptionM->where($w)->save($data);
            if($save != 1){
                return false;
            }else{
                self::log($key);
                return true;
            }
        }

    }

    private static function log($key){
        $w = array(
            'key' => array('eq', $key)
        );
        $data = M('TSysOption')->where($w)->find();
        unset($data['id']);
        $purchaseM = M('TSysOptionLog');
        $purchaseM->data($data)->add();
    }

    /**
     * @param $key
     * @return null
     */
    public static function get_option($key){
        $result = null;

        $sysoptionM = M('TSysOption');
        $w = array(
            'key' => $key
        );
        $r = $sysoptionM->where($w)->find();
        if(!empty($r)){
            $result = $r['value'];
        }
        return $result;
    }

}