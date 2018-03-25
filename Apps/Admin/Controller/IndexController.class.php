<?php
namespace Admin\Controller;

use Think\Controller;
use Common\Service\DataService;
use Common\Service\UtilService;
use Org\Util\Rbac;


class IndexController extends AdminBaseController {

	public $basebi_arr = array(
    		'ETH'=>'ethereum',
    		'BTC'=>'bitcoin',
    		'USDT'=>'tether'
    );
	public function indexAction(){
        $project = D('Project');
        $pro_list = $project->select();
        $chenben = $project->getchenben();
        $nowprice = $project->getnowprice();
        $percentinfo = $project->getallpercent();

        //总收益
        $this->assign('prolist',$pro_list);
        $this->assign('chenben',$chenben);
        $this->assign('nowprice',$nowprice);
        $this->assign('prv_percent',bcdiv($percentinfo[0]['percent'],$percentinfo[0]['count'],4));
        $this->assign('allpercent',$percentinfo[0]['percent']);
		$this->display();
	}

    /**
     * addby: lly
     * addtime: 2018-03-14
     * used: 从非小号网站获取信息
    **/
    public function get_data_from_feixiaohaoAction()
    {
    	$source_url = 'https://www.feixiaohao.com';
    	$curl_service = new UtilService();
    	$info = $curl_service->curl_get_http($source_url);
    	//file_put_contents('b.html',$info);
    	if(file_exists(ROOT.'/b.html')){
    		$str = file_get_contents(ROOT.'/b.html');
    	}
    	//页数先不要了，后面如果非要确切的页码再来解析
    	/*
    	$preg = '/<div class="pageList">(.*?)<\/div>/ism';
    	preg_match_all($preg,$str,$matches);
    	if(isset($matches[0]) && !empty($matches[0])){}
    	*/
    	$preg = "/<tr.*?>(.*?)<\/tr>/ism";
    	preg_match_all($preg,$str,$matches);
    	$allcoin_info = array();
		foreach($matches[0] as $key=>$v)
		{
			$temp_info = array();
			$removestr = '';
			if($key < 1){continue;}
			//根据分析获取出名称、价格、24小时成交额
			$a = "/<a.*?>(.*?)<\/a>/ism";
			preg_match_all($a,$v,$aarr);
			if(!empty($aarr[0]))
			{
				//echo count($aarr[0]);获取数量
				//获取到币的名称 ： strip_tags($aarr[0][0]);
				$temp_info['名称'] = strip_tags($aarr[0][0]);
				$temp_info['价格'] = strip_tags($aarr[0][1]);
				$temp_info['24小时成交额'] = strip_tags($aarr[0][2]);
				//去掉解析出来的a标签html
				$v = str_replace($aarr[0][0],'',$v);
				$v = str_replace($aarr[0][1],'',$v);
				$v = str_replace($aarr[0][2],'',$v);
			}
			
			//第二步拿取涨幅信息
			$span ="/<span.*?>(.*?)<\/span>/ism";
			preg_match_all($span,$v,$linearr);
			if(!empty($linearr[0]))
			{
				$temp_info['24小时涨幅'] = strip_tags($linearr[0][0]);
				//去掉解析出来的html
				$v = str_replace($linearr[0][0], '', $v);
				$v = str_replace($linearr[0][1], '', $v);
			}
			//对最后剩下的数据进行处理
			$tmpv = strip_tags($v);
			$pattern = '/\s/';//去除空白
			$newstr = preg_replace($pattern, '@', $tmpv);
			$newstr = array_values(array_filter(explode('@',$newstr)));
			$temp_info['流通市值'] = isset($newstr[1]) ? $newstr[1] : '';
			$temp_info['流通数量']  = isset($newstr[2]) ? $newstr[2] : '';
			//给对应的列赋值
			//$cloune = array(0=>'名称',1=>'流通市值',2=>'价格',3=>'流通数量',4=>'24小时成交额',5=>'24小时涨幅');
			array_push($allcoin_info,$temp_info);
		}
		
		if(empty($allcoin_info)){
			return false;
		}

		$RealModel = M('DataCollectionInfo');
		foreach($allcoin_info as $obj)
		{
			$data['source_id'] = '1';
			$data['bi_name'] = $obj['名称'];
			$data['bi_trade_price'] = $obj['价格'];
			$data['bi_trade_money'] = $obj['24小时成交额'];
			$data['bi_all_num'] = $obj['流通数量'];
			$data['bi_all_money'] = $obj['流通市值'];
			$data['bi_change'] = $obj['24小时涨幅'];
			$data['bi_add_time'] = date('Y-m-d H:i:s');
			$data['add_user'] = 'lly';
			$RealModel->data($data)->add();
			echo $RealModel->_sql().'<br/>';
		}
    }

