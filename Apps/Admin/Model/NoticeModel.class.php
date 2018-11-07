<?php
namespace Admin\Model;
use Think\Model;
class NoticeModel extends Model
{
	//定义主表名称
    protected $tableName = 't_notice';

	protected $_validate = array(
		array('title','require','公告不能为空!',1),
        array('content','require','公告内容!',1)
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