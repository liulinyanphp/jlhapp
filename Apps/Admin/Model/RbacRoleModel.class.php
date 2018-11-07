<?php
namespace Admin\Model;
use Think\Model;
class RbacRoleModel extends Model
{
    //定义主表名称
    protected $tableName = 't_rbac_role';

	protected $_validate = array(
		array('rolename','require','角色名称不能为空!',1),
		array('rolename','','角色名称不得重复!',1,unique)
	);

	public function rolelist()
	{
		return $this->select();
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