    public function get_data_from_alcoinAction()
    {
    	/*获取数据
    	$source_url = 'http://www.aicoin.net.cn/currencies';
    	$curl_service = new UtilService();
    	$info = $curl_service->curl_get_http($source_url);
    	file_put_contents('b.html',$info);*/
    	//接下来解析第一步获取到的数据
    	if(file_exists(ROOT.'/b.html')){
    		$str = file_get_contents(ROOT.'/b.html');
    	}
		$dataService = new DataService();
		$data_info = $dataService->get_data_from_alcoin();
		print_r($data_info);
    }


    /*
	 * used : 投资项目配置数据录入
	 * addby : lly
	 * date : 2018-03-15
    */
    public function project_config_adddebugAction()
    {
    	/*
    	 * pro_name = '投资项目'
    	 * pro_alias = '代币名称'
    	 * pro_money = '额度'
    	 * pro_num = '折合代币数'
    	 * pro_trade_net = '交易所'
    	 * pro_status_desc = '锁仓情况'
    	 * pro_ico_base_price = 'ICO成本(ETH')
    	 * pro_ico_now_price = '现价(ETH)'
    	 * pro_money_percent = '目前收益率'
    	 * pro_money_basebi_name = '项目投资基本单位币元,默认为ETH';
    	*/
    	//模拟组装数据
    	$bi_info = array(
    		array(
	    		'pro_name' => 'XRP-瑞波币',
	    		'pro_alias' => 'ripple',
	    		'pro_money' => '1000',
	    		'pro_num' => '5000000',
	    		'pro_trade_net' => 'BIT-Z',
	    		'pro_status_desc' => '锁仓50%，另外每月10%',
	    		'pro_ico_base_price' => '',
	    		'pro_ico_now_price' => '',
	    		'pro_money_percent' => '',
	    		'pro_money_basebi_name' => 'ETH',
	    		'pro_add_time' => date('Y-m-d H:i:s'),
	    		'pro_add_user' => 'lly'
    		),
    		array(
	    		'pro_name' => 'LTC-莱特币',
	    		'pro_alias' => 'litecoin',
	    		'pro_money' => '300',
	    		'pro_num' => '3666600',
	    		'pro_trade_net' => 'coinegg',
	    		'pro_status_desc' => '第一个月解锁40%，首月的40%分四次打过来，后五个月每个月12%',
	    		'pro_ico_base_price' => '',
	    		'pro_ico_now_price' => '',
	    		'pro_money_percent' => '',
	    		'pro_money_basebi_name' => 'ETH',
	    		'pro_add_time' => date('Y-m-d H:i:s'),
	    		'pro_add_user' => 'lly'
    		)
    	);
    	$insertModel = M('DataProjectConfig');
    	foreach($bi_info as $obj)
    	{
    		$obj['pro_ico_base_price'] = $this->_del_right_O(bcdiv($obj['pro_money'],$obj['pro_num'],8));
    		$insertModel->data($obj)->add();
    		echo $insertModel->_sql();
    	}
    }

