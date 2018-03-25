<?php
namespace Admin\Controller;

use Common\Service\DataService;
use Common\Service\UtilService;

use Think\Controller;

class ProjectController extends AdminBaseController {

    public $basebi_arr = array(
            'ETH'=>'ethereum',
            'BTC'=>'bitcoin',
            'USDT'=>'tether'
    );
    /**
     * desc : 结合amaze UI的函数
     * used : 项目列表
     * addby : lly
     * date : 2018-03-16
    **/
    public function project_listAction()
    {

        $pageNow = I('p',1);
        $limitRows = 10;
        $where['pro_is_delete'] = array('eq',0);
        $configModel = M('DataProjectConfig');
        $data = $configModel->where($where)->page($pageNow .','. $limitRows)->order('pro_id desc')->select();
        $count = $configModel->where($where)->count();
        $Page = new  \Think\AdminPage ( $count, $limitRows, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('pager',$show);
        $this->assign('prolist',$data);
        $this->display();
    }

    /**
     * used : 项目添加和编辑
     * addby : lly
     * date : 2018-03-16
    **/
    public function project_editAction()
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

    public function anylc_data_for_fxh()
    {

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
     * @parames now_price 现在的价格
     * @parames $proinfo  项目的信息
     * @parames $cjnum   24小时成交量
     * @tarpercent 警告百分比。5% =》 0.05   默认值3分钟5%给出邮件提示
    **/
    private function _worn_cal($proinfo=array(),$nowprice=5,$cjnum=0,$tarpercent='0.05')
    {

        $worning_str = '';
        if(empty($proinfo)){
            return $worning_str;
        }
        $befor_price = $proinfo['pro_ico_lastprice'];
        //警告的浮动值
        $worn_price = bcmul($befor_price,$tarpercent,2);
        //警告的最高值
        $height_price = bcadd($befor_price,$worn_price,2);
        //警告的最低值
        $low_price = bcsub($befor_price,$worn_price,2);
        if($nowprice>=$height_price || $nowprice<= $low_price)
        {
            $uplow = bcsub($nowprice,$befor_price,2);
            $uplow = bcdiv($uplow,$befor_price,6);
            //浮动率
            $uplow_percent = $this->_del_right_O(bcmul($uplow,100,4));
            $nowtime = date('Y-m-d H:i:s');
            $price_gettime = $proinfo['pro_price_gettime'];
            $timeinfo = timediff(strtotime($price_gettime),strtotime($nowtime));
            $worning_str .= '【预警】目标币种（'.$proinfo['pro_alias'].'）发生变化，（'.$timeinfo['min'].'）分钟内波动为（'.$uplow_percent.'%），成交量为（'.$cjnum.'）请您密切关注！\r\n';
        }
        return $worning_str;
    }

    
    //币的获取
    //从非小号默认获取ETH的价格
    private function _get_price_byfeixiaohao($base_biname='ethereum')
    {
    	
        $logdir = ROOT.'/bilog';
        //瑞波ripple
    	$source_url = 'https://www.feixiaohao.com/currencies/'.$base_biname.'/';
    	$curl_service = new UtilService();
    	$info = $curl_service->curl_get_http($source_url);
    	$filaName = $logdir.'/'.$base_biname.'_'.date('His').'.html';
    	file_put_contents($filaName,$info);
        $res = array('price'=>0,'cjnum'=>0);
    	if(file_exists($filaName)){
    		$str = file_get_contents($filaName);
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
            if(!empty($strinfo)){
                $res['price'] = $strinfo;
            }
            //正则显示出成交量的地方<span class="tag-vol">
            $tmp_cj = '/<div class="value">(.*?)<\/div>/ism';
            preg_match_all($tmp_cj,$str,$cjarr);
            $money = strip_tags($cjarr[0][3]);
            $money = trim(str_replace('¥','',$money));
            $money = str_replace(',','',$money);
            $di_index = strpos($money,'第');
            $cj_num = substr($money,0,$di_index);
            if(!empty($cj_num) && $strinfo>0) {
                $res['cjnum'] = bcdiv($cj_num,$strinfo,4);
            }
            return $res;
    	}
		return $strinfo;
    }

    private function _del_right_O($str='0.00')
    {
    	return rtrim(rtrim($str,'0'),'.');
    }



    
    


    /*
     * addby :lly
     * date : 2018-03-21
     * used : 获取价格和24小时成交量
    */
    public function get_price_byfeixiaohaoAction()
    {
        //瑞波ripple
        $source_url = 'https://www.feixiaohao.com/currencies/pixiecoin/';
        $curl_service = new UtilService();
        $info = $curl_service->curl_get_http($source_url);
        $filaName = ROOT.'/bilog/'.$base_biname.'_'.date('Ymd H:i:s').'.html';
        //file_put_contents($filaName,$info);
        $res = array('price'=>0,'cjnum'=>0);

        $dir = ROOT.'/bilog/'.date('Y-m-d');
        if (!file_exists($dir)){
            mkdir ($dir,0777,true);  
        } 
        $base_biname = 'aaa';
        $fileName = $dir.'/'.$base_biname.'_'.date('His').'.html';
        echo $fileName;die();
        if(file_exists($fileName)){
            $str = file_get_contents($fileName);
        }
        die();
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
        if(!empty($strinfo)){
            $res['price'] = $strinfo;
        }

        //正则显示出成交量的地方<span class="tag-vol">
        $tmp_cj = '/<div class="value">(.*?)<\/div>/ism';
        preg_match_all($tmp_cj,$str,$cjarr);
        $money = strip_tags($cjarr[0][3]);
        $money = trim(str_replace('¥','',$money));
        $money = str_replace(',','',$money);
        $di_index = strpos($money,'第');
        $cj_num = substr($money,0,$di_index);
        if(!empty($cj_num) && $strinfo>0) {
            $res['cjnum'] = bcdiv($cj_num,$strinfo,2);
        }
        return $res;
    }


    /**
     * used : 根据配置信息,批量更新价格
     * addby : lly
     * date : 2018-03-23
    **/

    public function upprice_for_prolistAction()
    {
        $pro_id = I('proid');
        if(empty($pro_id))
        {
            $this->error('请您先选择要导出的记录','/admin/index/project_config_list',1);
            exit();
        }
        $proids = explode(',',$pro_id);
        $proids = array_filter($proids);
        $checkprice = 1;
        $data = $this->_getprodata_forexcel_byid($proids,$checkprice);
        if($data =='nodata'){
            echo '没有满足条件的数据';
            exit();
        }
        $this->_upprice_for_pro($data);
    }

    /**
     * used : 根据配置的信息,批量或者是单个获取信息,然后更新目前价格和收益,同时录入价格信息获取数据
     * addby : lly
     * date : 2018-03-15
    **/
    public function targ_checkAction()
    {
        $pro_id = I('proid');
        if(empty($pro_id))
        {
            $this->error('请您先选择要导出的记录','/admin/index/project_config_list',1);
            exit();
        }
        $proids = explode(',',$pro_id);
        $proids = array_filter($proids);
        $checkprice = 1;
        $data = $this->_getprodata_forexcel_byid($proids,$checkprice);
        if($data =='nodata'){
            echo '没有满足条件的数据';
            exit();
        }
        $this->_upprice_for_pro($data,1);
    }

    /**
     * addby : lly
     * date : 2013-03-23
     * used : 封装价格更新函数
     * prolist 项目的数据
     * ischeck_fazhi  是否检查阀值,0为不检查,1为检查（主要做价格报警用）
    **/

    private function _upprice_for_pro($prolist=array(),$ischeck_fazhi=0)
    {
        if(empty($prolist))
        {
            return fasle;
        }
        //抓取价格记录数组
        $price_getarr = array();
        //用来存储不同基础货币的值
        $basebi_value = array();
        //价格波动太大，警告的str;
        $worning_str = '';
        //目标达成
        $worn_okmsg = '';
        $configModel = M('DataProjectConfig');
        foreach($prolist as $k=>$lineinfo)
        {
            $base_biprice = $self_price = 0;  //在没有获取价格之前设置代币和自身币的价格为0
            //基础货币的名称如ETH,BTC
            $basebiname = $lineinfo['pro_money_basebi_name'];
            //如果基础币价格数组里面没有，说明还没有获取，目的是到时候为了获取多个基础币的信息
            if(!isset($basebi_value[$basebiname]) && in_array($basebiname, array_keys($this->basebi_arr)))
            {
                //去非小号获取价格的别名
                $base_biname = $this->basebi_arr[$basebiname];
                //拿取这个取非小号获取价格
                $base_bi_info= $this->_get_price_byfeixiaohao($base_biname);
                //获取到基础货币的值
                $base_biprice = $base_bi_info['price'];
                //存储获取到的基础币的价格
                $basebi_value[$basebiname] = $base_biprice;
            }
            if(isset($basebi_value[$basebiname])){
                $base_biprice = $basebi_value[$basebiname];
            }

            //获取投资币自身现在的价格 如瑞波币 ripple
            $bi_alias = trim($lineinfo['pro_alias']);
            if($bi_alias !=''){
                $self_bi_info = $this->_get_price_byfeixiaohao($bi_alias);
                $self_price = $self_bi_info['price'];
            }else{
                continue;
            }
            //如果目标价格和自身价格都获取成功了
            if($base_biprice>0 && $self_price>0)
            {
                //ico现在的兑换基础币的价格 如0.0001ETH
                $lineinfo['pro_ico_now_price'] = $tmpprice = $this->_del_right_O(bcdiv($self_price,$base_biprice,8));
                //目前收益率(现在成本-ico成本)/ico成本*100= ?%  直接是最后的百分比
                $price_sub = bcsub($tmpprice,$lineinfo['pro_ico_base_price'],8);
                $price_sub = bcdiv($price_sub,$lineinfo['pro_ico_base_price'],6);
                $now_promoneypercent = $this->_del_right_O(bcmul($price_sub,100,4));              
                //看是否需要发出警告
                if($ischeck_fazhi>0)
                {
                    $worn_price = bcmul($lineinfo['pro_ico_lastprice'],0.05,2);
                    $worning_str .= $this->_worn_cal($lineinfo,$self_price,$self_bi_info['cjnum']);
                }
                //先算再赋值
                $lineinfo['pro_ico_lastprice'] = $self_price;
                $lineinfo['pro_ico_tradenum'] = $self_bi_info['cjnum'];
                $lineinfo['pro_price_gettime'] = date('Y-m-d H:i:s');
                //看是否达到收益率. 当前收益率是否大于目标收益率
                if($ischeck_fazhi>0 && $now_promoneypercent>=$lineinfo['pro_money_percent'])
                {
                    $worn_okmsg .= '发现币种（'.$bi_alias.'）已达成目标收益率（'.$lineinfo['pro_money_percent'].'%)，价格为';
                    $worn_okmsg .= $price_sub.$basebiname.'，目前收益率为'.$now_promoneypercent.'%请您密切关注并售出。';
                }
                //亏损的后面再追加

                //更新目前收益率
                $lineinfo['pro_money_percent']  = $now_promoneypercent;

                //如果当前收益率大于最高收益率，则记录这次为最高
                if($now_promoneypercent > $lineinfo['pro_hight_percent'])
                {
                    $lineinfo['pro_hight_percent'] = $now_promoneypercent;
                    $lineinfo['pro_hightpercent_time'] = date('Y-m-d H:i:s');
                }
                //如果当前收益率小于最低收益率,则记录这次为最低
                if($now_promoneypercent  < $lineinfo['pro_low_percent'])
                {
                    $lineinfo['pro_low_percent'] = $now_promoneypercent;
                    $lineinfo['pro_lowpercent_time'] = date('Y-m-d H:i:s');
                }
                $configModel->save($lineinfo);
                //echo $configModel->_sql();
            }
            $price_dt['pro_id'] = $lineinfo['pro_id'];
            $price_dt['pro_alias_name'] = $lineinfo['pro_alias'];
            $price_dt['pro_daibi_name'] = $lineinfo['pro_daibi_name'];
            $price_dt['pro_basebi_name'] = $lineinfo['pro_money_basebi_name'];
            $price_dt['pro_bi_price'] = $self_price;
            $price_dt['pro_basebi_price'] = $base_biprice;
            $price_dt['pro_now_percent'] = $now_promoneypercent;
            $price_dt['pro_price_getsource'] = 1;
            $price_dt['pro_trade_num'] = isset($self_bi_info['cjnum']) ? $self_bi_info['cjnum'] : 0;
            $price_dt['price_add_time'] = date('Y-m-d H:i');
            $price_dt['price_add_user'] = 'lly';
            array_push($price_getarr,$price_dt);
        }
        //追加日志记录
        if(!empty($price_getarr)){
            $priceModel = M('DataProjectPricelist');
            foreach($price_getarr as $priceobj)
            {
                $priceModel->data($priceobj)->add();
            }
        }
        echo '处理完毕!';
        //如果是阀值检测才需要这个
        if($ischeck_fazhi){
            if($worning_str !='' || $worn_okmsg !='')
            {
                $sysemails = $this->_get_worn_email();
            }
            if($worning_str !=''){
                $email_title = '价格剧烈变化预警'.date('Y-m-d H:i:s');
                $this->_send_sample_email($sysemails,$email_title,$worning_str);
            }
            if($worn_okmsg !='')
            {
                $email_title = '达到预定盈利目标提示'.date('Y-m-d H:i:s');
                $this->_send_sample_email($sysemails,$email_title,$worn_okmsg);
            }
        }

    }


    /**
     * addby : lly
     * date : 2018-03-22
     * used : 封装根据1,2,3这样的id导出信息
    **/
    private function _getprodata_forexcel_byid($proids = array(),$checkprice=0)
    {
        if(empty($proids)){
            return 'id为空';
        }
        $where['pro_is_delete'] = array('eq',0);
        $where['pro_id'] = array('in',$proids);
        $configModel = M('DataProjectConfig');
        //所需字段
        $field = 'pro_name,pro_daibi_name,pro_money,pro_num,pro_trade_net,pro_status_desc,';
        $field .='pro_ico_base_price,pro_ico_now_price,pro_money_percent';
        if($checkprice>0)
        {
            $where['pro_alias'] = array('neq',1);
            $field = '*';
        }
        $data = $configModel->field($field)->where($where)->select();
        if(empty($data)){
            return 'nodata';
        }
        //如果是阀值检查 直接返回数据
        if($checkprice>0)
        {
            return $data;
        }
        //总收益率 = (现价的总价值-ico成本的总价值)/ico成本的总价值
        $all_price = 0;
        $all_chenben = 0;
        foreach($data as $k=>$v)
        {
            //单个币的总价. 代币数*ico成本.  代币数*ico现价
            $all_chenben = bcadd($all_chenben,bcmul($v['pro_num'],$v['pro_ico_base_price'],8),4);
            $all_price = bcadd($all_price,bcmul($v['pro_num'],$v['pro_ico_now_price'],8),4);
        }
        //总收益率
        $all_sub = bcsub($all_price,$all_chenben,4);
        $all_sub = bcmul($all_sub,100,4);
        $all_percent = $this->_del_right_O(bcdiv($all_sub,$all_chenben,4));

        //平均收益率
        $pro_percent = array_column($data,'pro_money_percent');
        $tmppercent = $this->_del_right_O(bcdiv(array_sum($pro_percent),count($data),4));
        foreach($data as &$obj){
            $obj['avg_percent'] = $tmppercent;
            $obj['all_percent'] = $all_percent;
        }
        return $data;
    }


    /**
     * used : 根据筛选的信息,按照需求模版导出excel
     * addby : lly
     * date : 2018-03-15
     * upate : 2018-03-20 20:50
    **/
    public function pro_exportAction()
    {
        $pro_id = I('proid');
        if(empty($pro_id))
        {
            $this->error('请您先选择要导出的记录','/admin/index/project_config_list',1);
            exit();
        }
        $proids = explode(',',$pro_id);
        $proids = array_filter($proids);
        $data = $this->_getprodata_forexcel_byid($proids);
        if($data=='nodata')
        {
            $this->error('没有满足条件的数据','/admin/index/project_config_list',1);
            exit();
        }
        $file_name= '项目币币收益 '.date('n月j日G点');
        $Excel = A('Excel');
        $Excel->outBiBiReport($data,$file_name);
    }


    /**
     * usde : 发送excel给到配置好了的email中
     * addby : lly
     * date : 2018-03-20
    **/
    public function pro_sendto_emailAction()
    {
        /*
         * 找到通用配置中的email,和自身配置的emal进行合并(必须选择单条记录,多条记录的话，有点乱，除非说一个项目发送一个邮件【后续可追加判断即可】)
        */
        $pro_id = I('proid');
        if(empty($pro_id))
        {
            $this->error('请您先选择要发送邮件的项目','/admin/index/project_config_list',1);
            exit();
        }
        $proids = explode(',',$pro_id);
        $proids = array_filter($proids);
        $data = $this->_getprodata_forexcel_byid($proids);
        if($data=='nodata')
        {
            $this->error('没有满足条件的数据','/admin/index/project_config_list',1);
            exit();
        }
        //开始导出
        $file_name= '项目币币收益 '.date('n月j日G点');
        $Excel = A('Excel');
        $Excel->outBiBiReport($data,$file_name,1);
        $fileName = $file_name.'.xlsx';
        //要发送的文件
        $file = array();
        if(file_exists(ROOT.'/Public/upload/money/export/'.$fileName))
        {
            $file = array(ROOT.'/Public/upload/money/export/'.$fileName);
        }else{
            echo '生成收益报表出错';
        }
       //发送邮件
       $sysemails = $this->_get_worn_email();
       $subject = '公司内部'.date('n月j日G点').'收益';
       $email_content = '尊敬的领导请您查收今天的收益报表,谢谢';
       $this->_send_sample_email($sysemails,$subject,$email_content,$file);
    }

    /**
     * addby :lly
     * date : 2018-03-22 21:35
     * used : 公用的获取配置需要发送的邮箱
    **/
    private function _get_worn_email()
    {
        $sysemails = array();
        $sysModel = M('DataWorningConfig');
        $sysemail = $sysModel->Field('worn_email as sys_email')->find();
        if(!empty($sysemail))
        {
            $tmp_data = str_replace('；',';',$sysemail['sys_email']);
            $sysemails = array_filter(explode(';',$tmp_data));
        }
        return $sysemails;
    }

    /*
     * 简单的发送邮件
     * sysemails 系统配置的需要发送的接受邮件
     * title.    邮件主题
     * content.  邮件内容
    */
    private function _send_sample_email($sysemails=array(),$subject='系统警报',$email_content='',$file=array())
    { 
        if(empty($sysemails))
        {
            return false;
        }
        if($email_content =='')
        {
            $email_content ='尊敬的领导请您查收今天的收益报表,谢谢';
        }
        $sendmailModel = M('DataSendemailLog');
        //循环给配置了邮箱的人发送邮件
        foreach($sysemails as $to_email)
        {
            $to_email = strip_tags(trim($to_email));
            $issendok = sendMail($to_email,$subject,$email_content,$file);
            $issendok = 1;
            $send_email_log = array();
            $send_email_log['email'] = $to_email;
            $send_email_log['subject'] = $subject;
            $send_email_log['content'] = $email_content;
            $send_email_log['filename'] = empty($file) ? '' : json_encode($file);
            $send_email_log['status'] = $issendok ? 1 : 0 ;
            $send_email_log['add_user'] = 'lly';
            $send_email_log['add_time'] = date('Y-m-d H:i:s');
            $sendmailModel->data($send_email_log)->add();
        }
    }

}