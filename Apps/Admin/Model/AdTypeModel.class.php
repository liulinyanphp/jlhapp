<?php
namespace Admin\Model;
use Think\Model;
class AdTypeModel extends Model
{
	//定义主表名称
    protected $tableName = 't_ad_type';

	protected $_validate = array(
		array('name','require','广告标题不能为空!',1),
		array('name','','广告标题不能重复!',1,unique)
	);

	public function getlist($where=array(),$pageNow='1',$limitRows='10')
	{
		return $this->where($where)->page($pageNow .','. $limitRows)->select();
	}
	
	public function getcount($w=array())
    {
        if (empty($w)) {
            return $this->count();
        } else {
            return $this->where($w)->count();
        }
    }

    public function getTypeList($where=array())
    {
        return $this->field('id,rand_code as randCode,name')->where($where)->select();
    }

}
?>