<?php
/*
 * 项目管理
*/
namespace Admin\Controller;
use Think\Controller;




Vendor("JPush.autoload");

use JPush\Client as JPushClient;

use JPush\Exceptions as JPushExceptions;

use Common\Utils\ExloggerUtils;

class DatamsgController extends AdminBaseController {

    private $pro_status = array(
        'BEGIN'=>'PUSHCFG_FBAEAA8E1541404835'
    );


    public function __construct()
    {
        parent::__construct();
        self::_getRedisCfg();
    }

    /**
     * addby : lly
     * date : 2018-09-17 10:14
     * used : 系统消息推送列表
     */
    public function sysPushListAction()
    {
        //$this->_pushmsg();
        $pageNow = I('p',1);
        $pagesize = 10;
        $syspushM = M('TSysPushLog');
        $w['a.id'] = array('gt',0);
        $w['b.type'] = array('eq','SYS');
        $fields = 'a.push_rand_code,a.created_date,b.summary';
        $data = $syspushM->alias('a')->field($fields)->join('t_push_config as b on a.push_rand_code=b.rand_code')->where($w)->order('a.id desc')->page($pageNow .','. $pagesize)->select();
        $count = $syspushM->alias('a')->join('t_push_config as b on a.push_rand_code=b.rand_code')->where($w)->count();
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('listData',$data);
        $this->display('syslist');
    }
    /**
     * addby : lly
     * date : 2018-09-17 10:14
     * used : 项目推送列表
     */
    public function proPushListAction()
    {
        $pageNow = I('p',1);
        $pagesize = 10;
        $syspushM = M('TProPushLog');
        $w['a.id'] = array('gt',0);
        $w['b.type'] = array('eq','PROJECT');
        $fields = 'a.pro_rand_code,a.push_rand_code,a.user_id,a.udid,a.registration_id,a.created_date,b.summary';
        $data = $syspushM->alias('a')->field($fields)->join('t_push_config as b on a.push_rand_code=b.rand_code')->where($w)->order('a.id desc')->page($pageNow .','. $pagesize)->select();
        $count = $syspushM->alias('a')->join('t_push_config as b on a.push_rand_code=b.rand_code')->where($w)->count();
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('listData',$data);
        $this->display('prolist');
    }


    /**
     * 价格获取日志表
     */
    public function priceListAction()
    {
        $pageNow = I('p',1);
        $pagesize = 10;
        $priceM = M('TProPricelist');
        $w['id'] = array('gt',0);
        $data = $priceM->where($w)->order('id desc')->page($pageNow .','. $pagesize)->select();
        $count = $priceM->where($w)->count();
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('listData',$data);
        $this->display();
    }



    //测试全局推送和注册id推送
    private function _pushmsg($pushMsg,$registrationIds=array())
    {
        $client =  new JPushClient(C('JPUSH.PUSH_KEY'),C('JPUSH.PUSH_SECRET'));
        $error_data = array('status'=>0,'message'=>'ok');
        if(!empty($registrationIds)) {
            //$registrationIdStr = implode(",",$registrationIds);
            $push_payload = $client->push()
                ->setPlatform(array('ios', 'android'))
                ->addRegistrationId($registrationIds)
                ->setNotificationAlert($pushMsg);
        }else{
            $push_payload = $client->push()
                ->setPlatform('all')
                ->addAllAudience()
                ->setNotificationAlert($pushMsg);
        }
        try {
            $push_payload->send();
        } catch (JPushExceptions\APIRequestException $e) {
            $error_data['status'] = $e->getCode();
            $error_data['message'] = $e->getMessage();
        } catch (JPushExceptions\APIRequestException $e) {
            $error_data['status'] = $e->getCode();
            $error_data['message'] = $e->getMessage();
        }
        print_r($error_data);
        return $error_data;
    }

