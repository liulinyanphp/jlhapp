<?php
/**
 * addby : jamesliu
 * date : 2018-03-23
 * 后台父类,所有后台全部继承该类
**/
namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;


class AdminBaseController extends Controller {

    public static $amazeui_basepath = '/Public/admin';

    public function _initialize()
    {
        if(!session('uid')){
            $this->error('请先登陆系统',U('login/index'));
        }
        //为了安全起见每一步都根据sessionid去获取一遍,以免伪造
        $userM = D('RbacUser');
        $uinfo = $userM->find(session('uid'));
        if($uinfo['logintime'] == session('ckintime'))
        {
            $notAuth = in_array(CONTROLLER_NAME,explode(',',C('NOT_AUTH_MODULE'))) || in_array(ACTION_NAME,C('NOT_AUTH_ACTION'));

            if(C('USER_AUTH_TYPE') && !$notAuth) {
                Rbac::AccessDecision() || $this->error('没有访问权限');
            }
            $this->assign('username',session('username'));
            $this->assign('base_path',self::$amazeui_basepath);
        }else{
            $this->error('请先登陆系统',U('login/index'));
        }
        $menu = $this->_leftmenu();
        $this->assign('menu',$menu);



        //获取title
        $modelName = CONTROLLER_NAME;
        $navs = C("ADMIN_LEFT_MENU.$modelName");
        $navList = $navs['list'];
        $urlList = array_column($navs['list'],'url');
        $urlSort = strtolower(CONTROLLER_NAME.'/'.ACTION_NAME);
        $title_index = array_search($urlSort,$urlList);
        $this->assign('title',$navs['name'].'-'.$navList[$title_index]['name']);

    }

    //左侧公用导航
    /**
     * addby : lly
     * date : 2013-03-24 23:19
    **/
    private function _leftmenu()
    {
        $aclList = C('ADMIN_LEFT_MENU');
        $accessList = Rbac::getAccessList(session('uid'));
        //if(!$_SESSION[C('ADMIN_AUTH_KEY')])
        $_menu = array();
        foreach ($aclList as $key=>$val)
        { 
            $val['open'] = '';
            $_key = strtoupper($key);
            //左侧导航打开
            if($_key == strtoupper(CONTROLLER_NAME)){
                $val['open'] = 'on';
            }
            if($_SESSION[C('ADMIN_AUTH_KEY')]){
               $_menu[$key] = $val; 
            }elseif(array_key_exists($_key,$accessList['ADMIN']))
            { 
                foreach ($val['list'] as $k=>$v)
                {
                    if(!array_key_exists(strtoupper($v['act']),$accessList['ADMIN'][$_key])) { 
                        unset($val['list'][$k]);
                    }
                }
                $_menu[$key] = $val;
            }   
        }
        return $_menu;
    }




}