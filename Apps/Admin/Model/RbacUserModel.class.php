<?php
namespace Admin\Model;
use Think\Model;
class RbacUserModel extends Model
{
    //定义主表名称
    protected $tableName = 't_rbac_user';
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
		$info = $this->where($w)->select();
		if($info){
			$info = $info[0];
			if($info['password'] == md5($password)){
				return $info; 
			}else{
				return '密码错误';
			}
		}else{
			return '用户名不存在';
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