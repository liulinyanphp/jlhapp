<?php
namespace Admin\Model;
use Think\Model;
class ChannelModel extends Model
{
	//定义主表名称
    protected $tableName = 't_public_channel';

	protected $_validate = array(
		array('name','require','渠道名称不能为空!',1),
		array('logo_img_url','require','渠道logo不能为空!',1)
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