<?php
namespace Admin\Model;
use Think\Model;
class ProjectModel extends Model
{
	//定义主表名称
    protected $tableName = 'data_project_config';

	protected $_validate = array(
		//array('rolename','require','角色名称不能为空!',1),
		//array('rolename','','角色名称不得重复!',1,unique)
	);

	//获取总成本
	public function getchenben()
	{
		$sql = "select pro_money_basebi_name as basebiname,sum(pro_money) as sum from data_project_config group by pro_money_basebi_name";
		return $this->query($sql);
	}
	//获取现在的总成本
	public function getnowprice()
	{
		$sql = "select pro_money_basebi_name as basebiname,round(sum(pro_num*pro_ico_now_price),5) as sum from data_project_config group by pro_money_basebi_name;";
		return $this->query($sql);
	}
	//获取现在的所有收益
	public function getallpercent()
	{
		$sql = "select count(*) as count,sum(pro_money_percent) as percent from data_project_config where pro_alias!=1";
		return $this->query($sql);
	}
	
	/**
     * addby : lly
     * data : 2018-03-25
     * used : 获取项目的id和项目名称
    **/
    public function get_pro_list($field='*')
    {
    	$where['pro_is_delete'] = array('eq',0);
    	$data = $this->Field($field)->where($where)->select();
    	return $data;
    }
}
?>