<?php
/*
 * 手续费管理
*/
namespace Admin\Controller;
use Think\Controller;

class HeadlineController extends AdminBaseController {

	private $_open_status = array('open','close');
	private $_headline_types = array(
        'NEWS'=>'新闻',
	    'PROJECT'=>'项目'
    );

    /**
     * addby : lly
     * date : 2018-09-12 10:30
     * used : 信息新闻采集
     */
    public function listAction()
    {
        $pageNow = I('p',1);
        $pagesize = 10;
        $platM = D('Headline');
        $w['a.id'] = array('gt',0);
        $data = $platM->getlist($w,$pageNow,$pagesize);
        $count = $platM->getcount($w);
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('headlinelist',$data);
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-09-13 15:00
     */
    public function addAction()
    {
        if(IS_POST)
        {
            $result = array('status'=>0,'message'=>'资讯添加成功','data'=>'');
            $headlineM = D('Headline');
            $content = $_POST['content'];
            if($headlineM->create())
            {
                $headlineM->rand_code = "HEADLINE_".guid().time();
                //不考虑内容只有图片的
                $headlineM->content = $content;
                $headlineM->insert_type = 'USER';  //录入类型为人为添加
                $headlineM->created_by = session("username");
                $headlineM->last_modified_by = session("username");
                try{
                    $res = $headlineM->add();
                    if($res){
                        $result['data'] = U('list');
                    }else{
                        $result['status'] = '-1';
                        $result['message'] = '添加资讯信息入库操作失败';
                    }
                }catch(\Exception $e) {
                    $result['status'] = $e->getCode();
                    $result['message'] = '添加资讯信息失败'.$e->getMessage();
                }
            } else{
                $result['status'] = '-1';
                $result['message'] = ' 参数出错: '.$headlineM->getError();
            }
            $this->ajaxReturn($result);
            return '';
        }
        $this->assign('headlineTypes',$this->_headline_types);
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
        $upload = new \Think\Upload(); // 实例化上传类
        $upload->maxSize   =     1024*1024*3 ;// 设置附件上传大小
        $upload->exts      =     array('png','jpg','jpeg','gif','bmp');// 设置附件上传类型
        $upload->rootPath  =      './Public/upload/Headline/'; // 设置附件上传根目录
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
            $imgname='/Public/upload/Headline/'.$upload->savePath.$upload->saveName.strstr($_FILES['upimg']['name'],'.');
            $result['status'] = 1;
            $result['imgpath'] = $imgname;
            $this->ajaxReturn($result);
            //$this->rspsJSON(1,$imgname);
        }
    }

