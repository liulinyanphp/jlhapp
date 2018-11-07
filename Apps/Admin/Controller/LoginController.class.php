<?php
/**
 * addby : lly
 * date : 2018-03-21 22:23
 * 用户登陆控制器
**/
namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;

class LoginController extends Controller {
    public static $amazeui_basepath = '/Public/admin';
    private static $_username = '刘林燕';

    function __construct()
    {
        parent::__construct();
        $this->assign('username',self::$_username);
        $this->assign('base_path',self::$amazeui_basepath);
    }

    /**
     * addby : lly
     * date : 2018-03-24
     * used : 登陆首页/处理用户登陆函数
    **/
    public function indexAction()
    {
        session(null);
        layout(false);
        $account = D('RbacUser');
        if (IS_POST) {
            if($account->create())
            {
                $result = $account->login();
                if(!is_array($result)){
                    $this->error("$result"); 
                }else{
                    session("username",$result['username']);  
                    session("uid",$result['id']);
                    session("ckintime",time());
                    if($result['username']==C('RBAC_SUPERADMIN')){  
                        session(C('ADMIN_AUTH_KEY'),true);  
                    }
                    //将权限写入session
                    Rbac::AccessDecision();
                    $newM = M('TRbacUser');
                    $data = array("id"=> $result["id"], "logintime"=> time(), "loginip" => get_client_ip());
                    $newM->data($data)->save();
                    $this->redirect(U('/admin/index/index'));
                    //$this->success('欢迎登陆',U('/admin/index/index'));
                }
            }else{
                $this->error($account->getError()); 
            }
            return '';
        }
        $this->display();
    }    
    public function loginoutAction()
    {
        session(null);
        //$this->success('退出成功',U('/admin/login/index'));
        $this->redirect(U('/admin/login/index'));
    }

    public function verifyAction()
    {
        $Verify = new \Think\Verify();
        $Verify->length = 4;
        $Verify->entry();
    }


    public function listAction()
    {
        $admin = D('Admin');
        $where = 1;
        if($kw = I('kw')){
            $where .=' AND username like "%'.$kw.'%"';
        }
        //查询总记录数
        $count = $admin->where($where)->count();
        //实例化分也类 传入总记录数和每页显示的记录数(2)
        $page = new \Think\Adminpage($count,2);
        $show = $page->show(); //分页显示输出
        //$rolelist = $role->limit($page->firstRow.','.$page->listRows)->select();
        $adminlist = $admin->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        
        //获取角色
        $roleModel= D('Role');
        $roles = $roleModel->select();
        $this->assign('list',$adminlist);
        $this->assign('page',$show);
        $this->assign('roles',$roles);
        $this->display();
    }
    public function addAction()
    {
        $role = D('RbacRole');
        if(IS_POST){
            if($role->create()){
                $role->pri_id_list = implode(',',$role->pri_id_list);
                if($role->add()){
                    $this->success('添加角色成功!',U('list'));
                }else{
                    $this->error('添加角色失败!');
                }
            }else{
                $this->error($role->getError());
            }
            return ;
        }
        $pri  = D('Privilege');
        $pris = $pri->pritree();
        $this->assign('pris',$pris);
        $this->display();
    }
    public function editAction()
    {
        $role = D('RbacRole');
        if(IS_POST){
            if($role->create()){
                $role->pri_id_list = implode(',',$role->pri_id_list);
                if($role->save()){
                    $this->success('更新权限成功!',U('list'));
                }else{
                    $this->error('更新权限失败!');
                }
            }else{
                $this->error($role->getError());
            }
            return ;
        }
        $id = I('id');
        $roleres =  $role->find($id);

        $pri_id_list = array_filter(explode(',',$roleres['pri_id_list']));
        $pri = D('Privilege');
        $pris = $pri->pritree();
        $this->assign('roleres',$roleres);
        $this->assign('pri_id_list',$pri_id_list);
        $this->assign('pris',$pris);
        $this->display();
    }

    //单个删除角色
    public function delAction()
    {
        $role = D('RbacRole');
        $id = I('id');
        if($id ==1)
        {
            $this->error('超级管理员角色无法删除!');
        }
        if($role->delete($id)){
            $this->success('删除角色成功!',U('list'));
        }else{
            $this->error('删除角色失败!');
        }
    }
    //批量删除角色
    public function bdel()
    {
        $ids = I('ids');
        if($ids)
        {
            //超级管理员不能删除
            $key = array_search(1,$ids);
            if($key!==false)
            {
               unset($ids[$key]); 
            }   
        }
        $ids = implode(',',$ids); //1,2,3,4
        $role = D('RbacRole');
        if($ids)
        {
            if($role->delete($ids))
            {
                 $this->success('批量删除角色成功!',U('list'));
            }else{
                 $this->success('批量删除角色失败!',U('list'));
            }
        }else{
            $this->error('未选中任何内容');
        }
    }
}