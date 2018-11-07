<?php
namespace Admin\Controller;

use Think\Controller;
use Common\Service\DataService;
use Common\Service\UtilService;
use Org\Util\Rbac;
class ConfigController extends AdminBaseController {

	private $_open_status = array('open','close');
	public function __Construct()
	{
		parent::__construct();
		$this->assign('platform',C('PLATFORM'));
    	$this->assign('basebi',C('BASEBI'));
	}

	/**
     * addby : lly
     * date : 2018-04-13 20:40
     * used : 充值汇率信息列表
    **/
    public function rate_inlistAction()
    {
        $rateM = D('RateInfo');
        $data = $rateM->getlist();
        $this->assign('ratedata',$data);
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-04-14 11:00
     * used : 提现汇率信息列表
    **/
    public function rate_outlistAction()
    {
        $rateM = D('RateInfo');
        $data = $rateM->getlist(2);
        $this->assign('ratedata',$data);
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-04-13 21:12
     * used : 充值汇率添加、充值汇率添加处理
     * desc : 本来充和提可以写一起的,怕到时候要分不同的人用权限来管控,所以还是分开
    **/
    public function add_rateinAction(){
        if(IS_POST)
        {
        	$rateM = D('RateInfo');
        	if($rateM->create())
        	{
        		$rateM->addtime = time();
        		$rateM->updatetime = time();
        		$source_data['rate_value'] = $rateM->rate_value;
        		$res = $rateM->add();
        		if($res){
        			//写入日志列表
        			$source_data['id'] = $res;
        			$source_data['remark'] = '添加充值汇率';
        			$this->_log_record($source_data);
                    $this->success('添加充值汇率成功!'.$result,U('rate_inlist'));
                }else{
                    $this->error('添加充值汇率失败!');
                }
        	} else{
                $this->error($rateM->getError());
            }
            return '';
        }
        $this->display();
    }


    /**
     * addby : lly
     * date : 2018-04-14 11:02
     * used : 提现汇率添加、提现汇率添加处理
     * desc : 本来充和提可以写一起的,怕到时候要分不同的人用权限来管控,所以还是分开
    **/
    public function add_rateoutAction(){
        if(IS_POST)
        {
        	$rateM = D('RateInfo');
        	if($rateM->create())
        	{
        		$rateM->addtime = time();
        		$rateM->updatetime = time();
        		$source_data['rate_value'] = $rateM->rate_value;
        		$res = $rateM->add();
        		if($res){
        			//写入日志列表
        			$source_data['id'] = $res;
        			$source_data['remark'] = '添加提现汇率';
        			$this->_log_record($source_data);
                    $this->success('添加提现汇率成功!'.$result,U('rate_outlist'));
                }else{
                    $this->error('添加提现汇率失败!');
                }
        	} else{
                $this->error($rateM->getError());
            }
            return '';
        }
        $this->display();
    }



    /**
     * addby : lly
     * date : 2018-04-13 22:25
     * used : 充值汇率编辑、充值汇率编辑处理,状态变更处理
    **/
    public function edit_rateinAction(){
    	$id = I('id',0,'intval');
    	$act = I('act');
    	
		$rateM = D('RateInfo');
		$data = $rateM->find($id);
		if($id>0 && $act && in_array($act,$this->_open_status))
    	{

    		$data['is_used'] = ( $act == 'open' ? 1 : 0 ) ;
    		$data['updatetime'] = time();
    		$rateM->data($data)->save();
    		//记录日志
   			$data['remark'] = ( $act == 'open' ? '开启充值汇率配置' : '关闭充值汇率配置' ) ;
   			$this->_log_record($data);
    		$this->redirect(U('rate_inlist'));
    		return '';
    	}
    	if(IS_POST){
    		$str = '';
            if($rateM->create()){
            	$rateM->updatetime = time();
            	$source_data['id'] = $rateM->id;
            	$source_data['rate_value'] = $rateM->rate_value;

            	//看看都变了什么
            	if($rateM->rate_value != $data['rate_value']){
            		$str = '汇率由'.$data['rate_value'].'改为'.$rateM->rate_value;
            	}
            	if($rateM->is_used != $data['is_used'])
            	{
            		$str .= ( $rateM->is_used> 0 ? '状态由关闭修改为开启' : '状态由开启修改为关闭' );
            	}
                if($rateM->save()){
                	//记录日志
		   			$source_data['remark'] = '更新充值汇率:'.$str;
		   			$this->_log_record($source_data);
                    $this->success('更新充值汇率成功!',U('rate_inlist'));
                }else{
                    $this->error('更新充值汇率失败!');
                }
            }else{
                $this->error($rateM->getError());
            }
            return ;
        }
        $this->assign('datainfo',$data);
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-04-14 13:19
     * used : 充值汇率编辑、充值汇率编辑处理,状态变更处理
    **/
    public function edit_rateoutAction(){
    	$id = I('id',0,'intval');
    	$act = I('act');
    	
		$rateM = D('RateInfo');
		$data = $rateM->find($id);
		if($id>0 && $act && in_array($act,$this->_open_status))
    	{

    		$data['is_used'] = ( $act == 'open' ? 1 : 0 ) ;
    		$data['updatetime'] = time();
    		$rateM->data($data)->save();
    		//记录日志
   			$data['remark'] = ( $act == 'open' ? '开启提现汇率配置' : '关闭提现汇率配置' ) ;
   			$this->_log_record($data);
    		$this->redirect(U('rate_outlist'));
    		return '';
    	}
    	if(IS_POST){
    		$str = '';
            if($rateM->create()){
            	$rateM->updatetime = time();
            	$source_data['id'] = $rateM->id;
            	$source_data['rate_value'] = $rateM->rate_value;

            	//看看都变了什么
            	if($rateM->rate_value != $data['rate_value']){
            		$str = '提现汇率由'.$data['rate_value'].'改为'.$rateM->rate_value;
            	}
            	if($rateM->is_used != $data['is_used'])
            	{
            		$str .= ( $rateM->is_used> 0 ? '状态由关闭修改为开启' : '状态由开启修改为关闭' );
            	}
                if($rateM->save()){
                	//记录日志
		   			$source_data['remark'] = '更新提现汇率:'.$str;
		   			$this->_log_record($source_data);
                    $this->success('更新提现汇率成功!',U('rate_outlist'));
                }else{
                    $this->error('更新提现汇率失败!');
                }
            }else{
                $this->error($rateM->getError());
            }
            return ;
        }
        $this->assign('datainfo',$data);
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-04-14 9:00
     * used : 记录充值汇率的日志
    **/

    private function _log_record($source_data)
    {
    	$ratelogM = M('ZbRatelogInfo');
    	$data['rateid'] = $source_data['id'];
    	$data['rate_value'] = $source_data['rate_value'];
    	$data['remark'] = $source_data['remark'];
    	$data['uname'] = session("username");
    	$data['addtime'] = time();
    	if($ratelogM->add($data)){
    		return 1;
    	}else{
    		//记录写入日志列表出错了
    	}
    }

    /**
	 * addby : lly
	 * date : 2018-04-14 10:24
	 * used : 汇率操作的日志列表,不可修改
	**/
	public function rate_logAction()
	{
		$pageNow = I('p',1);
        $pagesize = 10;
        $where['a.id'] = array('gt',0);
        $rateM = D('RateInfo');
        $field = 'a.*,b.platform,b.basebi_id as bi';
        $data = $rateM->getloglist($field,$where,$pageNow,$pagesize);
        $count = $rateM->getlogcount();
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('pager',$show);
        $this->assign('loglist',$data);
        $this->display();
	}
}