<?php
namespace Admin\Controller;

use Common\Conf\BaseConfig;

class DepositController extends AdminBaseController {

    /**
     * 条件查询
     */
    public function listAction()
    {
        $pageNow = I('p',1);
        $pageSize = 10;
        $where['id'] = array('gt',0);
        if(I('platName')){
            $where['plat_code'] = array('eq',trim(I('platName')));
        }
        if(I('uid')){
            $where['uid'] = array('eq',trim(I('uid')));
        }
        if(I('userAddr')){
            $where['user_addr'] = array('eq',I('userAddr'));
        }
        if(I('status') && I('status')!='ALL'){
            $where['status'] = array('eq',I('status'));
        }
        $withdrawM = M('TDepositCoinRecord');
        $sending_addr = "concat(substring(sending_addr,1,3),'***',substring(sending_addr,LENGTH(sending_addr)-3,4)) as 'sending_addr' ";
        $user_addr = "concat(substring(user_addr,1,3),'***',substring(user_addr,LENGTH(user_addr)-3,4)) as 'user_addr' ";
        $field = 'id,uid,plat_code,'.$sending_addr.','.$user_addr.',status,amount,currency_code,created_date,created_by';
        $count = $withdrawM->where($where)->count();
        $pageNow = ( $pageNow >$count )  ? $count : ( $pageNow < 1 ? 1: $pageNow );
        $data = $withdrawM->field($field)->where($where)->page($pageNow .','. $pageSize)->order('id desc')->select();
        $Page = new  \Think\AdminPage ( $count, $pageSize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('depositList',$data);
        $this->assign('div','style="text-align:left;"');
        $this->assign('DepositStatus',BaseConfig::$DepositStateSearch);
        $this->assign('param',I('get.'));
        $this->display();
    }

    /**
     * 查看详情
     */
    public function detailAction()
    {
        $id = I('id');
        $w['id'] = array('eq',$id);
        $data = M('TDepositCoinRecord')->where($w)->find();
        $data['status'] = BaseConfig::$DepositStateSearch[$data['status']];
        unset($data['id']);
        $this->ajaxReturn($data);
    }
}