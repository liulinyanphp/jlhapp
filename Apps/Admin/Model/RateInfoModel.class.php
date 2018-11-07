<?php
namespace Admin\Model;
use Think\Model;
class RateInfoModel extends Model
{
	//定义主表名称
    protected $tableName = 'zb_rateinfo';

	protected $_validate = array(
		array('rate_value','require','汇率值不能为空!',1),
		//array('platform_basebi_type','','同一种平台同一种币同一种重提方式不得重复!',1,unique,2)
	);

	public function getlist($rate_type=1)
	{
		$where['rate_type'] = (int)$rate_type;
		return $this->where($where)->select();
	}
	public function getmaxid(){
		$field = 'max(id)';
		return $this->getField($field);
	}

	//获取日志列表
	public function getloglist($field='*',$where=array(),$pageNow='1',$limitRows='10')
	{
		$RatelogModel = M('ZbRatelogInfo');
		$data = $RatelogModel->field($field)->alias('a')->join("$this->tableName as b on a.rateid=b.id")
		                     ->where($where)->page($pageNow .','. $limitRows)->order('a.id desc')->select();
		return $data;
	}
	public function getlogcount($where=array())
	{
		$RatelogModel = M('ZbRatelogInfo');
		$count = $RatelogModel->alias('a')->join("$this->tableName as b on a.rateid=b.id")->where($where)->count();
		return $count;
	}
        
}
?>