<?php
namespace Admin\Controller;
use Think\Controller;

class SysconfigController extends AdminBaseController {

	/**
	 * addby : lly
	 * date : 2018-03-20
	 * used : 设置提醒时间点，每日提醒次数，以及输出报表
    **/
	public function indexAction()
	{
    	/*一天次数*/
    	$day_num = array(2=>'一天两次',1=>'一天一次',3=>'一天三次',4=>'一天四次');
    	$day_time = array(0=>'0点',6=>'6点',12=>'12点',18=>'18点');
    	//获取所有饿项目id和名称
    	$proM = D('Project');
    	$pro_data = $proM ->get_pro_list('pro_id,pro_name');
    	$pro_data_all = array_combine(array_column($pro_data,'pro_id'),array_column($pro_data,'pro_name'));
    	array_unshift($pro_data_all,'通用');
    	//
    	$wornModel = M('DataWorningConfig');
    	$data = $wornModel->find();
    	$post = I('post.');
    	if(!empty($post)){
    		//如果正常的话 一定要有邮箱和币种
	    	$status = I('config_status');
	    	$proid = I('pro_ids');
	    	$email = I('email','');
	    	if($status>0)
	    	{
	    		if(empty($proid) || empty($email))
	    		{
	    			$this->error('启用状态下提醒项目和提醒邮箱不能为空','/admin/index/projec_work_seting',3);
					exit();
	    		}
	    	}
	    	$tmpdata = $this->_combine_key($post,'worn_',1);
    		if(!empty($data)){
    			//编辑
    			$wornModel->data($tmpdata)->save();
    			$this->error('更新成功','/admin/index/projec_work_seting',3);
				exit();
    		}else{
    			//新增
    			if(isset($tmpdata['worn_id'])){
    				array_shift($tmpdata);
    			}
    			$tmpdata['worn_add_time'] = date('Y-m-d H:i:s');
    			$tmpdata['worn_add_user'] = 'lly';
    			$wornModel->data($tmpdata)->add();
    			$this->error('录入成功','/admin/index/projec_work_seting',3);
    			exit();
    		}
    	}
    	$this->assign('configdata',$data);
    	$this->assign('daynum',$day_num);
    	$this->assign('daytime',$day_time);
    	$this->assign('daytime_config',json_decode($data['worn_day_time']));
    	$this->assign('proids_config',json_decode($data['worn_pro_ids']));
    	$this->assign('status_ok',$data['worn_config_status'] ? 'am-active' :'');
    	$this->assign('status_unok',$data['worn_config_status'] ? '' :'am-active');
    	$this->assign('prodata',$pro_data_all);
    	$this->display();
    }

    /**
     * addby : lly
     * date : 2018-03-25
     * used : 系统获取价格日志查看
    **/
    public function sys_pricelogAction()
    {
    	$pageNow = I('p',1);
    	$limitRows = 10;
    	$where['price_id'] = array('gt',0);
    	$configModel = M('DataProjectPricelist');
    	$tmpdata = $configModel->where($where)->page($pageNow .','. $limitRows)->order('price_id desc')->select();
    	$data = array();
    	foreach($tmpdata as $k=>$v)
    	{	
    		$v['now_bi_base'] = bcdiv($v['pro_bi_price'],$v['pro_basebi_price'],8).$v['pro_basebi_name'];
    		$data[$k] = $v;
    		unset($tmpdata[$k]);	
    	}
    	$count = $configModel->where($where)->count();
    	$Page = new  \Think\AdminPage ( $count, $limitRows, '' );
		$show = $Page->show (); // 分页显示输出
		$this->assign('pager',$show);
    	$this->assign('col_loglist',$data);
    	$this->display();
    }
}