<?php
namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;
use Common\Service\OutbizNotifyService;
class RateController extends AdminBaseController {

	private $_open_status = array('open','close');
	public function __Construct()
	{
		parent::__construct();
		$this->assign('platform',C('PLATFORM'));
    	$this->assign('basebi',C('BASEBI'));
	}

    /**
     * addby : lly
     * date : 2018-04-23 15:59
     * used : 汇率信息列表
    **/
    public function listAction()
    {
        $pageNow = I('p',1);
        $pagesize = 10;
        $where['id'] = array('gt',0);
        $rateM = D('Rate');
        $field = '*';
        $data = $rateM->getlist($field,$where,$pageNow,$pagesize);
        $count = $rateM->getcount($where);
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('pager',$show);
        $this->assign('rate_list',$data);
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-04-23 15:54
     * used : 充值汇率添加、充值汇率添加处理
    **/
    public function addAction()
    {
        if(IS_POST)
        {
            $result = array('status'=>0,'message'=>'汇率信息添加成功','data'=>'');
        	$rateM = D('Rate');
        	if($rateM->create())
        	{
                $rateM->created_by = session("username");
                $rateM->last_modified_by = session("username");
                try{
                    $res = $rateM->add();
                    if($res){
                        $rateM->insert_into_log($res);
                        $result['data'] = U('list');
                    }else{
                        $result['result'] = '-1';
                        $result['message'] = '汇率信息录入失败,未找到自增id';
                    }
                }catch(\Exception $e) {
                    $result['status'] = $e->getCode();
                    $result['message'] = '添加汇率信息失败'.$e->getMessage();
                }
        	} else{
                $result['status'] = '-1';
                $result['message'] = ' 参数出错: '.$rateM->getError();
            }
            $this->ajaxReturn($result);
            return '';
        }
        //获取平台code
        $platM = D('PlatForm');
        $platform = $platM->get_plat_code();
        $this->assign('platform',$platform);

        //获取币种编码
        $currencyM = D('Currency');
        $currency = $currencyM->get_currency_code();
        $this->assign('currency',$currency);
        $this->display();
    }


    /**
     * addby : lly
     * date : 2018-04-13 22:25
     * used : 充值汇率编辑、充值汇率编辑处理,状态变更处理
    **/
    public function editAction(){
    	$id = I('id',0,'intval');
    	$act = I('act');
		$rateM = D('Rate');
		$data = $rateM->find($id);
		if($id>0 && $act && in_array($act,$this->_open_status))
    	{
            $result = array('status'=>0,'message'=>'汇率状态变更成功','data'=>'');
    		$data['is_used'] = ( $act == 'open' ? 1 : 0 );
            $data['last_modified_by'] = session("username");
            try{
                $rateM->data($data)->save();
                $rateM->insert_into_log($id);
                $result['data'] = U('inlist');
            }catch(\Exception $e) {
                $result['status'] = $e->getCode();
                $result['message'] = '添加汇率信息失败'.$e->getMessage();
            }
            $this->ajaxReturn($result);
    		return '';
    	}
    	if(IS_POST){
            $result = array('status'=>0,'message'=>'汇率信息更新成功','data'=>'');
            if($rateM->create()){
                $rateM->last_modified_by = session("username");
                try{
                    $depositValue = $rateM->deposit_price;
                    $withdrawValue = $rateM->withdraw_price;
                    if($rateM->save()){
                        //通知平台
                        $NotifyService = new OutbizNotifyService();
                        $notifyResult = $NotifyService->exchangeRateNotify($depositValue,$withdrawValue);
                        if( $notifyResult == 'success')
                        {
                            //记录日志
                            $rateM->insert_into_log($id);
                            $result['data'] = U('list');
                        }else{
                            $result['message'] = '修改成功,远程通知汇率变更失败'.$notifyResult;
                        }
                    }else{
                        $result['message'] = '您未做任何信息变更';
                    }
                }catch(\Exception $e) {
                    $result['status'] = $e->getCode();
                    $result['message'] = '汇率信息更新失败'.$e->getMessage();
                }
            }else{
                $result['status'] = '-1';
                $result['message'] = ' 参数出错: '.$rateM->getError();
            }
            $this->ajaxReturn($result);
            return ;
        }
        $this->assign('datainfo',$data);
        $this->display();
    }

    /**
	 * addby : lly
	 * date : 2018-04-23 18:31
	 * used : 汇率操作的日志列表,不可修改
	**/
	public function loglistAction()
	{
        $plat_code = I('platCode');
        $currency_code = I('currencyCode');
		$pageNow = I('p',1);
        $pagesize = 20;
        if(!empty($plat_code)){
            $where['plat_code'] = array('eq',$plat_code);
        }
        if(!empty($currency_code)){
            $where['currency_code'] = array('eq',$currency_code);
        }
        $where['id'] = array('gt',0);
        $rateM = D('Rate');
        $data = $rateM->get_log_list($where,$pageNow,$pagesize);
        $count = $rateM->get_log_count();
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('pager',$show);
        $this->assign('loglist',$data);
        $this->display();
	}
}