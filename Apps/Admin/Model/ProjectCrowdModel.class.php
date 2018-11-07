<?php
namespace Admin\Model;
use Think\Model;
class ProjectCrowdModel extends Model
{
	//定义主表名称
    protected $tableName = 't_project_crowd';

	protected $_validate = array(
		array('crowd_start_time','require','众筹开始时间不能为空!',1),
        array('crowd_end_time','require','众筹结束时间不能为空!',1),
        array('crowd_sale_detail','require','众筹细则不能为空,给后面带来不必要的纠纷!',1),
        array('crowd_fi_unit','require','众筹单位不能为空!',1),
        array('crowd_fi_amount','require','众筹总额不能为空!',1),
        array('crowd_consensus_cost','require','众筹共识成本不能为空!',1)
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