<?php
namespace Admin\Model;
use Think\Model;
class ProjectOrderModel extends Model
{
	//定义主表名称
    protected $tableName = 't_project_order';

	protected $_validate = array(
		array('order_start_time','require','预约开始时间不能为空!',1),
        array('order_end_time','require','预约结束时间不能为空!',1),
        array('order_sale_detail','require','预约细则不能为空,给后面带来不必要的纠纷!',1),
        array('order_fi_unit','require','预约单位不能为空!',1),
        array('order_fi_amount','require','预约总额不能为空!',1),
        array('order_consensus_cost','require','预约共识成本不能为空!',1)
	);

	public function getlist($where=array(),$pageNow='1',$limitRows='10')
	{
		return $this->where($where)->order('created_date desc')->page($pageNow .','. $limitRows)->select();
	}
	
	public function getcount($w=array())
    {
        if (empty($w)) {
            return $this->count();
        } else {
            return $this->where($w)->count();
        }
    }
}
?>