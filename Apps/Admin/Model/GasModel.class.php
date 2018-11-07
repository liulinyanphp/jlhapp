<?php
namespace Admin\Model;
use Think\Model;
class GasModel extends Model
{
	//定义主表名称
    protected $tableName = 't_gas_config';

	protected $_validate = array(
		array('percent','require','手续费百分比不能为空!',1),
		array('state_value','require','手续费固定值不能为空!',1)
	);

	public function getlist($gas_type=1)
	{
		$where['gas_type'] = (int)$gas_type;
		return $this->where($where)->select();
	}
	public function getmaxid(){
		$field = 'max(id)';
		return $this->getField($field);
	}

	//获取日志列表
	public function getloglist($field='*',$where=array(),$pageNow='1',$limitRows='10')
	{
		$RatelogModel = M('TGaslogInfo');
		$data = $RatelogModel->field($field)->alias('a')->join("$this->tableName as b on a.gasid=b.id")
		                     ->where($where)->page($pageNow .','. $limitRows)->order('a.lgid desc')->select();
		return $data;
	}
	public function getlogcount($where=array())
	{
		$RatelogModel = M('TGaslogInfo');
		$count = $RatelogModel->alias('a')->join("$this->tableName as b on a.gasid=b.id")->where($where)->count();
		return $count;
	}
        
}
?>