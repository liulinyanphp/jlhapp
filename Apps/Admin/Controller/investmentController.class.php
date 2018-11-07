<?php
/**
 * Created by PhpStorm.
 * User: linyanliu
 * Date: 2018/10/17
 * Time: 上午11:27
 */
namespace Admin\Controller;

class investmentController extends AdminBaseController{


    /**
     * addby : lly
     * date : 2018-10-17
     * used : 投研信息列表
     */
    public function listAction()
    {
        $pageNow = I('p',1);
        $pagesize = 10;
        $platM = D('Investment');
        $w['id'] = array('gt',0);
        $data = $platM->getlist($w,$pageNow,$pagesize);
        $count = $platM->getcount($w);
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('investmentList',$data);
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-10-17
     * used : 投研信息添加
     */
    public function addAction()
    {
        if(IS_POST) {
            $result = array('status'=>0,'message'=>'投研信息添加成功','data'=>'');
            $investmentM = D('Investment');
            $content = $_POST['content'];
            if($investmentM->create())
            {
                //不考虑内容只有图片的
                $investmentM->content = $content;
                $investmentM->rand_code = 'INVESTMENT_'.guid().time();
                $investmentM->created_by = session("username");
                $investmentM->last_modified_by = session("username");
                try{
                    $res = $investmentM->add();
                    if($res){
                        $result['data'] = U('list');
                    }else{
                        $result['status'] = '-1';
                        $result['message'] = '添加投研信息入库操作失败';
                    }
                }catch(\Exception $e) {
                    $result['status'] = $e->getCode();
                    $result['message'] = '添加投研信息失败'.$e->getMessage();
                }
            } else{
                $result['status'] = '-1';
                $result['message'] = ' 参数出错: '.$investmentM->getError();
            }
            $this->ajaxReturn($result);
            return '';
        }
        $this->display();
    }


    /**
     * addby : lly
     * date : 2018-10-17 15:07
     * used : 投研信息编辑、编辑处理,状态变更处理
     **/
    public function editAction(){
        $randCode = I('randCode') ? I('randCode') : 'NONCE';
        $investmentM = D('Investment');
        $w['rand_code'] = array('eq',$randCode);
        $data = $investmentM->where($w)->find();
        $result = array('status'=>0,'message'=>'投研信息变更成功','data'=>'');
        if(IS_POST && !empty($data)){
            if(I('act')){
                $isDeleted = ( I('act') == 'open' ? 0 : 1 );
                $investmentM->where($w)->setField('is_deleted',$isDeleted);
                $this->ajaxReturn($result);
                return '';
            }
            if($investmentM->create())
            {
                //内容
                $content = $_POST['content'];
                $investmentM->content = $content;
                try{
                    if(!$investmentM->where($w)->save())
                    {
                        $result['status'] = '-1';
                        $result['message'] = '您未做任何信息变更';
                    }else{
                        $result['data']  = U('list');
                    }
                }catch(\Exception $e) {
                    $result['status'] = $e->getCode();
                    $result['message'] = '更新投研信息失败!'.$e->getMessage();
                }
            }else{
                $result['status'] = '-1';
                $result['message'] = '参数出错：'.$investmentM->getError();
            }
            $this->ajaxReturn($result);
            return '';
        }
        $this->assign('datainfo',$data);
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
        $upload->rootPath  =      './Public/upload/Investment/'; // 设置附件上传根目录
        $upload->savePath  =      date('Y-m-d').'/'; // 设置附件上传（子）目录
        $upload->autoSub   = false;
        //随机生成文件名
        $upload->saveName = 'in'.time().mt_rand(10000,99999);
        $upload->replace = true;
        // 上传文件
        $info = $upload->uploadOne($_FILES['upimg']);
        if(!$info) {// 上传错误提示错误信息
            echo $upload->getError();
        }else{// 上传成功 获取上传文件信息
            $imgname='/Public/upload/Investment/'.$upload->savePath.$upload->saveName.strstr($_FILES['upimg']['name'],'.');
            $result['status'] = 1;
            $result['imgpath'] = $imgname;
            $this->ajaxReturn($result);
            //$this->rspsJSON(1,$imgname);
        }
    }

    /**
     * addby : lly
     * date : 2018-10-26 14:56
     * used : 投研信息举报列表
     */
    public function reportAction()
    {
        //项目唯一编码
        $randCode = I('randCode',0);
        $where['a.id'] = array('gt',0);
        $where['a.type'] = array('eq','INVEST');
        if(!empty($randCode)){
            $where['a.rand_code'] = array('eq',trim($randCode));
        }
        $pageNow = I('p',1);
        $pageSize = 20;
        $reportM = M('TReport');
        $count = $reportM->alias('a')->where($where)->count();
        $pageNow = $pageNow >$count ? $count : ( $pageNow < 1 ? 1: $pageNow);
        $data = $reportM->alias('a')->join('t_investment_research_info as b on a.rand_code=b.rand_code')->field('a.*,b.title')->where($where)->page($pageNow .','. $pageSize)->order('a.id desc')->select();
        $Page = new  \Think\AdminPage ( $count, $pageSize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('reportList',$data);
        $this->display();
    }

    public function showAction()
    {
        $randCode = I('randCode') ? I('randCode') : 'NONCE';
        $investmentM = D('Investment');
        $w['rand_code'] = array('eq',$randCode);
        $data = $investmentM->where($w)->find();
        $this->assign('datainfo',$data);
        $this->display();
    }

}