    /**
     * addby : lly
     * date : 2018-09-13 16:34
     * used : 文章查看
     */
    public function showAction()
    {
        $randCode = I('randCode');
        $headlineM = D('Headline');
        $w_search['rand_code'] = array('eq',$randCode);
        $data = $headlineM->where($w_search)->find();
        $this->assign('headlineTypes',$this->_headline_types);
        $this->assign('datainfo',$data);
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-09-13 16:15
     * used : 资讯编辑、编辑处理,状态变更处理
    **/
    public function editAction(){
        $randCode = I('randCode');
        $headlineM = D('Headline');
        $w_search['rand_code'] = array('eq',$randCode);
        $data = $headlineM->where($w_search)->find();
        $result = array('status'=>0,'message'=>'资讯信息变更成功','data'=>'');
        if(IS_POST && !empty($data)){
            if(I('act')){
                $isDeleted = ( I('act') == 'close' ? 1 : 0 );
                $w['id'] = array('eq',$data['id']);
                $headlineM->where($w)->setField('is_deleted',$isDeleted);
                $this->ajaxReturn($result);
                return '';
            }
            if($headlineM->create())
            {
                $headlineM->id = $data['id'];
                //内容
                $content = $_POST['content'];
                $headlineM->content = $content;
                if(I('is_publish') > 0)
                {
                    $headlineM->published_time = empty($headlineM->published_time) ? date('Y-m-d H:i:s') : $headlineM->published_time;
                }
                try{
                    if(!$headlineM->where($w_search)->save())
                    {
                        $result['status'] = '-1';
                        $result['message'] = '您未做任何信息变更';
                    }else{
                        $result['data']  = U('list');
                    }
                }catch(\Exception $e) {
                    $result['status'] = $e->getCode();
                    $result['message'] = '更新资讯信息失败!'.$e->getMessage();
                }
            }else{
                $result['status'] = '-1';
                $result['message'] = '参数出错：'.$headlineM->getError();
            }
            $this->ajaxReturn($result);
            return '';
        }
        $this->assign('headlineTypes',$this->_headline_types);
        $this->assign('datainfo',$data);
        $this->display();
    }
    


	/**
     * addby : lly
     * date : 2018-09-12 16:36
     * used : 自动从http://www.coindog.com/获取
     */
	public function get_news_from_coindog()
    {
        $accessKey = 'd354a6bfd27ca662658a0557e2a399e9';
        $secretKey = '23c706a255fb709d';
        $httpParams = array(
            'access_key' => $accessKey,
            'date' => time()
        );
        //获取从last_id之后的文章列表(行不通，只能用增量查询)
        $last_ids = $this->_get_last_ids();

        $signParams = array_merge($httpParams, array('secret_key' => $secretKey));
        ksort($signParams);
        $signString = http_build_query($signParams);
        $httpParams['sign'] = strtolower(md5($signString));
        $url = 'http://api.coindog.com/topic/list?'.http_build_query($httpParams);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curlRes = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($curlRes, true);
        $headLineM = M(THeadlineInfo);
        foreach($json as $article_obj) {
            if(in_array($article_obj['id'],$last_ids)){
                continue;
            }else{
                $param['rand_code'] = 'HEADLINE_'.guid().time();
                $param['info_id'] = $article_obj['id'];
                $param['title'] = $article_obj['title'];
                $param['img_url'] = $article_obj['thumbnail'];
                $param['summary'] = $article_obj['summary'];
                $param['type'] = 'NEWS';
                $param['content'] = $article_obj['content'];
                $param['author'] = $article_obj['author'];
                $param['resource'] = $article_obj['resource'] ? $article_obj['resource'] : '金色财经' ;
                $param['resource_url'] = $article_obj['resource_url'] ? $article_obj['resource_url'] : $article_obj['url'];
                $param['is_published'] =  1;
                $param['published_time'] = $article_obj['published_time'];
                $param['created_by'] = 'system';
                $param['last_modified_by'] = 'system';
                $headLineM->data($param)->add();
            }
        }
    }

    /**
     * addby : lly
     * date : 2018-09-15 11:18
     * used : 触发获取资讯的函数
     */
    public function getNewsAction()
    {
        layout(false);
        $sourceid = I('fromid') ? I('fromid') : 1;
        if($sourceid == 1)
        {
            $this->get_news_from_coindog();
        }
    }

    /**
     * 增量获取头条的基数
     */
    private function  _get_last_ids()
    {
        $headlineM = M(THeadlineInfo);
        $w['from_sourceid']  = array('eq',1);
        $last_ids = $headlineM->where($w)->order('published_time desc')->page(1,20)->getField('info_id',true);
        return $last_ids;
    }

    /**
     * addby : lly
     * date : 2018-10-17 10:50
     * used : 举报信息列表
     */
    public function reportAction()
    {
        //项目唯一编码
        $randCode = I('randCode',0);
        $where['a.id'] = array('gt',0);
        $where['a.type'] = array('eq','HEADLINE');
        if(!empty($randCode)){
            $where['a.rand_code'] = array('eq',trim($randCode));
        }
        $pageNow = I('p',1);
        $pageSize = 20;
        $reportM = M('TReport');
        $count = $reportM->alias('a')->where($where)->count();
        $pageNow = $pageNow >$count ? $count : ( $pageNow < 1 ? 1: $pageNow);
        $data = $reportM->alias('a')->join('t_headline_info as b on a.rand_code=b.rand_code')->field('a.*,b.title')->where($where)->page($pageNow .','. $pageSize)->order('a.id desc')->select();
        $Page = new  \Think\AdminPage ( $count, $pageSize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('reportList',$data);
        $this->display();
    }
}