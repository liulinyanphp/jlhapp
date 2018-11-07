<?php
namespace Admin\Model;
use Think\Model;
class RecommendModel extends Model
{
	//定义主表名称
    protected $tableName = 't_recommend_info';

	protected $_validate = array(
		array('rand_code','require','项目编码不能为空!',1)
	);

	public function getlist($where=array(),$pageNow='1',$limitRows='10')
	{
		return $this->alias('a')->join('t_project as b on a.rand_code=b.rand_code')->field('a.*,b.name,b.logo_img_url')->where($where)->order('a.created_date desc')->page($pageNow .','. $limitRows)->select();
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