<?php
namespace Admin\Model;
use Think\Model;
class UserAddressBalanceModel extends Model
{
	//定义主表名称
    protected $tableName = 't_user_address_balance';

	protected $_validate = array(
	);

    /**
     * 查询所有的用户余额
     * @return mixed
     */
	public function getList()
	{
		return $this->select();
	}

    /**
     * 分页查询
     * @param string $field
     * @param array $where
     * @param string $pageNow
     * @param string $limitRows
     * @return mixed
     */
    public function getbalancepagerlist($field='*',$where=array(),$pageNow='1',$limitRows='10')
    {

        $data = $this->field($field)->where($where)->page($pageNow .','. $limitRows)->order('created_date desc')->select();
        echo $this->_sql();

        return $data;
    }


    public function getcount($w=array())
    {
        if(empty($w)){
            return $this->count();
        }else{
            return $this->where($w)->count();
        }
    }
        
}
?>