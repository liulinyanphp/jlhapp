<?php
namespace Admin\Model;
use Think\Model;
class InvestmentModel extends Model
{
	//定义主表名称
    protected $tableName = 't_investment_research_info';

	protected $_validate = array(
		array('title','require','投研标题不能为空!',1),
		array('img_url','require','投研缩略图不能为空!',1),
        array('content','require','投研内容不能为空!',1),
        array('author','require','投研作者不能为空!',1)
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