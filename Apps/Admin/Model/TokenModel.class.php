<?php
namespace Admin\Model;
use Think\Model;
class TokenModel extends Model
{
	//定义主表名称
    protected $tableName = 't_token_info';

	protected $_validate = array(
		array('code','require','币种编码不能为空!',1),
		array('code','','币种编码不得重复!',1,unique),
		array('name','require','币种名称不能为空!',1),
		array('name','','币种名称不得重复!',1,unique),
		array('property_id','require','币种标示id不能为空!',1),
		array('property_id','number','币种标示id只能为数字',1)

	);

	public function getlist($where=array(),$pageNow='1',$limitRows='10')
	{
		return $this->where($where)->page($pageNow .','. $limitRows)->select();
	}
	
	public function getcount($w=array())
	{
		if(empty($w)){
			return $this->count();
		}else{
			return $this->where($w)->count();
		}
	}

	public function get_token_code()
	{
		$w['status'] = array('eq',0);
		return $this->where($w)->getField('code',true);
	}
	

}
?>