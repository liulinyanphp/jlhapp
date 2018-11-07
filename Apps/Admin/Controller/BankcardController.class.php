<?php
namespace Admin\Controller;

use Think\Controller;
use Common\Service\UtilService;
class BankcardController extends AdminBaseController {

    private $_open_status = array('active','close');
	public function __Construct()
	{
		parent::__construct();
		$this->assign('carstatus',C('BANKCAR_STATUS'));
        $this->assign('banktype',C('BANKTYPE'));
        $this->assign('bankname',C('BANKNAME'));
	}

	/**
     * addby : lly
     * date : 2018-09-11 15:10
     * used : 银行卡信息列表
    **/
    public function listAction()
    {
        $bankM = D('BankCardInfo');
        $data = $bankM->getlist();
        $this->assign('carlist',$data);
        $this->display();
    }
    /**
     * addby : lly
     * date : 2018-09-11 15:20
     * used : 后台对用户银行卡添加、用户银行卡添加处理
    **/
    public function addAction(){
        $result = array('status'=>0,'message'=>'添加银行卡成功','data'=>'');
        if(IS_POST)
        {
        	$carM = D('BankCardInfo');
        	if($carM->create())
        	{
                $carM->created_by = session("username");
                try{
                    $res = $carM->add();
                    /*写入日志列表*/
                    $source_data['id'] = $res;
                    $source_data['remark'] = '添加银行卡';
                    $this->_log_record($source_data);
                    $result['data'] = U('list');
                }catch(\Exception $e) {
                    $result['status'] = $e->getCode();
                    $result['message'] = '添加银行卡失败'.$e->getMessage();
                }
        	} else{
        	    $result['status'] = '-1';
        	    $result['message'] = $carM->getError();
            }
            $this->ajaxReturn($result);
            return '';
        }
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-09-11 15:34
     * used : 用户银行卡信息的操作日志
     **/

    private function _log_record($source_data)
    {
        $cardLogM = M('TCardlogInfo');
        $data['carid'] = $source_data['id'];
        $data['remark'] = $source_data['remark'];
        $data['uname'] = session("username");
        $data['addtime'] = time();
        try{
            $cardLogM->add($data);
        }catch(\Exception $e){
            $result['status'] = $e->getCode();
            $result['message'] = '银行卡状态变更失败'.$e->getMessage();
            $this->ajaxReturn($result);exit;
        }
    }

    /**
     * addby : lly
     * date : 2018-09-11 15:52
     * used : 银行卡状态变更处理
    **/
    public function change_statusAction(){
        $result = array('status'=>0,'message'=>'银行卡状态变更成功','data'=>array());
    	$id = I('id',0,'intval');
    	$act = I('act');
		$carM = D('BankCardInfo');
		$data = $carM->find($id);
		if($id>0 && $act!='' && in_array($act,$this->_open_status))
    	{
    		$data['status'] = ( $act == 'active' ? 1 : 0 ) ;
            try{
                $carM->data($data)->save();
                //记录日志
                $data['remark'] = ( $act == 'active' ? '激活银行卡' : '冻结银行卡' ) ;
                $this->_log_record($data);
                $result['message'] = '成功'.$data['remark'];
                $result['data'] = U('list');
            }catch(\Exception $e) {
                $result['status'] = $e->getCode();
                $result['message'] = '状态变更失败'.$e->getMessage();
            }
    	}else{
            $result['status'] = '-1';
            $result['message'] = '参数错误';
        }
        $this->ajaxReturn($result);
    }

    /**
	 * addby : lly
	 * date : 2018-04-14 10:24
	 * used : 汇率操作的日志列表,不可修改
	**/
	public function loglistAction()
	{
		$pageNow = I('p',1);
        $pagesize = 10;
        $where['a.lgid'] = array('gt',0);
        $carM = D('BankCardInfo');
        $data = $carM->getloglist($where,$pageNow,$pagesize);
        $count = $carM->getlogcount();
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('pager',$show);
        $this->assign('loglist',$data);
        $this->display();
	}
}