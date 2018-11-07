<?php
namespace Admin\Controller;


use Common\Conf\BaseConfig;

use Org\Util\Result;
use \Org\Util\Exception;
use Common\Utils\CommonUtils;
use Common\Service\PresentRecordService;
use Common\Service\OutbizNotifyService;

class PresentController extends AdminBaseController {

    /**
     * 条件查询
     */
    public function listAction()
    {
        $pageNow = I('p',1);
        $pageSize = 10;
        $where['id'] = array('gt',0);
        if(I('trade_no')) {
            $where['trade_no'] = array('eq',trim(I('trade_no')));
        }
        if(I('real_name')){
            $where['real_name'] = array('like','%'.I('real_name').'%');
        }
        if(I('bank_card_no')){
            $where['bank_card_no'] = array('eq',I('bank_card_no'));
        }
        if(I('status') && I('status')!=''){
            $where['status'] = array('eq',I('status'));
        }
        $presentM = M('TPresentRecord');
        $field = 'trade_no,uid,status,plat_code,currency_code,sell_num,curr_price,apply_amount,fee_amount,
        real_amount,created_by,apply_time';
        $count = $presentM->where($where)->count();
        $pageNow = ( $pageNow >$count )  ? $count : ( $pageNow < 1 ? 1: $pageNow );
        $data = $presentM->field($field)->where($where)->page($pageNow .','. $pageSize)->order('id desc')->select();
        $Page = new  \Think\AdminPage ( $count, $pageSize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('is_checked',BaseConfig::WithdrawDepositSubmitted);
        $this->assign('page',$show);
        $this->assign('presentList',$data);
        $this->assign('PresentStatusSearch',BaseConfig::$withdraw_deposit_state);
        $this->assign('param',I('get.'));
        $this->display();
    }

    /**
     * 查看详情
     */
    public function detailAction()
    {
        $tradeNo = I('tradeNo');
        $w['trade_no'] = array('eq',$tradeNo);
        $data = M('TPresentRecord')->where($w)->find();
        $this->assign('PresentStatusSearch',BaseConfig::$withdraw_deposit_state);
        $this->assign('data',$data);
        $this->assign('div','style="text-align:left;width:26%;"');
        $this->display();
    }

    /*
     * 编辑
     */
    public function editAction()
    {
        $tradeNo = I('tradeNo');
        $w['trade_no'] = array('eq',$tradeNo);
        $data = M('TPresentRecord')->where($w)->find();
        if(IS_POST)
        {
            try{
                $postdata = I('post.');
                foreach($postdata as $key=>$obj)
                {
                    $v = trim($obj);
                    if($v=='')
                    {
                        $conf_error_key = strtoupper('error_'.$key.'_is_empty');
                        Exception::throwsErrorMsg(C($conf_error_key));
                        break;
                    }
                    //检查数字类型
                    $conf_error_ck_type = strtoupper('error_'.$key.'_need_number');
                    $conf_info = C('ADMIN_NUMBER_CHECK');
                    if(!isset($conf_info[$conf_error_ck_type])){
                        continue;
                    }else{
                        $fun_name = $conf_info[$conf_error_ck_type][2];
                        if(!CommonUtils::$fun_name($v)){
                            Exception::throwsErrorMsg($conf_info[$conf_error_ck_type]);
                        }
                    }
                }
                //if( I('pay_amout') != $data['apply_amount'])
                //{
                //    print_r(I('pay_amout').$data['apply_amount']);
                //    Exception::throwsErrorMsg(C('ERROR_PAY_AMOUNT_IS_NEQ'));
                //}
                #code 写自己的业务逻辑
                $presentService = new PresentRecordService();
                $param = array(
                    'plat_code' => $data['plat_code'],
                    'trade_no' => $data['trade_no'],
                    'pay_bank' => I('pay_bank'),
                    'pay_bank_card_no'=>I('pay_bank_card_no'),
                    'pay_amout'=> I('pay_amout'),
                    'bank_trans_no'=>I('bank_trans_no'),
                    'payment_time'=>I('payment_time'),
                    'operator_id'=>session("uid"),
                    'operator_name'=>session("username")
                );
                $res = $presentService->present_record_pay_notice($param);
                if($res['res_info'] == 'success')
                {
                    //提现通知远端
                    if($this->_notifyData($w)!='success'){
                        $notifyarr = array('-1','内部处理完成,远程通知失败通知失败');
                        Exception::throwsErrorMsg($notifyarr);
                    }
                    $result = Result::innerResultSuccess();
                    $result['res_info'] = C('PRESENT_CHECK_OK');
                    $result['result_rows'] = U('list');
                    $this->ajaxReturn($result);
                }else{
                    //报错了
                    Exception::throwsErrorMsg(C('ERROR_PRESENT_CHECK_ERR'));
                    //$err = array($res['result'],$res['res_info']);
                    //Exception::throwsErrorMsg($err);
                }
            } catch(\Exception $e){
                $result =  Result::innerResultFail($e);
            }
            $this->ajaxReturn($result);
            return '';
        }
        //内部可转账的银行卡信息
        $bankCardInfo = D('BankCardInfo')->select();
        $this->assign('payBank',array_column($bankCardInfo,'bank_name'));
        $this->assign('bankTransNo',array_column($bankCardInfo,'bank_card_no'));
        $this->assign('data',$data);
        $this->assign('div','style="text-align:left;width:26%;"');
        $this->assign('PresentStatusSearch',BaseConfig::$withdraw_deposit_state);
        $this->display();
    }

    private function _notifyData($w)
    {
        $data = M('TPresentRecord')->where($w)->find();
        $return['tradeId'] = $data['trade_no'];
        $return['exchangeTradeId'] = strtolower($data['plat_code']).$data['id'].$data['trade_no'];
        $return['platformCode'] = $data['plat_code'];
        $return['userId'] = $data['uid'];
        $return['currencyName'] = $data['currency_code'];
        $return['userName'] = $data['real_name'];
        $return['bankName'] = $data['bank'];
        $return['branchBankName'] = $data['branch_bank'];
        $return['bankCardNo'] = $data['bank_card_no'];
        $return['applyNum'] = $data['sell_num'];
        $return['unitPrice'] = $data['curr_price'];
        $return['totalAmount'] = $data['apply_amount'];
        $return['status'] =  ( $data['status']=='FINISHED' || $data['status'] == 'COLLECTED') ? 'success' : 'fail';
        $notifyService = new OutbizNotifyService();
        return $notifyService->withdrawDepositStateNotify($return);
    }
}