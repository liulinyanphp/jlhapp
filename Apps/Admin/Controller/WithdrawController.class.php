<?php
namespace Admin\Controller;

use Common\Conf\BaseConfig;

class WithdrawController extends AdminBaseController {

    /**
     * 条件查询
     */
    public function listAction()
    {
        $pageNow = I('p',1);
        $pageSize = 10;
        $where['id'] = array('gt',0);
//        if(I('platName')) {
//            $where['plat_code'] = array('eq',trim(I('platName')));
//        }
        if(I('uid')) {
            $where['uid'] = array('eq',trim(I('uid')));
        }
        if(I('userAddr')){
            $where['user_addr'] = array('eq',I('userAddr'));
        }
        if(I('status') && I('status')!= 'ALL') {
            $where['status'] = array('eq',I('status'));
        }
        $withdrawM = M('TWithdrawCoinRecord ');
        $user_addr = "concat(substring(user_addr,1,3),'***',substring(user_addr,LENGTH(user_addr)-3,4)) as 'user_addr' ";
        $field = 'id,plat_code,uid,trade_no,status,'.$user_addr.',apply_num,real_num,fee_num,currency_code,apply_time,
        batch_no,created_by';
        $count = $withdrawM->where($where)->count();
        $pageNow = ( $pageNow >$count )  ? $count : ( $pageNow < 1 ? 1: $pageNow );
        $data = $withdrawM->field($field)->where($where)->page($pageNow .','. $pageSize)->order('id desc')->select();
        $Page = new  \Think\AdminPage ( $count, $pageSize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('presentList',$data);
        $this->assign('div','style="text-align:left;"');
        $this->assign('DrawStatusSearch',BaseConfig::$approval_state);
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
        $data = M('TWithdrawCoinRecord')->where($w)->find();
        $data['status'] = BaseConfig::$approval_state[$data['status']];
        unset($data['id']);
        $this->ajaxReturn($data);
    }

    /**
     * 申请重发
     */
    public function applyResendAction()
    {
        $batchNo = I('batch_no');
        $w['batch_no'] = array('eq',$batchNo);

        if(empty($batchNo)){
            $ret = array('result'=>-1,'res_info'=>"参数有误！",'result_rows'=>array());
            $this->ajaxReturn($ret);
        }


        $RecordM =  M('TWithdrawCoinRecord');
        $record = $RecordM->where($w)->find();


        if(empty($record)){

            $ret = array('result'=>-1,'res_info'=>"提币记录不存在",'result_rows'=>array());
            $this->ajaxReturn($ret);
        }

        if($record['status'] != BaseConfig::ApprovalStateManual){
            $ret = array('result'=>-1,'res_info'=>"提币记录状态有误！",'result_rows'=>array());
            $this->ajaxReturn($ret);
        }



        $batchNo = I('batch_no');
        $w['batch_no'] = array('eq',$batchNo);
        $TransactionM = M('TWithdrawCoinTransaction');
        $transaction = $TransactionM->where($w)->find();

        if(empty($transaction)){

            $ret = array('result'=>-1,'res_info'=>"提币记录不存在",'result_rows'=>array());
            $this->ajaxReturn($ret);
        }

        if($transaction['status'] != BaseConfig::TransactionStateManual){
            $ret = array('result'=>-1,'res_info'=>"提币交易状态有误！",'result_rows'=>array());
            $this->ajaxReturn($ret);
        }

        //重发的交易没有hash 或者hash查不到交易记录
        $resend = false;
        if(empty($transaction['tx_hash'])){

            $resend = true;
        }else{
            //查询交易确认数和状态
            $ret = OmniUtils::getTransInfo($record['tx_hash']);

            if(empty($ret)){

                $resend = true;
            }else{
                if($ret['confirmations'] > 0 && $ret['valid'] == false){

                    $resend = true;
                }

            }
        }

        //重新进入发送队列
        if($resend == true){

            //记录重发日志
            $data = array();
            $data['id'] = $record['id'];
            $data['status'] = BaseConfig::ApprovalStateReviewd;

            $RecordM->where('id='.$data['id'])->save($data);

            $data = array();
            $data['id'] = $transaction['id'];
            $data['status'] = BaseConfig::TransactionStateCreated;
            $TransactionM->where('id='.$data['id'])->save($data);

            $ret = array('result'=>0,'res_info'=>"操作成功！",'result_rows'=>array());
            $this->ajaxReturn($ret);
        }else{

            $ret = array('result'=>-1,'res_info'=>"不符合重发条件！",'result_rows'=>array());
            $this->ajaxReturn($ret);
        }
    }

    /**
     * 标记已发送
     */
    public function applySendedAction()
    {

        $batchNo = I('batch_no');
        $txHash = I('tx_hash');

        if(empty($batchNo) || empty($txHash)){
            $ret = array('result'=>-1,'res_info'=>"参数有误！",'result_rows'=>array());
            $this->ajaxReturn($ret);
        }

        $w['batch_no'] = array('eq',$batchNo);
        $RecordM =  M('TWithdrawCoinRecord');
        $record = $RecordM->where($w)->find();


        if(empty($record)){

            $ret = array('result'=>-1,'res_info'=>"提币记录不存在",'result_rows'=>array());
            $this->ajaxReturn($ret);
        }

        if($record['status'] != BaseConfig::ApprovalStateManual){
            $ret = array('result'=>-1,'res_info'=>"提币记录状态有误！",'result_rows'=>array());
            $this->ajaxReturn($ret);
        }



        $batchNo = I('batch_no');
        $w['batch_no'] = array('eq',$batchNo);
        $TransactionM = M('TWithdrawCoinTransaction');
        $transaction = $TransactionM->where($w)->find();

        if(empty($transaction)){

            $ret = array('result'=>-1,'res_info'=>"提币记录不存在",'result_rows'=>array());
            $this->ajaxReturn($ret);
        }

        if($transaction['status'] != BaseConfig::TransactionStateManual){
            $ret = array('result'=>-1,'res_info'=>"提币交易状态有误！",'result_rows'=>array());
            $this->ajaxReturn($ret);
        }

        if(empty($transaction['tx_hash'])){

            //修改已发送待确认交易状态
            $data = array();
            $data['id'] = $record['id'];
            $data['status'] = BaseConfig::ApprovalStateHandled;

            $RecordM->where('id='.$data['id'])->save($data);

            $data = array();
            $data['id'] = $transaction['id'];
            $data['tx_hash'] =  $txHash;
            $data['status'] = BaseConfig::TransactionStatSending;
            $TransactionM->where('id='.$data['id'])->save($data);

            $ret = array('result'=>0,'res_info'=>"操作成功！",'result_rows'=>array());
            $this->ajaxReturn($ret);
        }else{

            $ret = array('result'=>-1,'res_info'=>"发送交易hash已存在，txHash:".$transaction['tx_hash'],'result_rows'=>array());
            $this->ajaxReturn($ret);
        }
    }
}