    //系统消息推送
    public function pushSysInfoAction()
    {
        $w['is_deleted'] = array('eq',0);
        $w['type'] = array('eq','SYS');
        $pushM = M('TPushConfig');
        $pushInfo = $pushM->where($w)->find();
        if(!empty($pushInfo) && !empty($pushInfo['summary'])){
            $res = $this->_pushmsg($pushInfo['summary']);
            if($res['message'] === 'ok')
            {
                //把系统那条消息作废掉,以防再次推送
                $upw['id'] = array('eq',$pushInfo['id']);
                $pushM->where($upw)->setField('is_deleted',1);
                //记录推送日志
                $insertData['push_rand_code'] = $pushInfo['rand_code'];
                $insertData['push_status'] = 1;
                $insertData['created_by'] = 'system';
                $insertData['last_modified_by'] = 'system';
                M('TSysPushLog')->data($insertData)->add();
            }else{
                ExloggerUtils::log('推送编码为：'.$pushInfo['rand_code'].';内容为：'.$pushInfo['summary'].'失败,返回结果为：'.$res['status'].':'.$res['message'],'error');
            }
        }
    }

    /**
     * 关注项目信息推送
     * 根据需求定制
     */

    //http://www.jlhapp.com/admin/datamsg/autoPushPro
    public function autoPushProAction()
    {
        /* 用户设备记录表 t_user_device_info */
        /* 项目用户关注表 t_project_follow */

        /*
         * 比如某个项目即将开始了，需要推送给相关关注的用户则推送如1
         */
        layout(false);
        //场景1
        $this->_sinceBegin();

    }


    //获取项目用户关注信息
    private function _sinceBegin()
    {
        $proList = $this->_getProList();
        $pushM = M('TProPushLog');
        //项目提醒如果推送过了就不提醒了
        foreach($proList as $obj){
            $w['push_rand_code'] = array('eq',$this->pro_status['BEGIN']);
            $w['pro_rand_code'] = $obj;
            $count = $pushM->where($w)->count();
            if($count>0){
                continue;
            }
            //推送项目消息,自己去找需要推送的用户
            $this->_pushpromsg($obj,$this->pro_status['BEGIN']);
        }
    }

    //获取需要发送项目消息的项目编码
    private function _getProList($status='BEGIN')
    {
        //项目必须是有效的
        $w['is_deleted'] = array('eq',0);

        if($status=='BEGIN'){
            //获取还有2个小时之内就要开始众筹的
            $w['crowd_status'] = $status;
            $w['crowd_start_time'] = array('lt',date("Y-m-d H:i:s",time()+7200));
            $w['crowd_end_time'] = array('gt',date("Y-m-d H:i:s",time()-7200));
        }elseif($status=='CROWDED'){
            //获取还有2个小时之内就要众筹结束的
            $w['crowd_status'] = 'CROWDING';
            $w['crowd_end_time'] = array('lt',date("Y-m-d H:i:s",time()+7200));
        }
        $proList = M('TProject')->where($w)->getField('rand_code',true);
        return $proList;
    }

    //根据项目编码获取所有关注用户的registrationID,在极光推送的时候用
    private  function _pushpromsg($pro_rand_code,$push_rand_code)
    {
        //用户必须是已关注状态
        $w['a.is_follow'] = array('eq',1);
        //用户关注记录必须是有效的
        $w['a.is_deleted'] = array('eq',0);
        $w['a.project_rand_code'] = array('eq',$pro_rand_code);
        //获取用户的registration_id

        //一次最多1000,购买了以后就不限制了，先不分页
        $registrationIds = M('TProjectFollow')->alias('a')->join('t_user_device_info as c on a.open_id=c.open_id',inner)->where($w)->getField('registration_id',true);
        if(empty($registrationIds)){
            echo $pro_rand_code.'项目 没有用户关注';
            return '';
        }
        $this->_pushProInfo($registrationIds,$push_rand_code,$pro_rand_code);
    }

