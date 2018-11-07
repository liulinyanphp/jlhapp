<?php
namespace Admin\Model;
use Think\Model;
class AccountModel extends Model
{
	protected $_validate = array(
		array('username','require','用户名不能为空!',1),
		//添加的时候验证
		//array('username','','用户名不能重复!',1,unique,1),
		//编辑的时候验证
		//array('username','','用户名不能重复!',1,unique,2),
		array('password','require','用户密码不能为空!',1),
		//array('verify','verify','验证码错误',1,'callback',4)
	);

	public function login()
	{
		$password = $this->password;
		$w['username'] = array('eq',$this->username);
		$info = $this->where($w)->find();
		if($info){
			if($info['password'] == md5($password)){
				

				//根据登陆用户获取该用户的角色(只适合单一角色的,多角色后面再进行修改)
				$ro_w['a.id'] = $info['id'];
				$rolename = $this->alias('a')->join('role b on a.roleid=b.id')->where($ro_w)->getField('b.rolename');
				session('id',$info['id']);
				session('username',$info['username']);
				session('rolename',$rolename);
				return true; 
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	public function verify($code){
		$verify = new \Think\verify();
		return $verify->check($code,'');
	}

	public function _before_delete($options)
	{
		//当读删除时候id的值，是一个字符串，是一个单独的id
		//options['where']['id']. int(5)
		if(is_array($options['where']['id']))
		{
			$arr = explode(',',$options['where']['id'][1]);
			$soncates = array_unique($arr);
			$childrenids = implode(',',$soncates);
		}else{
			$childrenids = $options['where']['id'];
			$childrenids = implode(',',$childrenids);
		}
		if($childrenids)
		{
			$this->execute("delete from role where id in($childrenids)");
		}
	}














}
?>