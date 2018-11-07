<?php
namespace Admin\Controller;
use \Common\Conf\BaseConfig;
class AddressController extends AdminBaseController {

    /**
     * addby : lly
     * date : 2018-09-19 18:59
     * used : 钱包地址列表
    **/
    public function listAction()
    {
        $pageNow = I('p',1);
        $pageSize = 10;
        $address = I('address');
        $is_used = trim(I('is_used'));
        if(!empty($address)){
            $where['address']  = array('eq',$address);
        }
        if($is_used < 60) {
            $where['is_used']  = array('eq',$is_used);
        }
        $where['id'] = array('gt',0);
        $addressM = M('TEthAddress');
        $count = $addressM->where($where)->count();
        $pageNow = $pageNow>$count ? $count : ($pageNow<1 ? 1: $pageNow);
        $data = $addressM->where($where)->page($pageNow .','. $pageSize)->order('id desc')->select();
        $Page = new  \Think\AdminPage ( $count, $pageSize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('address_list',$data);
        $this->assign('IsUsedSearch',BaseConfig::$AddressUseStatusSearch);
        $this->assign('param',I('post.'));
        $this->display();
    }
}