<?php
namespace Admin\Model;
use Think\Model;
class AdvertisingModel extends Model
{
	//定义主表名称
    protected $tableName = 't_advertising_position';

	protected $_validate = array(
		array('ad_title','require','广告标题不能为空!',1),
		array('ad_title','','广告标题不能重复!',1,unique),
		array('img_url','require','广告图片不能为空!',1),
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
}
?>