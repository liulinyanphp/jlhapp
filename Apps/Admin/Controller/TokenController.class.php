<?php
namespace Admin\Controller;
use Think\Controller;

class TokenController extends AdminBaseController {

    //定义状态
    private $_open_status = array('open','close');

    /**
     * addby : lly
     * date : 2018-09-11 14:36
     * used : 币种列表
    **/
    public function listAction()
    {  
        $pageNow = I('p',1);
        $pagesize = 10;
        $tokenM = D('Token');
        $w['id'] = array('gt',0);
        $data = $tokenM->getlist($w,$pageNow,$pagesize);
        $count = $tokenM->getcount($w);
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('pager',$show);
        $this->assign('tokenlist',$data);
        $this->display();
    }
    
    /**
     * addby : lly
     * date : 2018-04-23 11:30
     * used : 平台添加、平台添加处理
    **/
    public function addAction(){
        $result = array('status'=>0,'message'=>'添加币种成功','data'=>'');
        if(IS_POST)
        {
            $tokenM = D('Token');
            if($tokenM->create())
            {
                $tokenM->created_by = session("username");
                $tokenM->last_modified_by = session("username");
                //查看同一个平台同一个币种是否存在了，先不做校验 后面追加
                try{
                    $tokenM->add();
                    $result['data'] = U('list');
                }catch(\Exception $e) {
                    $result['status'] = $e->getCode();
                    $result['message'] = '添加币种失败'.$e->getMessage();
                }
            } else{
                $result['status'] = '-1';
                $result['message'] = $tokenM->getError();
            }
            $this->ajaxReturn($result);
            return '';
        }
        $this->assign('currency_type',C('CURRENCT_TYPE'));
        $this->display();
    }


    /**
     * addby : lly
     * date : 2018-04-15 01:25
     * used : 平台状态变更处理
     * desc : cg_plat全意为change_platform_statu
    **/
    public function cg_statusAction(){
        $result = array('status'=>0,'message'=>'状态变更成功','data'=>array());
        $id = I('id',0,'intval');
        $act = I('act');
        $tokenM = D('Token');
        $data = $tokenM->find($id);
        if($id>0 && $act && in_array($act,$this->_open_status) && !empty($data))
        {
            if($data['created_by']!=session("username")){
                $result['status'] = '-1';
                $result['message'] = '添加的基础币元只能由录入人员变更,因为他要对这个负责!';
                $this->ajaxReturn($result);
                return '';
            }
            $data['is_deleted'] = ( $act == 'open' ? 1 : 0 ) ;
            try{
                $tokenM->data($data)->save();
                $result['data'] = U('list');
            }catch(\Exception $e) {
                $result['status'] = $e->getCode();
                $result['message'] = '币种状态变更失败'.$e->getMessage();
            }
        }else{
            $result['status'] = '-1';
            $result['message'] = '参数错误';
        }
        $this->ajaxReturn($result);
    }
}