<?php
namespace Admin\Model;
use Think\Model;
class BankCardInfoModel extends Model
{
	//定义主表名称
    protected $tableName = 't_bank_card_info';

	protected $_validate = array(
		array('bank_card_no','require','银行卡号不能为空!',1),
		array('bank_card_holder','require','银行卡持卡人不能为空!',1),
		array('bank_card_no','','银行卡号不得重复!',1,unique),
		array('bank_name','require','银行名称不能为空!',1),
		array('branch_bank','require','开户支行不能为空!',1),
		array('bank_addr','require','开户地不能为空！',1),
        array('user_id','require','用户编号不能为空！',1)
	);
	public function getlist()
	{
		return $this->select();
	}
	
	//获取日志列表
	public function getloglist($where=array(),$pageNow='1',$limitRows='10')
	{
		$RatelogModel = M('TCardlogInfo');
		$data = $RatelogModel->alias('a')->join("$this->tableName as b on a.carid=b.id")
		        ->where($where)->page($pageNow .','. $limitRows)->order('a.lgid desc')->select();
		return $data;
	}
	public function getlogcount($where=array())
	{
		$RatelogModel = M('TCardlogInfo');
		$count = $RatelogModel->alias('a')->join("$this->tableName as b on a.carid=b.id")->where($where)->count();
		return $count;
	}
        
}
?>