    /**
     * used : 根据配置的信息,批量或者是单个获取信息,然后更新目前价格和收益,同时录入价格信息获取数据
     * addby : lly
     * date : 2018-03-15
    **/
    public function update_configAction()
    {
    	$where['pro_is_delete'] = array('eq',0);
    	$pro_id = I('proid',0);
    	if($pro_id != 0 ){
    		if(is_array($pro_id)){
    			$where['pro_id'] = array('in',$pro_id);
    		}else{
    			$where['pro_id'] = array('eq',$pro_id);
    		}
    	}
    	$configModel = M('DataProjectConfig');
    	$data = $configModel->where($where)->select();
    	if(empty($data)){
    		echo '没有满足条件的数据';
    		exit();
    	}

    	$base_biprice = 0;
    	//抓取记录数组
    	$price_getarr = array();
    	//基础货币的值
    	$basebi_value = array();
    	foreach($data as $k=>$lineinfo)
    	{
    		
    		$basebiname = $lineinfo['pro_money_basebi_name'];
    		//if($base_biprice==0 && $lineinfo['pro_money_basebi_name'] =='ETH')
    		//如果基础币价格数组里面没有，说明还没有获取，目的是到时候为了获取多个基础币的信息
    		if(!isset($basebi_value[$basebiname]) && in_array($basebiname, array_keys($this->basebi_arr)))
    		{
    			$base_biname = $this->basebi_arr[$basebiname];
    			$base_biprice = $this->_get_price_byfeixiaohao($base_biname); //'3778';
    			$basebi_value[$basebiname] = $base_biprice;
    		}
    		//获取投资币自身现在的价格
    		$bi_alias = $lineinfo['pro_alias'];//'ripple';
    		if($bi_alias !=''){
	    		$self_price = $this->_get_price_byfeixiaohao($bi_alias); //'4.29';
	    	}
	    	//如果目标价格和自身价格都获取成功了
	    	if($base_biprice>0 && $self_price>0)
	    	{
	    		//ico现在的成本
	    		$lineinfo['pro_ico_now_price'] = $tmpprice = $this->_del_right_O(bcdiv($self_price,$base_biprice,8));
	    		//目前收益率(现在成本-ico成本)/ico成本*100= ?%
	    		$price_sub = bcsub($tmpprice,$lineinfo['pro_ico_base_price'],8);
	    		$price_sub = bcdiv($price_sub,$lineinfo['pro_ico_base_price'],6);
	    		$lineinfo['pro_money_percent']  = $this->_del_right_O(bcmul($price_sub,100,4));
	    		$configModel->save($lineinfo);
	    	}
	    	$price_dt['pro_id'] = $lineinfo['pro_id'];
	    	$price_dt['pro_alias_name'] = $lineinfo['pro_alias'];
	    	$price_dt['pro_basebi_name'] = $lineinfo['pro_money_basebi_name'];
	    	$price_dt['pro_bi_price'] = $self_price;
	    	$price_dt['pro_basebi_price'] = $base_biprice;
	    	$price_dt['pro_price_getsource'] = 1;
	    	$price_dt['price_add_time'] = date('Y-m-d H:i');
	    	$price_dt['price_add_user'] = 'lly';
	    	array_push($price_getarr,$price_dt);
    	}
    	$priceModel = M('DataProjectPricelist');
    	if(!empty($price_getarr)){
    		foreach($price_getarr as $priceobj)
    		{
    			$priceModel->data($priceobj)->add();
    		}
    	}
    	/*
    	//基础币的价格
    	$base_biprice = '3778'; //$this->_get_price_byfeixiaohao();
    	//瑞波币的价格
    	$ripple_price = '4.29'; //$this->_get_price_byfeixiaohao('ripple');
    	$pro_ico_now_price = bcdiv($ripple_price,$base_biprice,8);
    	echo $pro_ico_now_price;*/
    }

    //币的获取
    //从非小号默认获取ETH的价格
    private function _get_price_byfeixiaohao($base_biname='ethereum')
    {
    	//瑞波ripple
    	$source_url = 'https://www.feixiaohao.com/currencies/'.$base_biname.'/';
    	$curl_service = new UtilService();
    	$info = $curl_service->curl_get_http($source_url);
    	$filaName = ROOT.'/bilog/'.$base_biname.'_'.date('Ymd H:i:s').'.html';
    	file_put_contents($filaName,$info);

    	if(file_exists($filaName)){
    		$str = file_get_contents($filaName);
    	}
    	//正则出显示价格的那个地方
    	$div ='/<div class="coinprice">(.*?)<\/div>/ism';
		preg_match_all($div,$str,$linearr);

		//获取出div包含的涨幅的span
		$span = '/<span class="tags.*?>(.*?)<\/span>/ism';
		preg_match_all($span,$linearr[0][0],$spanarr);
		//去掉涨幅
		$new_str = str_replace($spanarr[0][0],'', $linearr[0][0]);
		//去掉标签获取到值
		$strinfo = strip_tags($new_str);
		//去掉货币符号,和字符分隔符
		$strinfo = str_replace('￥','',$strinfo);
		$strinfo = str_replace(',','',$strinfo);
		return $strinfo;
    }

    private function _del_right_O($str='0.00')
    {
    	return rtrim(rtrim($str,'0'),'.');
    }


