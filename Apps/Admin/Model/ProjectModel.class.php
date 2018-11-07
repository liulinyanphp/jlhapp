<?php
namespace Admin\Model;
use Think\Model;
class ProjectModel extends Model
{
	//定义主表名称
    protected $tableName = 't_project';

	protected $_validate = array(
		array('name','require','项目名称不能为空!',1),
		array('introduction','require','资讯简介不能为空!',1,'function',1),
        array('content','require','项目详情不能为空!',1)
	);

	public function getlist($where=array(),$pageNow='1',$limitRows='10')
	{
        return $this->field('a.*,b.count')->alias('a')->join('t_project_follow_stat as b on a.rand_code=b.project_rand_code',left)->
        where($where)->order('a.created_date desc')->page($pageNow .','. $limitRows)->select();

	}
	
	public function getcount($w=array())
    {
        if (empty($w)) {
            return $this->alias('a')->count();
        } else {
            return $this->alias('a')->where($w)->count();
        }
    }
}
?>