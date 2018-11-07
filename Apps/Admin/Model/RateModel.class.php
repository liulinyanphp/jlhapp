<?php
namespace Admin\Model;
use Think\Model;
class RateModel extends Model
{
	//定义主表名称
    protected $tableName = 't_rate';

	protected $_validate = array(
		array('deposit_price','require','充值汇率值不能为空!',1),
		array('withdraw_price','require','提现汇率值不能为空!',1),
		array('plat_code','require','平台code不能为空!',1),
		array('currency_code','require','币种编号不能为空!',1)
	);

	//获取汇率信息列表
	public function getlist($field='*',$where=array(),$pageNow='1',$limitRows='10')
	{
		if(empty($where)){
			$where['id'] = array('gt',0);
		}
		$data = $this->field($field)->where($where)->page($pageNow .','. $limitRows)->select();
		return $data;
	}
	//获取搜索条件的记录条数
	public function getcount($where = array()){
		if(empty($where)){
			$where['id'] = array('gt',0);
			return $this->where($where)->count();
		}
	}
	//新建记录的时候同步到日志信息表里面
	public function insert_into_log($id)
	{
		if( (int)$id > 0 )
		{
			$w['id'] = array('eq',$id);
		}
		$info = $this->where($w)->find();
		if(!empty($info))
		{
			unset($info['id']);
			M('TRateLog')->data($info)->add();
		}
	}

	//获取日志列表
	public function get_log_list($where=array(),$pageNow='1',$limitRows='10')
	{
		if(empty($where)){
			$where['id'] = array('eq',1);
		}
		return  M('TRateLog')->where($where)->page($pageNow .','. $limitRows)->order('id desc')->select();
	}
	public function get_log_count($where=array())
	{
		return M('TRateLog')->where($where)->count();
	}
}
?>