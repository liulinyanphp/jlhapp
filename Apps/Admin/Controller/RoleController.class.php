<?php
/**
 * addby : lly
 * date : 2018-03-21 22:23
 * 角色控制器
**/
namespace Admin\Controller;

use Think\Controller;

class RoleController extends Controller {
    public static $amazeui_basepath = '/Public/admin';
    private static $_username = '刘林燕';

    function __construct()
    {
        parent::__construct();
        $this->assign('username',self::$_username);
        $this->assign('base_path',self::$amazeui_basepath);
    }

    public function listAction()
    {
        $role = D('Role');
        //查询总记录数
        $count = $role->count();
        //实例化分也类 传入总记录数和每页显示的记录数(2)
        $page = new \Think\Adminpage($count,3);
        $show = $page->show(); //分页显示输出
        //$rolelist = $role->limit($page->firstRow.','.$page->listRows)->select();
        $rolelist = $role->field('a.*,group_concat(b.pri_name) pri_name')->alias('a')->join('left join privilege b on find_in_set(b.id,a.pri_id_list)')->group('a.id')->
        limit($page->firstRow.','.$page->listRows)->select();
        //$rolelist = $pri->rolelist();
        $this->assign('rolelist',$rolelist);
        $this->assign('page',$show);
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