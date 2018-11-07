<?php
namespace Home\Controller;
use Think\Controller;
use Common\Service\UtilService;
use Common\Service\RsaService;
use Common\Service\Bitcoin;
use Common\Service\OmniService;
use Common\Util\CommonLogs;
use Common\Service\AddressService;

//极光推送相关类引入
Vendor("JPush.autoload");
use JPush\Client as JPushClient;
use JPush\Exceptions as JPushExceptions;

class MsgPushController extends Controller {


    public function jpushMsgAction()
    {
        layout(false);
        //$this->sendNotifyAll();

        //$registrationID = '1104a897928ce680415';
        //$res = $this->getDevices($registrationID);


        //$res = $this->addTags('1104a8979287df4dbdd',array('heyu'));

        $res =  $this->pushTag(array('heyu'),'您好啊,帅哥');

        print_r($res);
    }



    //添加rationId
    public function addRegistrationId()
    {
        $registration_id = '1104a897928ce680415';
        $client =  new JPushClient(C('JPUSH.PUSH_KEY'),C('JPUSH.PUSH_SECRET'));
        $res = $client->addRegistrationId($registration_id);
        print_r($res);
    }

    //获取alias和tags
    public function getDevices($registrationID){
        $client =  new JPushClient(C('JPUSH.PUSH_KEY'),C('JPUSH.PUSH_SECRET'));
        $result = $client->device()->getDevices($registrationID);
        return $result;
    }

    //添加tags
    public function addTags($registrationID,$tags){

        $client =  new JPushClient(C('JPUSH.PUSH_KEY'),C('JPUSH.PUSH_SECRET'));
        $result = $client->device()->addTags($registrationID,$tags);
        return $result;
    }

    //移除tags
    public function removeTags($registrationID,$tags){

        $client =  new JPushClient(C('JPUSH.PUSH_KEY'),C('JPUSH.PUSH_SECRET'));
        $result = $client->device()->removeTags($registrationID,$tags);
        return $result;
    }

    //标签推送
    public function pushTag($tags,$content){
        $client =  new JPushClient(C('JPUSH.PUSH_KEY'),C('JPUSH.PUSH_SECRET'));
        //$tags = implode(",",$tag);
        $result = $client->push()
            ->setPlatform(array('ios', 'android'))
            ->addTag($tags)                            //标签
            ->setNotificationAlert($content)           //内容
            ->send();
        return $result;

    }

    //别名推送
    public function pushAlias($userids,$content){

        $client =  new JPushClient(C('JPUSH.PUSH_KEY'),C('JPUSH.PUSH_SECRET'));
        $alias = implode(",",$userids);
        $result = $client->push()
            ->setPlatform(array('ios', 'android'))
            ->addAlias($alias)                      //别名
            ->setNotificationAlert($content)        //内容
            ->send();
        return $result;

    }
    //向所有设备推送消息（用于app公告）
    function sendNotifyAll($message='新的一天,祝大家有个美好的一天'){
        $client =  new JPushClient(C('JPUSH.PUSH_KEY'),C('JPUSH.PUSH_SECRET'));
        $push_payload = $client->push()
            ->setPlatform('all')
            ->addAllAudience()                       //别名
            ->setNotificationAlert($message);        //内容
        try {
            $result = $push_payload->send();
            print_r($result);
        } catch (JPushExceptions\APIRequestException $e) {
            $error_data['status'] = $e->getCode();
            $error_data['message'] = $e->getMessage();
        } catch (JPushExceptions\APIRequestException $e) {
            $error_data['status'] = $e->getCode();
            $error_data['message'] = $e->getMessage();
        }
        return $result;
    }
}