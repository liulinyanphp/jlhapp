<?php
/*
 * 项目管理
*/
namespace Admin\Controller;
use Think\Controller;
//引入公用的结果处理类
use \Org\Util\Result;
//引入公用的异常抛出类
use \Org\Util\Exception;

Vendor("JPush.autoload");

use JPush\Client as JPushClient;

use JPush\Exceptions as JPushExceptions;

class ProjectController extends AdminBaseController {

	private $_open_status = array('open','close');

	public function __construct()
    {
        parent::__construct();
        S('pro_investment_ins',null);
        S('pro_channel',null);
        S('pro_competitor',null);
        S('pro_token_desc',null);
        self::_getRedisCfg();
    }

    /**
     * addby : lly
     * date : 2018-09-17 10:14
     * used : 项目列表
     */
    public function listAction()
    {
        $pageNow = I('p',1);
        $pagesize = 10;
        $projectM = D('Project');
        $w['a.id'] = array('gt',0);
        $data = $projectM->getlist($w,$pageNow,$pagesize);
        $count = $projectM->getcount($w);
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('proType',C('PROJECT_TYPE'));
        $this->assign('listData',$data);
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-09-19 11:53
     * used : 项目日志表
     */
    public function logListAction()
    {
        $pageNow = I('p',1);
        $pagesize = 15;
        $proLogM = M('TProjectLog');
        $w['id'] = array('gt',0);
        $data = $proLogM->where($w)->order('created_date desc')->page($pageNow .','. $pagesize)->select();
        $count = $proLogM->where($w)->count();
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('proType',C('PROJECT_TYPE'));
        $this->assign('listData',$data);
        $this->display('logList');
    }

    /**
     * addby : lly
     * date : 2018-09-17 13:36
     */
    public function addAction()
    {
        if(IS_POST)
        {
            $result = array('status'=>0,'message'=>'项目添加成功','data'=>'');
            $projectM = D('Project');
            $content = $_POST['content'];
            if($projectM->create($_POST,1))
            {
                $trans = M();
                $trans->startTrans();
                $protype = $projectM->type;
                //随机码
                $projectM->rand_code = $randCode = 'PRO_'.guid().time();
                //不考虑内容只有图片的
                $projectM->content = $content;
                $projectM->created_by = session("username");
                $projectM->last_modified_by = session("username");
                try{
                    //各自的关联表入库
                    $mapping_array = array_merge(I('token_mapping'),I('investment_mapping'),I('channel_mapping'),I('competitor_mapping'),array(I('analysis_mapping')));
                    foreach($mapping_array as $v)
                    {
                        $mappingM = M('TProjectMapping');
                        $parames['rand_code'] = $randCode;
                        $parames['mapping_rand_code'] = $v;
                        $parames['type'] = explode('_',$v)[0];
                        if(!$mappingM->data($parames)->add()){
                            Exception::throwsErrorMsg(array('55555',' 数据出错: '.$mappingM->getError()));
                            break;
                        }
                    }
                    $location_imgurl = self::_drawImg(I('token_mapping'),$randCode);
                    if(!empty($location_imgurl))
                    {
                        $projectM->token_allocation_imgurl = $location_imgurl;
                    }
                    $res = $projectM->add();
                    if($res){
                        //项目方地址从t_eth_address表中自动获取
                        $up_data = array('project_id'=>$res,'is_used'=>1);
                        M("TEthAddress")->where('is_used=0')->limit(1)->setField($up_data);
                        //获取临时占位的地址
                        $tmp_address = M("TEthAddress")->where("project_id=$res")->getField('address');
                        if(empty($tmp_address))
                        {
                            Exception::throwsErrorMsg(C('ADDRESS_DB_ERROR.address_empty'));
                        }
                        M('TProject')->where("id=$res")->setField('project_address',$tmp_address);
                        //记录日志
                        $w['id'] = array('eq',$res);
                        $inserted_data = $projectM->where($w)->find();
                        unset($inserted_data['id']);
                        if(!M('TProjectLog')->add($inserted_data)){
                            Exception::throwsErrorMsg(array('-1',' 数据出错: '.$projectM->getError()));
                        }
                    }else{
                        Exception::throwsErrorMsg(array('-1',' 数据出错: '.$projectM->getError()));
                    }
                }catch(\Exception $e) {
                    $result['status'] = $e->getCode();
                    $result['message'] = '添加项目信息失败'.$e->getMessage();
                    $trans->rollback();
                    $this->ajaxReturn($result);
                    return '';
                }
                //根据记录的结果来判断是否需要提交事务
                if($result['status'] == 0) {
                    $trans->commit();
                    $result['data'] = U('list');
                }
            } else{
                $result['status'] = '-1';
                $result['message'] = ' 参数出错: '.$projectM->getError();
            }
            $this->ajaxReturn($result);
            return '';
        }
        //众筹/预约单位
        $this->assign('FiUnit',S('pro_crowd_fi_unit'));
        //项目类型
        $this->assign('proType',C('PROJECT_TYPE'));
        //评级级别
        $this->assign('proRating',S('pro_rating'));
        //token分配方案
        $this->assign('tokenCfg',S('pro_token_desc'));
        //投资机构配置
        $this->assign('investmentCfg',S('pro_investment_ins'));
        //众筹渠道
        $this->assign('channelCfg',S('pro_channel'));
        //竞品配置
        $this->assign('competitorCfg',S('pro_competitor'));
        //行业分析
        $this->assign('analysisCfg',S('pro_analysis'));
        $this->display();
    }


    /**
     * addby : lly
     * date : 2018-09-13 15:17
     * used : 图片上传
     */
    public function UploadImgAction()
    {
        layout(false);
        //$this->rspsJSON(1,'/Public/upload/common_ad/2017-07-05/ad149924598282919.png');
        //上传图片
        $upFilepath = I('upFile') ? I('upFile') : 'Project';
        $upload = new \Think\Upload(); // 实例化上传类
        $upload->maxSize   =     1024*1024*3 ;// 设置附件上传大小
        $upload->exts      =     array('png','jpg','jpeg','gif','bmp');// 设置附件上传类型
        $upload->rootPath  =      './Public/upload/'.$upFilepath.'/'; // 设置附件上传根目录
        $upload->savePath  =      date('Y-m-d').'/'; // 设置附件上传（子）目录
        $upload->autoSub   = false;
        //随机生成文件名
        $upload->saveName = 'ad'.time().mt_rand(10000,99999);
        $upload->replace = true;
        // 上传文件
        $info = $upload->uploadOne($_FILES['upimg']);
        if(!$info) {// 上传错误提示错误信息
            echo $upload->getError();
        }else{// 上传成功 获取上传文件信息
            $imgname='/Public/upload/'.$upFilepath.'/'.$upload->savePath.$upload->saveName.strstr($_FILES['upimg']['name'],'.');
            $result['status'] = 1;
            $result['imgpath'] = $imgname;
            $this->ajaxReturn($result);
        }
    }




    /**
     * addby : lly
     * date : 2018-09-13 16:15
     * used : 资讯编辑、编辑处理,状态变更处理
    **/
    public function editAction(){
        $randCode = I('randCode');
    	$w['rand_code'] = $randCode;
		$projectM = D('Project');
		$data = $projectM->where($w)->find();
        $result = array('status'=>0,'message'=>'项目信息变更成功','data'=>'');

    	if(IS_POST){
            if($projectM->create())
            {
                $protype = $projectM->type;
                $projectM->last_modified_by = session("username");
                //内容
                $content = $_POST['content'];
                $projectM->content = $content;

                $trans = M();
                $trans->startTrans();
                try{
                    //各自的关联表入库
                    $mapping_array = array_merge(I('token_mapping'),I('investment_mapping'),I('channel_mapping'),I('competitor_mapping'),array(I('analysis_mapping')));
                    $mappingM = M('TProjectMapping');
                    $mappingM->where($w)->delete();
                    foreach($mapping_array as $v)
                    {
                        $parames['rand_code'] = $randCode;
                        $parames['mapping_rand_code'] = $v;
                        $parames['type'] = explode('_',$v)[0];
                        if(!$mappingM->data($parames)->add()){

                            Exception::throwsErrorMsg(array('55555',' 数据出错: '.$mappingM->getError()));
                            break;
                        }
                    }
                    //获取之前的token和现在选的是否一致,如果不一致的话,就重新画
                    $location_imgurl = self::_drawImg(I('token_mapping'),$randCode);
                    if(!empty($location_imgurl))
                    {
                        $projectM->token_allocation_imgurl = $location_imgurl;
                    }
                    if($projectM->where($w)->save())
                    {
                        //记录日志
                        $w['rand_code'] = array('eq',$randCode);
                        $inserted_data = $projectM->where($w)->find();
                        $inserted_data['pro_id'] = $inserted_data['id'];
                        unset($inserted_data['id']);
                        M('TProjectLog')->add($inserted_data);
                    }
                    //修改其他的
                    if($protype=='PUBLIC') {
                        //录入到众筹表
                        $crowdM = D('ProjectCrowd');
                        $crowdM->where($w)->setField('is_deleted',1);
                        if($crowdM->create()){
                            $crowdM->rand_code = $randCode;
                            $crowdM->add();
                        }else{
                            $result['status'] = '-1';
                            $result['message'] = ' 众筹参数出错: '.$crowdM->getError();
                        }
                    }elseif($protype==3 || $protype==4){
                        //录入到预约表
                        $orderM = D('ProjectOrder');
                        $orderM->where($w)->setField('is_deleted',1);
                        if($orderM->create()){
                            $orderM->rand_code = $randCode;
                            $orderM->add();
                        }else{
                            $result['status'] = '-1';
                            $result['message'] = ' 预约参数出错: '.$orderM->getError();
                            $trans->rollback();
                            $this->ajaxReturn($result);
                            return '';
                        }
                    }
                    //根据记录的结果来判断是否需要提交事务
                    if($result['status'] == 0) {
                        $trans->commit();
                        $result['data'] = U('list');
                    }else{
                        $trans->rollback();
                    }
                }catch(\Exception $e) {
                    $result['status'] = $e->getCode();
                    $result['message'] = '更新资讯失败!'.$e->getMessage();
                }
            }else{
                $result['status'] = '-1';
                $result['message'] = '参数出错：'.$projectM->getError();
            }
            $this->ajaxReturn($result);
            return '';
        }
        //

        $mappingCfg = M('TProjectMapping')->where($w)->getField('mapping_rand_code',true);
    	$this->assign('mappingCfg',$mappingCfg);
        //评级级别
        $this->assign('proRating',S('pro_rating'));
        //token分配方案
        $this->assign('tokenCfg',S('pro_token_desc'));
        //投资机构配置
        $this->assign('investmentCfg',S('pro_investment_ins'));
        //众筹渠道
        $this->assign('channelCfg',S('pro_channel'));
        //竞品配置
        $this->assign('competitorCfg',S('pro_competitor'));
        //行业分析
        $this->assign('analysisCfg',S('pro_analysis'));
        //众筹/预约单位
        $this->assign('FiUnit',S('pro_crowd_fi_unit'));

        $this->assign('proType',C('PROJECT_TYPE'));
        $this->assign('proInfo',$data);
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-09-19 10:31
     * used : 删除项目处理
     */
    public function cg_statusAction()
    {
        $randCode = I('randCode') ? I('randCode') : 'NONCE';
        $act = I('act');
        $projectM = D('Project');
        $w['rand_code'] = $randCode;
        $data = $projectM->where($w)->find();
        if($randCode !='NONCE' && $act && in_array($act,$this->_open_status) && !empty($data))
        {
            $isDeleted = ( $act == 'open' ? 0 : 1 ) ;
            $trans = M();
            $trans->startTrans();
            try{
                if($projectM->where($w)->setField('is_deleted',$isDeleted))
                {
                    //记录日志
                    $datalog = $data;
                    $datalog['is_deleted'] = $datalog;
                    unset($datalog['id']);
                    if(!M('TProjectLog')->add($datalog)){
                        Exception::throwsErrorMsg(C('PRO_DB_ERROR.logtb'));
                    }
                }else{
                    Exception::throwsErrorMsg(C('PRO_DB_ERROR.protb'));
                }
            }catch(\Exception $e) {
                $trans->rollback();
                $this->ajaxReturn(Result::innerResultFail($e));
            }
            $trans->commit();
            $this->ajaxReturn(Result::innerResultSuccess());
        }else{
            $this->ajaxReturn(Result::selfResultFail(C('PRO_DB_ERROR.pro_parame')));
        }
    }


    //评级配置表走缓存
    private static function _getRatingConfig()
    {
        $w['is_deleted'] = array('eq',0);
        $data = M('TRatingConfig')->field("rand_code,name")->where($w)->order('order_num asc,last_modified_date desc')->select();
        if(!empty($data))
        {
            $code = array_column($data,'rand_code');
            $value = array_column($data,'name');
            $res = array_combine($code,$value);
            S('pro_rating',$res);
        }
    }
    //token分配方案表走缓存
    private static function _getMappingConfig()
    {
        $w['is_deleted'] = array('eq',0);
        $w['type'] = array('eq','TOKEN');
        $data = M('TTokenMappingConfig')->field("rand_code,concat(txtcode,'% ',name) as token_desc")->where($w)->order('last_modified_date desc')->select();
        if(!empty($data))
        {
            $code = array_column($data,'rand_code');
            $value = array_column($data,'token_desc');
            $res = array_combine($code,$value);
            S('pro_token_desc',$res);
        }
    }
    //投资机构配置表走缓存
    private static function _getInvestmentConfig()
    {
        $w['is_deleted'] = array('eq',0);
        $data = M('TInvestmentIns')->field("rand_code,name,logo_img_url,link_url")->where($w)->order('last_modified_date desc')->select();
        if(!empty($data))
        {
            $res = '';
            foreach($data as $k=>$value)
            {
                $randCode = $value['rand_code'];
                unset($value['rand_code']);
                $res[$randCode] = $value;
            }
            S('pro_investment_ins',$res);
        }
    }
    //众筹渠道配置表走缓存
    private static function _getChannelConfig()
    {
        $w['is_deleted'] = array('eq',0);
        $data = M('TPublicChannel')->field("rand_code,name,logo_img_url,link_url")->where($w)->order('last_modified_date desc')->select();
        if(!empty($data))
        {
            $res = '';
            foreach($data as $k=>$value)
            {
                $randCode = $value['rand_code'];
                unset($value['rand_code']);
                $res[$randCode] = $value;
            }
            S('pro_channel',$res);
        }
    }
    //竞品配置表走缓存
    private static function _getCompetitorConfig()
    {
        $w['is_deleted'] = array('eq',0);
        $data = M('TCompetitorConfig')->field("rand_code,name")->where($w)->order('last_modified_date desc')->select();
        if(!empty($data))
        {
            $code = array_column($data,'rand_code');
            $value = array_column($data,'name');
            $res = array_combine($code,$value);
            S('pro_competitor',$res);
        }
    }
    //行业分析表走缓存
    private static function _getAnalysisConfig()
    {
        $w['is_deleted'] = array('eq',0);
        $data = M('TAnalysisConfig')->field("rand_code,title")->where($w)->order('last_modified_date desc')->select();
        if(!empty($data))
        {
            $code = array_column($data,'rand_code');
            $value = array_column($data,'title');
            $res = array_combine($code,$value);
            S('pro_analysis',$res);
        }
    }

    //众筹单位表走缓存
    private static function _getCrowUnitConfig()
    {
        $w['is_deleted'] = array('eq',0);
        $data = M('TCrowdUnitConfig')->field("rand_code,code")->where($w)->order('id asc')->select();
        if(!empty($data))
        {
            $code = array_column($data,'rand_code');
            $value = array_column($data,'code');
            $res = array_combine($code,$value);
            S('pro_crowd_fi_unit',$res);
        }
    }

    private static function _getRedisCfg()
    {
        //S('pro_competitor',null);
        if(empty(S('pro_rating'))){
            self::_getRatingConfig();
        }
        if(empty(S('pro_token_desc'))){
            self::_getMappingConfig();
        }
        if(empty(S('pro_investment_ins'))){
            self::_getInvestmentConfig();
        }
        if(empty(S('pro_channel'))){
            self::_getChannelConfig();
        }
        if(empty(S('pro_competitor'))){
            self::_getCompetitorConfig();
        }
        if(empty(S('pro_analysis'))){
            self::_getAnalysisConfig();
        }
        if(empty(S('pro_crowd_fi_unit'))){
            self::_getCrowUnitConfig();
        }
    }

    private static function _drawImg($mapping_arr,$randCode)
    {
        if(empty($mapping_arr)){
            return '';
        }
        $randCodeArr = array_filter($mapping_arr);
        if(is_array($randCodeArr))
        {
            $w['rand_code'] = array('in',$randCodeArr);
        }else{
            $w['rand_code'] = array('eq',$randCodeArr);
        }
        $w['type'] = 'TOKEN';
        $txtcodeArr = M('TTokenMappingConfig')->where($w)->getField('txtcode',true);
        if(empty($txtcodeArr))
        {
            return '';
        }

        $tmpw['rand_code'] = $randCode;
        $tmpw['type'] = 'TOKEN';
        $tmpw['mapping_rand_code'] = $w['rand_code'];
        $exitCount = M('TProjectMapping')->where($tmpw)->count();
        if($exitCount == count($mapping_arr))
        {
            return '';
        }
        Vendor('Jpgraph.jpgraph');
        Vendor('Jpgraph.jpgraph_pie');
        // Some data
        //$data = array(40,21,17,14,23);
        $data =$txtcodeArr;
        // Create the Pie Graph.
        $graph = new \PieGraph(350,250);
        $theme_class="DefaultTheme";
        //$graph->SetTheme(new $theme_class());
        // Set A title for the plot
        //$graph->title->Set("A Simple Pie Plot");
        $graph->SetBox(true);
        // Create
        $p1 = new \PiePlot($data);
        $graph->Add($p1);
        $p1->ShowBorder();
        $p1->SetColor('black');
        $p1->SetSliceColors(array('#1E90FF','#2E8B57','#ADFF2F','#DC143C','#BA55D3'));
        $tmpfileName = '/Public/upload/Jpgraph/'.time().'.png';
        $filename = ROOT.$tmpfileName;
        //$graph->Stroke("/tmp/jpgraph/a.png");
        $graph->Stroke($filename);
        return $tmpfileName;
    }

    /**
     * addby : lly
     * date : 2018-10-26 15:22
     */
    public function recommendAction()
    {

        $pageNow = I('p',1);
        $pagesize = 10;
        $commendM = D('Recommend');
        $w['a.type'] = $count_w['type'] = array('eq','PROJECT');
        $data = $commendM->getlist($w,$pageNow,$pagesize);
        $count = $commendM->getcount($count_w);
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('listData',$data);
        $this->display();
    }

    public function recommendAddAction()
    {
        if(IS_POST)
        {
            $result = array('result'=>0,'message'=>'添加成功','data'=>U('recommend'));
            $randCode = I('randCode');
            $orderNum = I('order_num') ? I('order_num') : 10;
            if(empty($randCode)){
                $result['result'] = '-1';
                $result['data'] = '';

            }else{
                $parames['rand_code'] = $randCode;
                $parames['type'] = 'PROJECT';
                $count = M('TRecommendInfo')->where($parames)->count();
                if($count>0)
                {
                    $result['result'] = '-1';
                    $result['message'] = '项目已经存在了,请在列表中改变状态即可,切勿重复推荐';
                    $result['data'] = '';
                }else {
                    $parames['order_num'] = $orderNum;
                    $parames['type'] = 'PROJECT';
                    $parames['created_by'] = session("username");
                    if (!M('TRecommendInfo')->data($parames)->add()) {
                        $result['result'] = '-1';
                        $result['message'] = '添加失败';
                        $result['data'] = '';
                    }
                }
            }
            $this->ajaxReturn($result);
            return '';
        }else{
            $w['is_deleted'] = array('eq',0);
            $data = M('TProject')->field("rand_code as randCode,name")->where($w)->select();
            if(!empty($data))
            {
                $data = array_combine(array_column($data,'randCode'),array_column($data,'name'));
            }
            $adorder = array(1,2,3,4,5,6,7,8,9,10);
            $this->assign('adorder',$adorder);
            $this->assign('proList',$data);
            $this->display();
        }

    }

    public function recommendEditAction()
    {
        $randCode = I('randCode') ? I('randCode') : 'NONCE';
        $w['rand_code'] = array('eq',$randCode);
        $recommendM = M('TRecommendInfo');
        $data = $recommendM->where($w)->find();
        $result = array('result'=>0,'message'=>'项目推荐信息变更成功','data'=>U('recommend'));
        if(IS_POST && !empty($data))
        {
            if(I('act')){
                $isDeleted = ( I('act') == 'close' ? 1 : 0 );
                $recommendM->where($w)->setField('is_deleted',$isDeleted);
                $this->ajaxReturn($result);
                return '';
            }
            $orderNum = I('order_num') ? I('order_num') : 10;
            $parames['order_num'] = $orderNum;
            if(!$recommendM->where($w)->save($parames))
            {
                $result['status'] = '-1';
                $result['message'] = '您未做任何信息变更';
                $result['data'] = '';
            }
            $this->ajaxReturn($result);
            return '';
        }
        $adorder = array(1,2,3,4,5,6,7,8,9,10);
        $this->assign('adorder',$adorder);
        $this->assign('dataInfo',$data);

        $prow['is_deleted'] = array('eq',0);
        $proData = M('TProject')->field("rand_code as randCode,name")->where($prow)->select();
        if(!empty($proData))
        {
            $proData = array_combine(array_column($proData,'randCode'),array_column($proData,'name'));
        }
        $this->assign('proList',$proData);
        $this->display();

    }

    public function pushmsgAction()
    {
        $client =  new JPushClient('e95c0ddb794c5ef2fcfdcaf4fdfd', 'b5db28345d5355d26e5da69a');
        $push_payload = $client->push()
            ->setPlatform('all')
            ->addAllAudience()
            ->setNotificationAlert('hi lly this is lly');
        try {
            $response = $push_payload->send();
            print_r($response);
        } catch (JPushExceptions\APIRequestException $e) {
            $error_data['status'] = $e->getCode();
            $error_data['message'] = $e->getMessage();
        } catch (JPushExceptions\APIRequestException $e) {
            $error_data['status'] = $e->getCode();
            $error_data['message'] = $e->getMessage();
        }
    }


    /**
     * 币价获取配置列表
     */
    public function priceConfigAction()
    {
        $pageNow = I('p',1);
        $pagesize = 10;
        $priceCfgM = M('TProGetpriceConfig');
        $w['a.id'] = array('gt',0);
        $data = $priceCfgM->alias('a')->join('t_project as b on a.pro_rand_code=b.rand_code', inner)->field('a.*,b.name,b.full_name')->where($w)->order('a.created_date desc')->page($pageNow .','. $pagesize)->select();
        $count = $priceCfgM->alias('a')->join('t_project as b on a.pro_rand_code=b.rand_code', inner)->where($w)->count();
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('sourceNames',C('SOURCE_NAMES'));
        $this->assign('listData',$data);
        $this->display();
    }

    /**
     * @return string
     * 币价获取配置添加
     */

    public function priceConfigAddAction()
    {
        if(IS_POST)
        {
            $result = array('result'=>0,'message'=>'添加成功','data'=>U('priceConfig'));
            $randCode = I('randCode');
            $sourceName = I('pro_price_getsource') ? I('pro_price_getsource') : 'FEIXIAOJAO';
            $aliasName = I('pro_alias_name') ? I('pro_alias_name') : '';

            if(trim($aliasName)=='')
            {
                $result['result'] = '-1';
                $result['data'] = '';
                $result['message'] = '别名不能为空';
            }elseif(empty($randCode)){
                $result['result'] = '-1';
                $result['data'] = '';
            } else{
                $countW['pro_rand_code'] = array('eq',$randCode);
                $count = M('TProGetpriceConfig')->where($countW)->count();
                if($count>0)
                {
                    $result['result'] = '-1';
                    $result['message'] = '项目已经存在了,请在列表中改变状态即可,切勿重复添加';
                    $result['data'] = '';
                }else {
                    $parames['pro_rand_code'] = $randCode;
                    $parames['pro_rand_code'] = $randCode;
                    $parames['pro_alias_name'] = $aliasName;
                    $parames['pro_price_getsource'] = $sourceName;
                    $parames['created_by'] = session("username");
                    $parames['last_modified_by'] = session("username");
                    if (!M('TProGetpriceConfig')->data($parames)->add()) {
                        $result['result'] = '-1';
                        $result['message'] = '添加失败';
                        $result['data'] = '';
                    }
                }
            }
            $this->ajaxReturn($result);
            return '';
        }else{
            $w['is_deleted'] = array('eq',0);
            $data = M('TProject')->field("rand_code as randCode,name")->where($w)->select();
            if(!empty($data))
            {
                $data = array_combine(array_column($data,'randCode'),array_column($data,'name'));
            }
            $this->assign('proList',$data);
            $this->display();
        }

    }
    public function priceConfigEditAction()
    {
        $randCode = I('randCode') ? I('randCode') : 'NONCE';
        $w['pro_rand_code'] = array('eq',$randCode);
        $pricecfgM = M('TProGetpriceConfig');
        $data = $pricecfgM->field('pro_rand_code,pro_alias_name,pro_price_getsource')->where($w)->find();
        $result = array('result'=>0,'message'=>'项目价格获取配置信息变更成功','data'=>U('priceConfig'));
        if(IS_POST && !empty($data))
        {
            if(I('act')){
                $isDeleted = ( I('act') == 'close' ? 1 : 0 );
                $pricecfgM->where($w)->setField('is_deleted',$isDeleted);
                $this->ajaxReturn($result);
                return '';
            }
            $parames['pro_price_getsource'] = I('pro_price_getsource') ? I('pro_price_getsource') : 'FEIXIAOJAO';
            $aliasName = $parames['pro_alias_name'] = I('pro_alias_name') ? I('pro_alias_name') : '';
            if(trim($aliasName)=='')
            {
                $result['result'] = '-1';
                $result['data'] = '';
                $result['message'] = '别名不能为空';
            }
            if(!$pricecfgM->where($w)->save($parames))
            {
                $result['status'] = '-1';
                $result['message'] = '您未做任何信息变更';
                $result['data'] = '';
            }
            $this->ajaxReturn($result);
            return '';
        }
        $this->assign('dataInfo',$data);
        $this->display();

    }


}