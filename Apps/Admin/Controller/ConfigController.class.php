<?php
/*
 * 运营系统相关配置管理
*/
namespace Admin\Controller;
use Think\Controller;
//引入公用的结果处理类
use \Org\Util\Result;
//引入公用的异常抛出类
use \Org\Util\Exception;

class  ConfigController extends AdminBaseController {

    //删除操作变量
	private $_open_status = array('open','close');
	//去掉list,add,edit之后使用config控制器的方法
    private $_controllerList = array(
        'keywords','crowdunit','rating','token','pushcfg','investment','channel','competitor','analysis','help'
    );
    //各个方法对应的提示信息
    private $_showMsg = array(
        'keywords'=>'关键词',
        'crowdunit'=>'众筹单位',
        'rating'=>'评级级别',
        'distribute'=>'token分配',
        'pushcfg'=>'配置消息推送',
        'investment'=>'投资机构',
        'channel'=>'众筹渠道',
        'competitor'=>'竞品',
        'analysis'=>'行业分析',
        'help'=>'新手帮助'
    );
    //mapping配置表的类型
    private $_mappingType = array(
        'pushcfg'=>'SYS'
    );


    private $_keywordsOrderNums = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15);

	public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        $oktag = 0;
        if(strpos(strtolower($name),'list')!==false)
        {
            //list的索引
            $list_index = strpos($name,'list');
            $modelName = substr($name,0,$list_index);
            if(in_array($modelName,$this->_controllerList)){
                $oktag = 1;
                $modelName = ucfirst($modelName);
                $this->_funDoList($modelName);
            }
        }else if(strpos(strtolower($name),'add')!==false){
            //add的索引
            $add_index = strpos($name,'add');
            $modelName = substr($name,0,$add_index);
            if(in_array($modelName,$this->_controllerList)){
                $oktag = 1;
                $modelName = ucfirst($modelName);
                $this->_funDoAdd($modelName);
            }
        }else if(strpos(strtolower($name),'edit')!==false){
            //add的索引
            $edit_index = strpos($name,'edit');
            $modelName = substr($name,0,$edit_index);
            if(in_array($modelName,$this->_controllerList)){
                $oktag = 1;
                $modelName = ucfirst($modelName);
                $this->_funDoEdit($modelName);
            }
        }
        if($oktag==0){
            //404吧
            echo 'hi boy';
        }
    }

    /**
     * @param $modelName
     * used 公用的列表方法，之后如果多个list的话,可以使用该方法配合call一起使用
     *
     */
    private function _funDoList($modelName)
    {
        $pageNow = I('p',1);
        $pagesize = 20;
        $investmentInsM = D('Config');
        $w['id'] = array('gt',0);
        $data = $investmentInsM->getlist($w,$pageNow,$pagesize);
        $count = $investmentInsM->getcount($w);
        $Page = new  \Think\AdminPage ( $count, $pagesize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('listData',$data);
        $tplName = strtolower(ACTION_NAME);
        $this->assign('isPageTool',( $count > $pagesize ? 1 : 0));
        $this->display("$tplName");
    }

    /**
     * addby : lly
     * date : 2018-10-18
     * used : 公用的添加方法,之后如果多个add的话,可以使用该方法配合call一起使用
     */
    public function _funDoAdd($modelName)
    {
        if(IS_POST) {
            //返回的list
            $returnList = $modelName.'List';
            //前端显示的msg
            $msg_key = strtolower($modelName);
            $showName = $this->_showMsg[$msg_key];

            $result = array('status'=>0,'message'=>$showName.'添加成功','data'=>'');
            $projectM = D('Config');
            if($projectM->create())
            {
                //随机码
                $projectM->rand_code = strtoupper($modelName).'_'.guid().time();
                //追加mapping表中的类型
                if(in_array($msg_key,array_keys($this->_mappingType))){
                    $projectM->type = $this->_mappingType[$msg_key];
                }
                if(isset($_POST['content'])){
                    $projectM->content = $_POST['content'];
                }
                $projectM->created_by = session("username");
                $projectM->last_modified_by = session("username");
                try{
                    $res = $projectM->add();
                    if($res){
                        $result['data'] = U($returnList);
                    }else{
                        $result['status'] = '-1';
                        $result['message'] = '添加'.$showName.'入库操作失败';
                    }
                }catch(\Exception $e) {
                    $result['status'] = $e->getCode();
                    $result['message'] = '添加'.$showName.'失败'.$e->getMessage();
                }
            } else{
                $result['status'] = '-1';
                $result['message'] = ' 参数出错: '.$projectM->getError();
            }
            $this->ajaxReturn($result);
            return '';
        }
        $this->assign('currency_type',C('CURRENCT_TYPE'));
        $this->assign('orderNums',$this->_keywordsOrderNums);
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-10-18 15:05
     * used : 公用的编辑方法,之后如果多个edit的话,可以使用该方法配合call一起使用
     **/
    public function _funDoEdit($modelName)
    {
        $randCode = I('randCode');
        $projectM = D('Config');
        $w_search['rand_code'] = array('eq',$randCode);
        $data = $projectM->where($w_search)->find();
        //前端显示的msg
        $msg_key = strtolower($modelName);
        $showName = $this->_showMsg[$msg_key];
        $result = array('status'=>0,'message'=>$showName.'信息变更成功','data'=>'');
        if(IS_POST && !empty($data)){
            if(I('act') && in_array(I('act'),$this->_open_status)){
                $isDeleted = ( I('act') == 'close' ? 1 : 0 );
                $w['id'] = array('eq',$data['id']);
                $projectM->where($w)->setField('is_deleted',$isDeleted);
                $this->ajaxReturn($result);
                return '';
            }
            //返回的list
            $returnList = $modelName.'List';
            if($projectM->create())
            {
                try{
                    if(isset($_POST['content'])){
                        $projectM->content = $_POST['content'];
                    }
                    if(!$projectM->where($w_search)->save())
                    {
                        $result['status'] = '-1';
                        $result['message'] = '您未做任何信息变更';
                    }else{
                        $result['data']  = U($returnList);
                    }
                }catch(\Exception $e) {
                    $result['status'] = $e->getCode();
                    $result['message'] = '更新'.$showName.'信息失败!'.$e->getMessage();
                }
            }else{
                $result['status'] = '-1';
                $result['message'] = '参数出错：'.$projectM->getError();
            }
            $this->ajaxReturn($result);
            return '';
        }
        $this->assign('orderNums',$this->_keywordsOrderNums);
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
}