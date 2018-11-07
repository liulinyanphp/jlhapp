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
//引入抓包请求工具类
use \Common\Service\UtilService;
use \Org\Util\simple_html_dom;

class NoticeController extends AdminBaseController {
    /**
     * addby : lly
     * date : 2018-09-17 10:14
     * used : 广告列表
     */
    public function listAction()
    {
        $pageNow = I('p',1);
        $pagesize = 10;
        $projectM = D('Notice');
        $w['id'] = array('gt',0);
        $data = $projectM->getlist($w,$pageNow,$pagesize);
        $count = $projectM->getcount($w);
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('listData',$data);
        $this->display();
    }
    /**
     * addby : lly
     * date : 2018-09-17 13:36
     * used : 添加广告
     */
    public function addAction()
    {
        if(IS_POST)
        {
            $noticeM = D('Notice');
            $content = $_POST['content'];
            if($noticeM->create())
            {
                //不考虑内容只有图片的
                $noticeM->content = $content;
                $noticeM->created_by = session("username");
                $noticeM->last_modified_by = session("username");
                try{
                    if(!$noticeM->add()){
                        Exception::throwsErrorMsg(C('NOTICE_DB_ERROR'));
                    }
                }catch(\Exception $e) {
                    $this->ajaxReturn(Result::innerResultFail($e));
                }
            } else{
                $err_array = array('-1','参数出错: '.$noticeM->getError());
                $this->ajaxReturn(Result::selfResultFail($err_array));
            }
            $this->ajaxReturn(Result::innerResultSuccess());
        }
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-09-19 17:45
     * used : 删除广告处理
     */
    public function cg_statusAction()
    {
        $id = I('id',0,'intval');
        $act = I('act');
        $projectM = D('Notice');
        $data = $projectM->find($id);
        if($id>0 && $act && in_array($act,C('OPEN_CLOSE_BTN')) && !empty($data))
        {
            $isDeleted = ( $act == 'open' ? 0 : 1 ) ;
            try{
                $w['id'] = $data['id'];
                $projectM->where($w)->setField('is_deleted',$isDeleted);
            }catch(\Exception $e) {
                $this->ajaxReturn(Result::innerResultFail($e));
            }
            $this->ajaxReturn(Result::innerResultSuccess());
        }else{
            $this->ajaxReturn(Result::selfResultFail(C('PRO_DB_ERROR.pro_parame')));
        }
    }




    /**
     * addby : lly
     * date : 2018-09-19 17:56
     * used : 广告编辑、广告处理
    **/
    public function editAction(){
    	$id = I('id',0,'intval');
		$noticeM = D('Notice');
		$data = $noticeM->find($id);
    	if(IS_POST){
            if($noticeM->create())
            {
                //内容
                $content = $_POST['content'];
                $noticeM->content = $content;
                try{
                    if(!$noticeM->save())
                    {
                        Exception::throwsErrorMsg(C('ERROR_NO_CHANGE.notice_edit'));
                    }
                }catch(\Exception $e) {
                    $this->ajaxReturn(Result::innerResultFail($e));
                }
            }else{
                $error_arr = array('-1',$noticeM->getError());
                $this->ajaxReturn(Result::selfResultFail($error_arr));
            }
            $this->ajaxReturn(Result::innerResultSuccess());
        }else{
            $this->assign('noticeInfo',$data);
            $this->display();
        }
    }


    /**
     * addby : lly
     * date : 2018-09-25 15:07
     * used : 从非小号获取平台公告列表
     */
    public function getNoticeAction()
    {
        layout(false);
        $logdir = ROOT.'/Public/upload/Notice';
        $isdebug = 0;
        if($isdebug){
            $fileName = $logdir.'/'.'feixiaohao_1.html';
        }else{
            $curl_url = 'https://www.feixiaohao.com/notice/';
            $source_url = 'https://www.feixiaohao.com/';
            $curl_service = new UtilService();
            $info = $curl_service->moni_brower_curl($curl_url,$source_url);
            $fileName = $logdir.'/'.'feixiaohao_1.html';
            file_put_contents($fileName,$info);
        }
        import("Org.Util.simple_html_dom");
        //dom参考文档：http://microphp.us/plugins/public/microphp_res/simple_html_dom/manual.htm#section_quickstart
        $noticeM = M('TNotice');
        if(file_exists($fileName)) {
            $str = file_get_contents($fileName);
            //正则
            $div = '/<ul class=noticeList>(.*?)<\/ul>/ism';
            preg_match($div, $str, $linearr);
            if(!empty($linearr[1]))
            {
                $html = str_get_html($linearr[1]);
                foreach($html->find('li') as $liobj)
                {
                    $tmp =  array();
                    $tmp_title = $liobj->find('a',1)->getAttribute("title");
                    $lastTitle_arr = $this->getLastNotice();
                    if(in_array($tmp_title,$lastTitle_arr)){
                        continue ;
                    }
                    $tmp['title'] = $tmp_title;
                    $tmp['plat_url'] = 'http://'.$liobj->find('a',0)->getAttribute('href');
                    $tmp['plat_name'] = $liobj->find('a',0)->plaintext;
                    $tmp['plat_logo'] = $liobj->find('img',0)->getAttribute("src");
                    $tmp['published_time'] = $liobj->find(".time",0)->plaintext;

                    $tmp['source_url'] = $detail_url = $liobj->find('a',1)->getAttribute("href");
                    //echo $html->find("li",0)->children(1)->getAttribute('href');
                    if(strpos($detail_url,'/notice/')!==false && strpos($detail_url,'/notice/')===0){
                        //$tmp['notice_link_desc'] = '非小号内部';
                        $tmp['is_published'] =  1;
                        //获取详细内容
                        $tmp['content'] = $this->getDetail($detail_url);
                    }else{
                        //$tmp['notice_link_desc'] = '非小号外部';
                        $tmp['content'] = '详情请查看原文链接'.$detail_url;
                    }
                    $tmp['created_by'] =  session("username");
                    $tmp['last_modified_by'] =  session("username");
                    $noticeM->data($tmp)->add();
                }
            }
        }
    }

    /**
     * @param $notice_href
     * daddy ： lly
     * date : 2018-09-26 16:48
     * used : 获取非小号公告详情
     */
    private function getDetail($notice_href)
    {
        if(empty($notice_href)){
            return '链接不可用';
        }
        $isdug = 0;
        $html_tpl = ROOT.'/Public/upload/Notice/Detail';
        if($isdug){
            $fileName = $html_tpl.'/3602792.html';
        }else{
            $curl_url = 'https://www.feixiaohao.com/'.$notice_href;
            $source_url = 'https://www.feixiaohao.com/notice/';
            $curl_service = new UtilService();
            $info = $curl_service->moni_brower_curl($curl_url,$source_url);
            $fileName = $html_tpl.'/'.substr($notice_href,8);
            file_put_contents($fileName,$info);
        }
        if(file_exists($fileName)){
            import("Org.Util.simple_html_dom");
            $fileInfo = file_get_contents($fileName);
            $html = str_get_html($fileInfo);
            $str = $html->find(".artBox",0)->innertext;
            return $str;
        }else{
            $str = '内容不可用';
            return $str;
        }
    }

    /**
     * addby : lly
     * date : 2018-09-26 17:38
     * used : 获取最近20条title
     */
    private function getLastNotice()
    {
        return M('TNotice')->order('id desc')->limit(20)->getField('title',true);
    }

}