    //项目消息推送
    private function _pushProInfo($registrationIds,$push_rand_code,$pro_rand_code)
    {
        $w['is_deleted'] = array('eq',0);
        $w['type'] = array('eq','PROJECT');
        $w['rand_code'] = array('eq',$push_rand_code);
        $pushM = M('TPushConfig');
        $pushInfo = $pushM->where($w)->find();
        if(!empty($pushInfo) && !empty($pushInfo['summary'])){
            $res = $this->_pushmsg($pushInfo['summary'],$registrationIds);
            if($res['message'] === 'ok')
            {
                //记录项目推送日志
                $this->_insertProPushDetail($pro_rand_code,$push_rand_code);
            }else{
                ExloggerUtils::log('推送编码为：'.$pushInfo['rand_code'].';内容为：'.$pushInfo['summary'].'失败,返回结果为：'.$res['status'].':'.$res['message'],'error');
            }
        }
    }
    //推送日志入库操作
    private function _insertProPushDetail($pro_rand_code,$push_rand_code)
    {
        $sql = 'insert into t_pro_push_log(`user_id`,`udid`,`registration_id`,`pro_rand_code`,`push_rand_code`,`push_status`,`created_by`,`last_modified_by`) ';
        $sql .= " select a.open_id,b.udid,b.registration_id,'{$pro_rand_code}','{$push_rand_code}',1,'system','system' from t_project_follow as a inner join t_user_device_info as b ";
        $sql .=" on a.open_id=b.open_id where a.project_rand_code='{$pro_rand_code}' and a.is_follow=1 and a.is_deleted=0";
        M('TProPushLog')->execute($sql);
    }




    /********** 以下是为了前端录入的只负责展示  **********/

    //项目用户关注信息
    public function proFloolwListAction()
    {
        $pageNow = I('p',1);
        $pagesize = 10;
        $randCode = I('randCode');
        $w['id'] = array('gt',0);
        if(!empty($randCode)){
            $w['project_rand_code'] = array('eq',$randCode);
        }
        $followM = M('TProjectFollow');
        $fields = 'open_id,project_rand_code,is_follow,app,is_deleted,created_date,last_modified_date';
        $data = $followM->field($fields)->where($w)->order('project_rand_code')->page($pageNow .','. $pagesize)->select();
        $count = $followM->where($w)->count();
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('listData',$data);
        $this->display('profloowlist');
    }

    //用户投资记录表
    public function investRecordAction()
    {
        $pageNow = I('p',1);
        $pagesize = 10;
        $randCode = I('randCode');
        $w['id'] = array('gt',0);
        if(!empty($randCode)){
            $w['project_rand_code'] = array('eq',$randCode);
        }
        $investM = M('TInvestRecord');
        $fields = 'open_id,project_rand_code,invest_unit,invest_amount,app,is_deleted,created_date,last_modified_date';
        $data = $investM->field($fields)->where($w)->order('project_rand_code')->page($pageNow .','. $pagesize)->select();
        $count = $investM->where($w)->count();
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('listData',$data);
        $this->display('investlist');
    }
    //用户投资流水表
    public function investRecordLogAction()
    {
        $pageNow = I('p',1);
        $pagesize = 10;
        $randCode = I('randCode');
        $w['id'] = array('gt',0);
        if(!empty($randCode)){
            $w['project_rand_code'] = array('eq',$randCode);
        }
        $investM = M('TInvestRecordLog');
        $fields = 'open_id,project_rand_code,invest_unit,invest_amount,app,is_deleted,created_date,last_modified_date';
        $data = $investM->field($fields)->where($w)->order('project_rand_code')->page($pageNow .','. $pagesize)->select();
        $count = $investM->where($w)->count();
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('listData',$data);
        $this->display('investlistlog');
    }






    /********** redis  **********/
    //推送消息配置表走缓存
    private static function _getPushConfig()
    {
        $w['is_deleted'] = array('eq',0);
        $data = M('TPushConfig')->field("rand_code,title,img_url,summary")->where($w)->select();
        if(!empty($data))
        {
            $res = '';
            foreach($data as $k=>$value)
            {
                $randCode = $value['rand_code'];
                unset($value['rand_code']);
                $res[$randCode] = $value;
            }
            S('sys_push_config',$res);
        }
    }

    //获取所需的redis
    private static function _getRedisCfg()
    {
        if(empty(S('sys_push_config'))){
            self::_getPushConfig();
        }
    }
}