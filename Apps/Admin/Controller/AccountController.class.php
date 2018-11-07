<?php
/**
 * addby : lly
 * date : 2018-03-21 22:23
 * 角色控制器
**/
namespace Admin\Controller;

use Think\Controller;

class AccountController extends Controller {
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
     * date : 2018-03-22
     * used : 登陆首页/处理用户登陆函数
    **/
    public function indexAction()
    {
        layout(false);
        if(IS_POST){
            if($account->create()){
                if($account->login()){
                    $this->success('登陆中....',U('index/index'));
                }else{
                    $this->error('用户名或密码错误!');
                }
            }else{
                    $this->error($account->getError());
            }
        }
        $this->display();
    }

    public function listAction()
    {
        $account = D('Account');
        $where = 1;
        if($kw = I('kw')){
            $where .=' AND a.username like "%'.$kw.'%"';
        }
        //查询总记录数
        $count = $account->where($where)->count();
        //实例化分也类 传入总记录数和每页显示的记录数(2)
        $page = new \Think\Adminpage($count,2);
        $show = $page->show(); //分页显示输出
        //$rolelist = $role->limit($page->firstRow.','.$page->listRows)->select();
        //$adminlist = $admin->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        $adminlist = $account->field('a.id,a.username,b.rolename')->alias('a')
                            ->join('left join role as b on a.roleid=b.id')
                           ->where($where)->limit($page->firstRow.','.$page->listRows)->select();     
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
        $role = D('Role');
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
        $role = D('Role');
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
        $role = D('Role');
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
        $role = D('Role');
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