<?php
namespace Home\Controller;
use Common\Shell\BatchWithdrawNotifyJob;
use Common\Shell\BlockScanJob;
use Common\Shell\DepositRecordHandleJob;
use Org\Util\Result;
use Think\Controller;
use Common\Service\Bitcoin;
use Common\Service\AddressService;
use Common\Utils\ExloggerUtils;
use Common\Service\PlatformService;
use \Org\Util\Exception;
use Common\Shell\ReviewWithdrawCoinRecordJob;
use Common\Shell\BatchWithdrawTransHandleJob;
use Common\Shell\TransStateSyncJob;
use Common\Shell\BatchDepositNotifyJob;

class JobController extends Controller {

    //批量生成地址
    //public function createAddress()
    public function createAction()
    {
        //http://usdt.com/home/job/create/job_token/91b3afd42a5949b1d5acd30d756daaa2/plat_code/huobi/num/
        //定义少于1000个的时候自动就添加
        try{
            $sing = md5('jobCreateAddress');
            //91b3afd42a5949b1d5acd30d756daaa2
            if(I('job_token') == $sing)
            {
                //获取平台编码
                $platCodes = PlatformService::get_plat_code();
                if(!I('plat_code') || !in_array(strtoupper(I('plat_code')),$platCodes))
                {
                    Exception::throwsErrorMsg(C('PLAT_CODE_IS_NULL'));
                }
                //目前只支持usdt
                //如果穿了num则生成指定数目的,否则自动验证阀值
                if(I('num')>0){
                    $needCount = (int)I('num');
                }else{
                    $addressCount = AddressService::get_available_address_count();
                    $needCount = ( $addressCount>= C('BATCH_CREATE_ADDRESS_NUM')) ? 0 : (C('BATCH_CREATE_ADDRESS_NUM')-$addressCount);
                    if($needCount < 1)
                    {
                        Exception::throwsErrorMsg(C('ADDRESS_NUM_IS_ENOUGH'));
                    }
                }
                $param = array(
                    'plat_code'=>strtoupper(I('plat_code')),
                    'currency_code'=>I('currency_code') ? strtoupper('currency_code') : 'USDT',
                    'num'=>$needCount,
                    'created_by'=>'system'
                );
                $result = AddressService::batch_create_address($param);
                if($result['res_info']!='ok'){
                    $res[0] = $result['result'];
                    $res[1] = $result['res_info'];
                    Exception::throwsErrorMsg($res);
                }
            }else{
                Exception::throwsErrorMsg(C('ADDRESS_PARAM_IS_ERROR'));
            }
        }catch(\Exception $e){
            ExloggerUtils::log(json_encode(Result::innerResultFail($e)),'error');
            $this->ajaxReturn(Result::innerResultFail($e));
        }
        $this->ajaxReturn(Result::innerResultSuccess());
    }


    /**
     * 频次： 5分钟执行一次
     * 定时自动审批提币记录，生成提币交易待发送记录
     * @return array
     */
    public function reviewAction(){

        //http://localhost/index.php?m=home&c=Job&a=review&job_token=1f731cae4a20324e685d55ff1cba92ba
        try{
            $sing = md5('reviewWithdrawCoinRecordJob');

            if(I('job_token') == $sing) {
                $job = new ReviewWithdrawCoinRecordJob();
                $job->excute();
            }
        }catch(\Exception $e) {

            ExloggerUtils::log(json_encode(Result::innerResultFail($e)), 'error');
            $this->ajaxReturn(Result::innerResultFail($e));
        }
        $this->ajaxReturn(Result::innerResultSuccess());

    }

    /**
     * 频次： 30分钟执行一次
     * 定时批量发送提币待发送状态的交易
     * @return array
     */
    public function  batchSendTransAction(){

        //http://localhost/index.php?m=home&c=Job&a=batchSendTrans&job_token=acbe46a5c78cb841e3a3003ca4f19b58
        try{
            $sing = md5('batchWithdrawTransHandleJob');

            if(I('job_token') == $sing) {
                $job = new BatchWithdrawTransHandleJob();
                $job->excute();
            }
        }catch(\Exception $e) {

            ExloggerUtils::log(json_encode(Result::innerResultFail($e)), 'error');
            $this->ajaxReturn(Result::innerResultFail($e));
        }
    }


    /**
     *  频次： 15分钟执行一次
     * @return array
     */
    public function  transStateSyncAction(){

        //http://localhost/index.php?m=home&c=Job&a=transStateSync&job_token=6a9aeea3f36d2b09064ecc7af4ce9396
        try{
            $sing = md5('transStateSyncJob');

            if(I('job_token') == $sing) {
                $job = new TransStateSyncJob();
                $job->excute();
            }
        }catch(\Exception $e) {

            ExloggerUtils::log(json_encode(Result::innerResultFail($e)), 'error');
            $this->ajaxReturn(Result::innerResultFail($e));
        }
    }


    /**
     *  频次： 10分钟执行一次
     * @return array
     */
    public function  blockScanAction(){

        //http://localhost/index.php?m=home&c=Job&a=blockScan&job_token=fd59dd27be6c240b969cdc084a16adcb
        try{
            $sing = md5('blockScanJob');

            if(I('job_token') == $sing) {
                $job = new BlockScanJob();
                $job->excute();
            }
        }catch(\Exception $e) {

            ExloggerUtils::log(json_encode(Result::innerResultFail($e)), 'error');
            $this->ajaxReturn(Result::innerResultFail($e));
        }
    }

    /**
     *  频次： 10分钟执行一次
     * @return array
     */
    public function  depositRecordAction(){

        //http://localhost/index.php?m=home&c=Job&a=depositRecord&job_token=a4d2de3dec6daf6158eefea35527748f
        try{
            $sing = md5('depositRecordJob');
            if(I('job_token') == $sing) {
                $job = new DepositRecordHandleJob();
                $job->excute();
            }
        }catch(\Exception $e) {

            ExloggerUtils::log(json_encode(Result::innerResultFail($e)), 'error');
            $this->ajaxReturn(Result::innerResultFail($e));
        }
    }

    /**
     *  频次：  20分钟执行一次
     *  定时推送充币通知到平台
     * @return array
     */
    public function  depositNotifyAction(){

        //http://localhost/index.php?m=home&c=Job&a=depositNotify&job_token=85db171e7c02d12bcbcee27b0afab0e5
        try{
            $sing = md5('batchDepositNotifyJob');

            if(I('job_token') == $sing) {
                $job = new BatchDepositNotifyJob();
                $job->excute();
            }
        }catch(\Exception $e) {

            ExloggerUtils::log(json_encode(Result::innerResultFail($e)), 'error');
            $this->ajaxReturn(Result::innerResultFail($e));
        }
    }

    /**
     * 频次： 20分钟执行一次
     * 定时批量发送提币通知
     * @return array
     */
    public function  withdrawNotifyAction(){

        //http://localhost/index.php?m=home&c=Job&a=withdrawNotify&job_token=ea43c4d3cbac963cbfce618f3116dc1a
        try{
            $sing = md5('batchWithdrawNotifyJob');

            if(I('job_token') == $sing) {
                $job = new BatchWithdrawNotifyJob();
                $job->excute();
            }
        }catch(\Exception $e) {

            ExloggerUtils::log(json_encode(Result::innerResultFail($e)), 'error');
            $this->ajaxReturn(Result::innerResultFail($e));
        }
    }

}