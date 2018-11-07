<?php
namespace Home\Controller;
use Think\Controller;

use Common\Service\UtilService;


class TokenController extends Controller {

    public $basebi_arr = array(
        'ETH'=>'ethereum',
        'BTC'=>'bitcoin',
        'USDT'=>'tether'
    );

    public function getPriceAction()
    {
        $this->_getprice();
    }

    /**
     * addby : jamesliu
     * used : 获取币价获取配置信息
     */
    private function _getpriceConfig()
    {
        $priceCfg = M('TProGetpriceConfig');
        $w['a.is_deleted'] = array('eq',0);
        $w['b.is_deleted'] = array('eq',0);
        $fields= 'a.pro_rand_code,a.pro_alias_name,a.pro_price_getsource,b.name,b.crowd_fi_unit';
        $dataInfo = $priceCfg->alias('a')->field($fields)->join('t_project as b on a.pro_rand_code=b.rand_code',inner)->where($w)->select();
        return $dataInfo;
    }

    private function _getprice()
    {
        $sourceData = $this->_getpriceConfig();
        //print_r($sourceData);die();
        if(empty($sourceData)) return '';

        //定义一个变量用来存储基础代币的价格 如: array('USDT'=>6);
        $baseBiPrice = array();
        //定义一个变量用来存储基础代币价格是否已经获取，避免重复获取
        $ckbaseBi = array();
        $priceM = M('TProPricelist');
        foreach($sourceData as $k=>$obj)
        {
            $sourceName = $obj['pro_price_getsource'];
            //配置的获取价格的别名
            $tokenName = $obj['pro_alias_name'];
            //项目的众筹单位
            $crowdFiUnit = $obj['crowd_fi_unit'];
            //众筹单位获取价格的别名
            $baseToken = $this->basebi_arr[$crowdFiUnit];
            if(!in_array($baseToken,$ckbaseBi))
            {
                $baseBiPrice[$crowdFiUnit] = $this->_get_price_byfeixiaohao($baseToken);
            }
            if($sourceName=='FEIXIAOJAO'){
                $price = $this->_get_price_byfeixiaohao($tokenName);
            }
            if($sourceName == 'AICOIN'){
                $price = $this->_get_price_byaicoin($tokenName);
            }
            $insert = '';
            //如果基础币价和token币价都获取到了就存进去
            if($baseBiPrice[$crowdFiUnit] != 0 && $price !=0)
            {
                $insert['pro_rand_code'] = $obj['pro_rand_code'];
                $insert['pro_alias_name'] = $obj['pro_alias_name'];
                $insert['pro_name'] = $obj['name'];
                //项目众筹单位
                $insert['pro_basebi_name'] = $obj['crowd_fi_unit'];
                //token价格
                $insert['pro_bi_price'] = $price;
                //众筹所用当前人名币价格
                $insert['crowd_fi_unit_price'] = $baseBiPrice[$crowdFiUnit];
                $insert['pro_price_getsource'] = $obj['pro_price_getsource'];
                $insert['created_by'] = session('username');
                $insert['last_modified_by'] = session('username');
                $insert['created_by'] = 'system';
                $insert['last_modified_by'] = 'system';
                $priceM->data($insert)->add();
            }
            unset($sourceData[$k]);
        }

        //基础币的价格直接去非小号拿算了
        //        $insert['pro_rand_code'] = $dataInfo['pro_rand_code'];
        //        $insert['pro_alias_name'] = $dataInfo['pro_alias_name'];
        //        $insert['pro_name'] = $dataInfo['name'];
        //        //项目众筹单位
        //        $insert['pro_basebi_name'] = $dataInfo['crowd_fi_unit'];
        //        //token价格
        //        $insert['pro_bi_price'] = '';
        //        //众筹所用当前人名币价格
        //        $insert['crowd_fi_unit_price'] = '';
        //        $insert['pro_price_getsource'] = $dataInfo['pro_price_getsource'];
        //        $insert['created_by'] = session('username');
        //        $insert['last_modified_by'] = session('username');
        //        print_r($insert);
    }



    /**
     * @param string $token_name 获取价格的别名
     * @return array
     */
    //币的获取
    //从非小号默认获取ETH的价格
    private function _get_price_byfeixiaohao($token_name='ethereum')
    {
        layout(false);
        if($token_name==''){
            return '';
        }
        $logdir = ROOT.'/bilog';
        //瑞波ripple
        $source_url = 'https://www.feixiaohao.com/currencies/'.$token_name.'/';
        $curl_service = new UtilService();
        $info = $curl_service->curl_get_http($source_url);
        $filaName = $logdir.'/'.$token_name.'_'.date('His').'.html';
        file_put_contents($filaName,$info);
        $res = 0 ;
        if(file_exists($filaName)){
            $str = file_get_contents($filaName);
            //正则出显示价格的那个地方
            $div ='/<div class=coinprice>(.*?)<\/div>/ism';
            preg_match_all($div,$str,$linearr);
            //获取出div包含的涨幅的span
            $span = '/<span class=tags.*?>(.*?)<\/span>/ism';
            preg_match_all($span,$linearr[0][0],$spanarr);
            //去掉涨幅
            $new_str = str_replace($spanarr[0][0],'', $linearr[0][0]);
            //去掉标签获取到值
            $strinfo = strip_tags($new_str);
            //去掉货币符号,和字符分隔符
            $strinfo = str_replace('￥','',$strinfo);
            $strinfo = str_replace(',','',$strinfo);
            if(!empty($strinfo)){
                $res = $strinfo;
            }
        }
        return $res;
    }

    /**
     * addby : lly
     * date : 2018-03-28 20:46
     * used : 从aicon获取价格
     **/
    private function _get_price_byaicoin($token_name='')
    {
        layout(false);
        if($token_name == '')
        {
            return '';
        }
        $res = 0;
        //$curlurl = "https://www.aicoin.net.cn/api/value/getDetail?key=$biname";
        $curlurl = "https://www.aicoin.net.cn/api/coin-profile/index?coin_type=".$token_name."&currency=cny";
        $referurl = "https://www.aicoin.net.cn/currencies";
        $curl_service = new UtilService();
        $info = $curl_service->moni_brower_curl($curlurl,$referurl);
        $result = json_decode($info,true);
        $price = $result['global']['last_cny'];
        if($price!=''){
            $price = str_replace('￥','',$price);
            $res = $price;
        }
        return $res;

    }
}