<?php
/**
 * addby : lly
 * date : 2018-03-21 22:23
 * 用户登陆控制器
**/
namespace Admin\Controller;

use Think\Controller;
//
class Rbaccontroller extends AdminBaseController {
    /**
     * addby : lly
     * date : 2018-03-25
     * used : 用户列表
    **/
    public function user_listAction() {
        $this->user =  D('TRbacUserRelation')->field('password', true)->relation(true)->select();
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-03-25
     * used : 渲染添加页面、用户添加处理
    **/
    function add_userAction()
    {
        if(IS_POST)
        {
            $user = array(
                'username'=>I('post.username', ''),
                'password'=>I('post.password','','md5'),
                'logintime'=>time(),
                'loginip' =>get_client_ip()
            );
            $uid = M('TRbacUser')->add($user);
            $rold = array();
            if($uid) {
                foreach($_POST['role_id'] as $v) {
                    $role[] = array(
                        'role_id'=>$v,
                        'user_id'=>$uid
                    );
                }
                M('TRbacRoleUser')->addAll($role);
                $this->redirect(U('user_list'));
            } else {
                $this->error('添加失败');
            }
            return '';
        }
        $this->role = M('Role')->select();
        $this->display();
    }


    /**
     * addby : lly
     * date : 2018-03-25
     * used : 角色列表
    **/
    public function role_listAction()
    {        
        $role = D('RbacRole')->select();
        $this->assign('rolelist',$role);
        $this->display();
    }

    /**
     * addby : lly
     * date : 2018-03-25
     * used : 渲染角色添加、角色添加处理
    **/
    public function add_roleAction(){
        if(IS_POST)
        {
            if(M('TRbacRole')->add($_POST)){
                $this->redirect(U('role_list'));
            }else{
                $this->error($this->error());
            }
            return '';
        }
        $this->display();
    }


    /**
     * addby : lly
     * date : 2018-03-25
     * used : 渲染权限列表
    **/
    public function node_listAction() {
        $field = array('id', 'name', 'title', 'pid');
        $node = M('TRbacNode')->field($field)->order('sort asc')->select();
        $nownode = node_regroup($node);
        $this->assign('node',$nownode);
        $this->display();
    }
    
    /**
     * addby : lly
     * date : 2018-03-25
     * used : 渲染添加节点(模块)、处理权限添加数据
    **/
    public function add_nodeAction() {
        $this->pid = I('get.pid', 0, 'int');//如果没有传递的pid参数，则默认为0
        $this->level = I('get.level', 1, 'int');//如果没有传递的level参数，则level是1，代表顶级（模块）
        switch($this->level) {
            case 1:
                $this->type = '模块';
                break;
            case 2:
                $this->type = '控制器';
                break;
            case 3:
                $this->type = '方法';
                break;
        }
        if(IS_POST){
            if(M('TRbacNode')->add($_POST)) {
                $this->redirect(U('node_list'));
            } else {
                $this->error('添加失败');
            }
            return '';
        }
        $this->display();
    }

    //配置权限
    public function accessAction() {
        $rid = I('get.rid', 0, 'int');//角色id
        $field = array('id', 'name', 'title', 'pid');
        $node = M('TRbacNode')->field($field)->order('sort asc')->select();
        $access = M('TRbacAccess')->where('role_id = '.$rid)->getField('node_id', true);//已经拥有的权限
        $node = node_regroup($node, 0, $access); //递归节点
        $this->assign('rid',$rid);
        $this->assign('node',$node);
        $this->display();
    }
    
    //权限配置的表单提交处理
    public function access_handleAction() {
        $rid = I('rid', 0, 'int');
        $db = M('TRbacAccess');
        $db->where('role_id = '.$rid)->delete();//删除原有权限
        $data = array();
        if(!empty($_POST['access'])) {
            foreach($_POST['access'] as $v) {
                $tmp = explode('_', $v);
                $data[] = array(
                    'role_id'=>$rid,
                    'node_id'=>$tmp[0],
                    'level'=>$tmp[1]
                );
            }
            if($db->addAll($data)) { //写入新权限
                $this->success('分配权限成功', U('role_list','',''));
            } else {
                $this->error('分配权限失败');
            }
        }
    }
    
}