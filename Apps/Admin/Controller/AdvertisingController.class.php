<?php
namespace Admin\Controller;
use Think\Controller;

Vendor("Qiniu.autoload");

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class AdvertisingController extends AdminBaseController {

    //定义状态
    private $_open_status = array('open','close');


    /**
     * addby : lly
     * date : 2018-09-11 10:13
     * usde : 广告类型列表
     */
    public function adTypeListAction()
    {
        $pageNow = I('p',1);
        $pagesize = 10;
        $platM = D('AdType');
        $w['id'] = array('gt',0);
        $data = $platM->getlist($w,$pageNow,$pagesize);
        $count = $platM->getcount($w);
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('pager',$show);
        $this->assign('adtypelist',$data);
        $this->display('adtypelist');
    }

    /**
     * addby : lly
     * date : 2018-04-22 11:30
     * used : 展示的广告列表
    **/
    public function listAction()
    {  
        $pageNow = I('p',1);
        $pagesize = 10;
        $advertisingM = D('Advertising');
        $w['id'] = array('gt',0);
        $data = $advertisingM->getlist($w,$pageNow,$pagesize);
        $count = $advertisingM->getcount($w);
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出

        //广告类型
        $adTypelist = $this->_gettypelist();
        $adTypes = $this->_cgkey_value($adTypelist);


        $this->assign('pager',$show);
        $this->assign('adlist',$data);
        $this->assign('adTypes',$adTypes);
        $this->display();
    }
    
    /**
     * addby : lly
     * date : 2018-04-23 11:30
     * used : 平台添加、平台添加处理
    **/
    public function addAction(){
        $result = array('status'=>0,'message'=>'广告添加成功','data'=>'');
        if(IS_POST)
        {
            $adInfoM = D('Advertising');
            if($adInfoM->create())
            {
                $adInfoM->rand_code = 'AD_'.guid().time();
                $adInfoM->created_by = session("username");
                $adInfoM->last_modified_by = session("username");
                try{
                    $adInfoM->add();
                    $result['data'] = U('list');
                }catch(\Exception $e) {
                    $result['status'] = $e->getCode();
                    $result['message'] = '添加广告失败'.$e->getMessage();
                }
            } else{
                $result['status'] = '-1';
                $result['message'] = ' 参数出错: '.$adInfoM->getError();
            }
            $this->ajaxReturn($result);
            exit();
        }
        $adorder = array(1,2,3,4,5,6,7,8,9,10);
        $this->assign('adorder',$adorder);
        $this->assign('adtypelist',$this->_gettypelist());
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-04-15 01:25
     * used : 平台状态变更处理
     * desc : cg_plat全意为change_platform_statu
    **/
    public function cg_statusAction(){
        $result = array('status'=>0,'message'=>'广告位状态变更成功','data'=>array());
        $id = I('id',0,'intval');
        $act = I('act');
        $obj = I('obj') ? I('obj') : 'adlist';
        if($obj == 'adtypelist')
        {
            $adInfoM = D('AdType');
        }else{
            $adInfoM = D('Advertising');
        }
        $data = $adInfoM->find($id);
        if($id>0 && $act && in_array($act,$this->_open_status) && !empty($data))
        {
            $data['is_deleted'] = ( $act == 'open' ? 0 : 1 ) ;
            try{
                $adInfoM->data($data)->save();
                if($obj=='adtypelist'){
                    $result['data'] = U('adTypeList');
                }else{
                    $result['data'] = U('list');
                }
            }catch(\Exception $e) {
                $result['status'] = $e->getCode();
                $result['message'] = '状态变更失败'.$e->getMessage();
            }
        }else{
            $result['status']= '-1';
            $result['msg']= '参数出错!';
        }
        $this->ajaxReturn($result);
    }

    /**
     * addby : lly
     * date : 2018-09-10 17:59
     * used : 图片上传
     */
    public function UploadImgAction()
    {
        layout(false);
        //$this->rspsJSON(1,'/Public/upload/common_ad/2017-07-05/ad149924598282919.png');
        //上传图片
        $upload = new \Think\Upload(); // 实例化上传类
        $upload->maxSize   =     1024*1024*3 ;// 设置附件上传大小
        $upload->exts      =     array('png','jpg','jpeg');// 设置附件上传类型
        $upload->rootPath  =      './Public/upload/Advertising/'; // 设置附件上传根目录
        $upload->savePath  =      date('Y-m-d').'/'; // 设置附件上传（子）目录
        $upload->autoSub   = false;
        //随机生成文件名
        $upload->saveName = 'ad'.time().mt_rand(10000,99999);
        $upload->replace = true;

        $qiniufilepath = $_FILES['upimg']['tmp_name'];
        //上传到七牛云服务器
        $qiniuRes = $this->_qiniuUpload($qiniufilepath,$upload->saveName);
        if(isset($qiniuRes['key']))
        {
            $imgname = C('IMG_CDN').$qiniuRes['key'];
        }else{
            $imgname='/Public/upload/Advertising/'.$upload->savePath.$upload->saveName.strstr($_FILES['upimg']['name'],'.');
        }

        // 上传文件
        $info = $upload->uploadOne($_FILES['upimg']);


        if(!$info) {// 上传错误提示错误信息
            echo $upload->getError();
        }else{// 上传成功 获取上传文件信息
            //$imgname='/Public/upload/Advertising/'.$upload->savePath.$upload->saveName.strstr($_FILES['upimg']['name'],'.');
            $result['status'] = 1;
            $result['imgpath'] = $imgname;
            $this->ajaxReturn($result);
            //$this->rspsJSON(1,$imgname);
        }
    }

    /**
     * 获取广告类型列表
     */
    private function _gettypelist()
    {
        //广告类型列表
        $adtypeM = D('AdType');
        $typeW['is_deleted'] = array('eq',0);
        $typedata = $adtypeM->getTypeList($typeW);
        return $typedata;
    }

    //转换为key->value
    private function _cgkey_value($data)
    {
        $res = array();
        foreach($data as $value)
        {
            $id = $value['randCode'];
            $res[$id] = $value['name'];
        }
        return $res;
    }

    /**
     * @param $tmpfileName 临时文件名
     * @param $houzui  上传到七牛的文件名
     * @return mixed   七牛生成的文件名
     */
    private function _qiniuUpload($tmpfileName,$key)
    {
        $bucket = C('QINIU.BUCKET');
        $accessKey = C('QINIU.ACCESSKEY');
        $secretKey = C('QINIU.SECRETKEY');

        $expires = 6000;
        $auth = new Auth($accessKey, $secretKey);

        $policy = array(
            //'callbackUrl' => 'http://micuer.com/qiniuyun/examples/upload_verify_callback.php',
            'callbackBody' => 'key=$(key)&hash=$(etag)&bucket=$(bucket)&fsize=$(fsize)&name=$(x:name)',
            'callbackBodyType' => 'application/json'
        );
        $token = $auth->uploadToken($bucket, null, $expires, $policy, true);
        // 构建 UploadManager 对象
        $uploadMgr = new UploadManager();
        // 要上传文件的本地路径
        //$filePath = $_FILES['file']['tmp_name'];
        $filePath = $tmpfileName;

        // 上传到七牛后保存的文件名
        //$key = 'ad_'.date("YmdHis");
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        //echo "\n====> putFile result: \n";
        if ($err !== null) {
            //var_dump($err);
            return '';
        } else {
            //var_dump($ret);
            //print_r($ret);
            return $ret;
        }
    }

}