    /**
     * used : 根据筛选的信息,下载
     * addby : lly
     * date : 2018-03-15
    **/
    public function downloadAction()
    {
    	$where['pro_is_delete'] = array('eq',0);
    	$pro_id = I('proid',0);
    	if($pro_id != 0 ){
    		if(is_array($pro_id)){
    			$where['pro_id'] = array('in',$pro_id);
    		}else{
    			$where['pro_id'] = array('eq',$pro_id);
    		}
    	}
    	$configModel = M('DataProjectConfig');
    	$data = $configModel->field('pro_name,pro_alias,pro_money,pro_num,pro_trade_net,pro_status_desc,pro_ico_base_price,pro_ico_now_price,pro_money_percent')->where($where)->select();
    	if(empty($data)){
    		echo '没有满足条件的数据';
    		exit();
    	}
    	$pro_percent = array_column($data,'pro_money_percent');
    	$tmppercent = $this->_del_right_O(bcdiv(array_sum($pro_percent),count($data),4));
    	foreach($data as &$obj){
    		$obj['avg_percent'] = $tmppercent;
    	}
    	$Excel = A('Excel');
        $Excel->outBiBiReport($data);

    }

    public function llyAction()
    {
    	$to_email = '939942478@qq.com';
    	$subject = '测试发送邮件';
    	$email_content = '请您关注';
    	$file = array(ROOT.'/Public/upload/money/项目币币收益 3月11日24点.xlsx');
    	sendMail($to_email,$subject,$email_content,$file);
    	/*
			在价格剧烈变化时：
		   【预警】目标币种（）发生变化，（）分钟内波动为（），成交量为（）请您密切关注！
			在达到预定盈利目标时：
		   【预警】发现币种（）已达成目标收益率（）%，价格为xxx，请您密切关注并售出。
		*/
    }


    
    /**
	 * used : 项目添加和编辑
	 * addby : lly
	 * date : 2018-03-16
    **/
    public function project_config_editAction()
    {
    	$title = '添加项目';
    	$isadd = 1;
    	$data = array();
    	$post_info = I('post.');
    	$configModel = M('DataProjectConfig');
    	$fields = $configModel->getDbFields();
    	//为编辑专用
    	$proid = I('id',0);
    	//如果是直接进入链接(可能带了id)
    	if(empty($post_info))
    	{
    		//如果是点击编辑按钮进来的
    		//如果不是提交信息
    		if(!isset($post_info['action'])){
    			if($proid>0){
    				$where['pro_id'] = array('eq',$proid);
		    		$title = '编辑项目';
		    		$tmpdata = $configModel->where($where)->find();
		    		if(empty($tmpdata)){
		    			$this->error('数据获取异常,请您别瞎搞','/admin/index/project_config_list');
    					exit();
		    		}
		    		foreach($tmpdata as $k=>$v)
		    		{
		    			$lk = str_replace('pro_','',$k);
		    			$data[$lk] = $v;
		    			unset($tmpdata[$k]);
		    		}
		    		$isadd = 0;
    			}
    		}
    	}else{	
    		//如果不是提交信息，而是别人伪提交
			$tb_data = array();
			foreach($post_info as $k=>$v)
			{
				$pro_k = 'pro_'.$k;
				if(in_array($pro_k,$fields) && $v !='')
				{
					$tb_data[$pro_k] = $v;
				}
			}
			//说明是提交信息
			//(1)计算成本
			if($tb_data['pro_money']>0 && $tb_data['pro_num']>0)
			{
				$tb_data['pro_ico_base_price'] = $this->_del_right_O(bcdiv($tb_data['pro_money'],$tb_data['pro_num'],8));
			}

			$tb_data['pro_add_user'] = 'lly';
			$tb_data['pro_add_time'] = date('Y-m-d H:i:s');
			//如果提交的proid大于0 并且操作不是添加 说明是编辑 并且action的值取的是是否添加  			
			if($proid>0 && $post_info['action']<1){
				$configModel->data($tb_data)->save();
				$this->success('更新成功','/admin/index/project_config_list',3);
				exit();
				//说明是编辑
			}elseif($proid==0 && I('action')>0){
				unset($tb_data['pro_id']);
				//说明是添加
				$configModel->data($tb_data)->add();
				$this->success('添加成功','/admin/index/project_config_list',3);
				exit();
			}else{
				$this->error('非法操作','/admin/index/project_config_list',3);
				exit();
			}
    	}
    	$this->assign('isadd',$isadd);
    	$this->assign('title',$title);
    	$this->assign('data',$data);
    	$this->display();
    }

    
    /**
     * 数据过滤&改变数组key
    **/
    private function _combine_key($data=array(),$pre='',$needjson=0)
    {
    	if(empty($data)){
    		return $data;
    	}
    	$res = array();
    	foreach($data as $k=>$v)
    	{
    		if($v!=='')
    		{
    			if(is_array($v)){
    				$v = $needjson ? json_encode($v) : $v;
    			}
    			$res[$pre.$k] = $v;
    		}
    	}
    	return $res;
    }

}