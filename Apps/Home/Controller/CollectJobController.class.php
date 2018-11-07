<?php
/**
 * Created by PhpStorm.
 * User: tang
 * Date: 2018/5/30
 * Time: 下午4:14
 */

namespace Home\Controller;
use Common\Shell\RecoveryCoinToPurchaseJob;
use Common\Shell\UserAddrCoinCollectJob;
use Common\Shell\HotToColdJob;
use Common\Shell\UserAddrCoinCollectStatusJob;
use Common\Utils\ExloggerUtils;
use Org\Util\Result;
use Think\Controller;

class CollectJobController extends Controller {

    //提现币回收脚本
    public function recoveryAction()
    {
        //http://localhost/index.php?m=home&c=CollectJob&a=recovery&job_token=e60c25dbbaac7f142b743eb18341f72f
        try{
            $sing = md5('recoveryJob');
            if(I('job_token') == $sing) {
                $s = new RecoveryCoinToPurchaseJob();
                $r = $s->recovery_coin_to_purchase(array());
            }
        }catch(\Exception $e) {
            ExloggerUtils::log(json_encode(Result::innerResultFail($e)), 'error');
            $this->ajaxReturn(Result::innerResultFail($e));
        }
    }

    //归集发交易脚本           两小时一次
    public function collectAction()
    {
        //curl http://usdt.app/home/CollectJob/collect/job_token/921a537fb5eaaa0cd7108f8ab59aa0f5
        try{
            $sing = md5('collectJob');
            if(I('job_token') == $sing) {
                $s = new UserAddrCoinCollectJob();
                $r = $s->collect_usdt_to_wallet(array());
            }
        }catch(\Exception $e) {
            ExloggerUtils::log(json_encode(Result::innerResultFail($e)), 'error');
            $this->ajaxReturn(Result::innerResultFail($e));
        }
    }

    //热转冷
    public function hottocoldAction()
    {
        //http://localhost/index.php?m=home&c=CollectJob&a=hottocold&job_token=c29a6afcc74f396f392527d0dc70ef9d
        try{
            $sing = md5('hottocoldJob');
            if(I('job_token') == $sing) {
                $s = new HotToColdJob();
                $s->collect_coin_form_hot_to_cold(array());
            }
        }catch(\Exception $e) {
            ExloggerUtils::log(json_encode(Result::innerResultFail($e)), 'error');
            $this->ajaxReturn(Result::innerResultFail($e));
        }
    }

    //交易状态查询脚本              20分钟一次
    public function collectstatusAction()
    {
        //http://localhost/index.php?m=home&c=CollectJob&a=collectstatus&job_token=c705a9aa08fdad80e3ceccc3dcd37465
        try{
            $sing = md5('collectstatusJob');
            if(I('job_token') == $sing) {
                $s = new UserAddrCoinCollectStatusJob();
                $s->check_collect_finish();
            }
        }catch(\Exception $e) {
            ExloggerUtils::log(json_encode(Result::innerResultFail($e)), 'error');
            $this->ajaxReturn(Result::innerResultFail($e));
        }
    }


}

