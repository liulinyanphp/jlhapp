<?php
namespace Admin\Controller;

use Common\Conf\BaseConfig;
use Common\Service\PurchaseCoinService;
use \Org\Util\Result;
use \Org\Util\Exception;

use Common\Service\OutbizNotifyService;
class OrderController extends AdminBaseController {

    /**
     * addby : lly
     * date : 2018-04-21 17:08
     * used : 用户充值地址列表
    **/
    public function purchase_listAction()
    {
        $pageNow = I('p',1);
        $pageSize = 20;
        $where['id'] = array('gt',0);
        if(I('tradeNo'))
        {
            $where['trade_no'] = array('eq',trim(I('tradeNo')));
        }
        if(I('referenceNo')){
            $where['reference_no'] = array('eq',I('referenceNo'));
        }
        if(I('bankCardNo')){
            $where['bank_card_no'] = array('eq',I('bankCardNo'));
        }
        if(I('status') && I('status') != BaseConfig::PurchaseCoinRecordEmpty)
        {
            $where['status'] = array('eq',I('status'));
        }
        $purchaseM = M('TPurchaseCoinRecord');
        $count = $purchaseM->where($where)->count();
        $pageNow = $pageNow >$count ? $count : ( $pageNow < 1 ? 1: $pageNow);
        $data = $purchaseM->where($where)->page($pageNow .','. $pageSize)->order('id desc')->select();
        $Page = new  \Think\AdminPage ( $count, $pageSize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('purchase_list',$data);
        $this->assign('submitted',BaseConfig::PurchaseCoinRecordSubmitted);
        $this->assign('PurchaseCoinRecordStatus',BaseConfig::$PurchaseCoinRecordStatus);
        $this->assign('param',I("get."));
        $this->display();
    }



    /**
     * used : 平台购买币列表
     */
    public function platPurchase_listAction()
    {
        $pageNow = I('p',1);
        $pageSize = 20;
        $where['id'] = array('gt',0);
        if(I('platName')) {
            $where['plat_code'] = array('eq',trim(I('platName')));
        }
        if(I('uid')){
            $where['uid'] = array('eq',I('uid'));
        }
        if(I('userAddr')){
            $where['user_addr'] = array('eq',I('userAddr'));
        }
        $purchaseM = M('TPlatPurchaseCoinRecord');
        $count = $purchaseM->where($where)->count();
        $pageNow = $pageNow >$count ? $count : ($pageNow<1 ? 1: $pageNow);
        $data = $purchaseM->where($where)->page($pageNow .','. $pageSize)->order('id desc')->select();
        $Page = new  \Think\AdminPage ( $count, $pageSize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('platPurchaseList',$data);
        $this->assign('param',I('get.'));
        $this->display('platPurchase_list');
    }

    /**
     * used：平台扣币列表
     */
    public function platCollect_listAction()
    {
        $pageNow = I('p',1);
        $pageSize = 20;
        $where['id'] = array('gt',0);
        if(I('platName')){
            $where['plat_code'] = array('eq',trim(I('platName')));
        }
        if(I('uid')){
            $where['uid'] = array('eq',I('uid'));
        }
        if(I('userAddr')){
            $where['user_addr'] = array('eq',I('userAddr'));
        }
        $purchaseM = M('TPlatCollectCoinRecord');
        $count = $purchaseM->where($where)->count();
        $pageNow = $pageNow >$count ? $count : ($pageNow<1 ? 1: $pageNow);
        $data = $purchaseM->where($where)->page($pageNow .','. $pageSize)->order('id desc')->select();
        $Page = new  \Think\AdminPage ( $count, $pageSize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('platCollectList',$data);
        $this->assign('param',I('get.'));
        $this->display('platCollect_list');
    }



    /**
     * addby : lly
     * date : 2018-04-14 14:30
     * used : 钱包添加、钱包添加处理
     * desc : 预留着给后台操作人员用
    **/
    public function editAction()
    {
        $tradeNo = I('tradeNo');
        $w['trade_no'] = array('eq',$tradeNo);
        $data = M('TPurchaseCoinRecord')->where($w)->find();
        if(IS_POST) {
            try{
                $str = '';
                if($data['status'] != BaseConfig::PurchaseCoinRecordSubmitted){
                    Exception::throwsErrorMsg(C('ADMIN_ORDER_STATUS_IS_ERROR'));
                }
                $amount = I('amount');
                $bankTransNo = I('bank_trans_no');
                $paymentTime = I('payment_time');
                $status = I('status');
                $operatorDesc = I('operator_desc');
                $up_data['id'] = $data['id'];
                $up_data['status'] = $status;
                $up_data['operator_desc'] = $operatorDesc;
                $up_data['bank_trans_no'] = $bankTransNo;
                $up_data['payment_time'] = $paymentTime;
                $up_data['operator_id'] = session("uid");
                $up_data['operator_name'] = session("username");
                $up_data['last_modified_by'] = session("username");
                if($status!= BaseConfig::PurchaseCoinRecordFinished)
                {
                    M('TPurchaseCoinRecord')->data($up_data)->save();
                    $result = Result::innerResultSuccess();
                    $result['res_info'] = $tradeNo.'审核不通过,已经成功驳回';
                    $result['result_rows'] = U('purchase_list');
                }else{
                    //如果信息未填写
                    if($amount==''){
                        $str .= '金额不能为空';
                    }
                    if($bankTransNo==''){
                        $str .= '银行流水不能为空';
                    }
                    if($paymentTime==''){
                        $str .= '到账时间不能未空';
                    }
                    if($str !='')
                    {
                        $error_msg = C('ADMIN_ORDER_OPTION_ERROR');
                        $error_msg[1] = $str;
                        Exception::throwsErrorMsg($error_msg);
                    }
                    if(bcdiv($amount,1,2) !== $data['amount'])
                    {
                        Exception::throwsErrorMsg(C('ADMIN_ORDER_AMOUNT_IS_ERROR'));
                    }
                    $purchaseCoinService = new PurchaseCoinService();
                    $result = $purchaseCoinService->transfer_coin($data);
                    if($result['res_info'] == 'ok')
                    {
                        $up_data['status'] = BaseConfig::PurchaseCoinRecordFinished;   //'已付款,已确认';
                        M('TPurchaseCoinRecord')->data($up_data)->save();
                        $result['res_info'] = '订单已处理,代币自动划账成功';
                        $result['result_rows'] = U('purchase_list');
                    }else{
                        $error_msg = C('ADMIN_ORDER_OPTION_ERROR');
                        $error_msg[1] = $result['res_info'];
                        Exception::throwsErrorMsg($error_msg);
                    }
                }
                if($this->_notifyData($w)!='success'){
                    $notifyarr = array('-1','内部处理完成,远程通知失败通知失败');
                    Exception::throwsErrorMsg($notifyarr);
                }
            }catch(\Exception $e)
            {
                $result = Result::innerResultFail($e);
            }
            $this->ajaxReturn($result);
            return '';
        }
        $purchaseCoinStatus = BaseConfig::$PurchaseCoinRecordStatusCheck;
        $this->assign('data',$data);
        $this->assign('purchaseCoinStatus',$purchaseCoinStatus);
        $this->assign('div','style="text-align:left"');
        $this->display();
    }

    private function _notifyData($w)
    {
        $data = M('TPurchaseCoinRecord')->where($w)->find();
        $return['tradeId'] = $data['trade_no'];
        $return['exchangeTradeId'] = strtolower($data['plat_code']).$data['id'].$data['trade_no'];
        $return['platformCode'] = $data['plat_code'];
        $return['userId'] = $data['uid'];
        $return['currencyName'] = $data['currency_code'];
        $return['receiverAddress'] = $data['user_addr'];
        $return['tradeAmount'] = $data['amount'];
        $return['unitPrice'] = $data['curr_price'];
        $return['tradeNum'] = $data['num'];
        $return['referenceNo'] = $data['reference_no'];
        $return['status'] = $data['status'] == 'FINISHED' ? 'success' : 'fail';
        $notifyService = new OutbizNotifyService();
        return $notifyService->rechargeNotify($return);
    }

}