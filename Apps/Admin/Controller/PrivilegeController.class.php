<?php
namespace Admin\Controller;

use Think\Controller;

class PrivilegeController extends Controller {
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
        $pri = D('Privilege');
        $pris = $pri->pritree();
        $this->assign('pris',$pris);
        $this->display();
    }
    public function addAction()
    {
        $pri = D('Privilege');
        if(IS_POST){
            if($pri->create()){
                if($pri->add()){
                    $this->success('添加权限成功!',U('list'));
                }else{
                    $this->error('添加权限失败!');
                }
            }else{
                $this->error($pri->getError());
            }
            return ;
        }
        $pris = $pri->pritree();
        $this->assign('pris',$pris);
        $this->display();
    }
    public function editAction()
    {
        $pri = D('Privilege');
        if(IS_POST){
            if($pri->create()){
                if($pri->save()){
                    $this->success('更新权限成功!',U('list'));
                }else{
                    $this->error('更新权限失败!');
                }
            }else{
                $this->error($pri->getError());
            }
            return ;
        }
        $id = I('id');
        $prires = $pri->find($id);
        $pris = $pri->pritree();
        $this->assign('prires',$prires);
        $this->assign('pris',$pris);
        $this->display();
    }

    public function del()
    {
        $pris = D('Privilege');
        $id = I('id');
        if($pri->delete($id)){
            $this->success('成功删除权限!',U('list'));
        }else{
            $this->error('删除权限失败!');
        }
    }
    //批量删除
    public function bdel()
    {
        $pris = D('Privilege');
        $ids = I('ids');
        $ids = implode(',',$ids); //1,2,3,4
        if($ids)
        {
            if($pris->delete($ids))
            {
                 $this->success('批量删除权限成功!',U('list'));
            }else{
                 $this->success('批量删除权限失败!',U('list'));
            }
        }else{
            $this->error('未选中任何内容');
        }
    }
}