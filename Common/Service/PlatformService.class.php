<?php
/**
 * addby : lly
 * date : 2018-04-27 15:40
 * used : 平台获取token的服务
 */
namespace Common\Service;
use Think\Model;
class PlatformService
{
    /**
     * 获取指定平台，指定币种的token
     * @param
     * plat_code    平台code
     * @return
     * string          token
     */
    public static function get_plat_token($plat_code='huobi')
    {
        $w['status'] = array('eq',1);
        $w['plat_code'] = array('eq',$plat_code);
        return M('TPlatform')->where($w)->getField('token');
    }

    public static function get_plat_code()
    {
        $w['status'] = array('eq',1);
        return M('TPlatform')->where($w)->getField('plat_code',true);
    }
}