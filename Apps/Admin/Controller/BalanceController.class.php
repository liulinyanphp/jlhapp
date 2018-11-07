<?php
namespace Admin\Controller;



class BalanceController extends AdminBaseController {

    public function __Construct()
	{
		parent::__construct();
	}

    /**
     * 条件查询
     */
    public function listAction()
    {
        $pageNow = I('p',1);
        $pageSize = 10;
        $where['id'] = array('gt',0);
        if(I('plat_code'))
        {
            $where['plat_code'] = array('eq',trim(I('plat_code')));
        }
        if(I('user_addr')){
            $where['user_addr'] = array('like','%'.I('user_addr').'%');
        }
        if(I('uid')){
            $where['uid'] = array('eq',I('uid'));
        }
        $balanceM = M('TUserAddressBalance');
        $field = 'uid,plat_code,user_addr,currency_code, user_total_num,user_address_num,freeze_num,last_freeze_type,created_by,created_date,last_modified_by,last_modified_date';
        $count = $balanceM->where($where)->count();
        $pageNow = ( $pageNow >$count )  ? $count : ( $pageNow < 1 ? 1: $pageNow );
        $data = $balanceM->field($field)->where($where)->page($pageNow .','. $pageSize)->order('id desc')->select();
        $Page = new  \Think\AdminPage ( $count, $pageSize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('balaceList',$data);
        $this->display();
    }


    /**
     * 模糊查询
     */
    public function query_listAction()
    {

        $pageNow = I('p',1);
        $limitRows = 10;

        $w = array();

        $platcode = I('plat_code',"");
        if(! $platcode == ""){
            $w['plat_code'] = array('eq',$platcode);

        }

        $userAddr = I('user_addr',"");
        if(! $userAddr == ""){
            $w['user_addr'] = array('like','%'.$userAddr.'%');

        }

        $currencyCode = I('currency_code',"");
        if(! $currencyCode == ""){
            $w['currency_code'] = array('eq',$currencyCode);

        }

        $field = 'uid,plat_code,user_addr,currency_code, user_total_num,user_address_num,freeze_num,last_freeze_type,created_by,created_date,last_modified_by,last_modified_date';
        $balanceList= D('UserAddressBalance')-> getbalancepagerlist($field, $w, $pageNow, $limitRows);
        $count =D('UserAddressBalance')->getCount();

        $Page = new  \Think\AdminPage ( $count, $limitRows, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('pager',$show);
        $this->assign('balaceList',$balanceList);
        $this->display();
    }

    /**
     * 条件查询
     */
    public function logListAction()
    {
        $pageNow = I('p',1);
        $pageSize = 10;
        $addr = I('addr');
        if(empty($addr))
        {
            $this->redirect('list');eixt;
        }
        $where['user_addr'] = array('eq',$addr);
        $balanceLogM = M('TUserAddressBalanceLog');
        $field = 'uid,plat_code,user_addr,currency_code, user_total_num,user_address_num,freeze_num,last_freeze_type,created_by,created_date,last_modified_by,mark';
        $count = $balanceLogM->where($where)->count();
        $pageNow = ( $pageNow >$count )  ? $count : ( $pageNow < 1 ? 1: $pageNow );
        $data = $balanceLogM->field($field)->where($where)->page($pageNow .','. $pageSize)->order('id asc')->select();
        $Page = new  \Think\AdminPage ( $count, $pageSize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('balaceLogList',$data);
        $this->display('logList');
    }
}