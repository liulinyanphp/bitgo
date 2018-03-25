<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Amaze UI Admin index Examples</title>
    <meta name="description" content="这是一个 index 页面">
    <meta name="keywords" content="index">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="icon" type="image/png" href="<?php echo ($base_path); ?>/i/favicon.png">
    <link rel="apple-touch-icon-precomposed" href="<?php echo ($base_path); ?>/i/app-icon72x72@2x.png">
    <meta name="apple-mobile-web-app-title" content="Amaze UI" />
    <link rel="stylesheet" href="<?php echo ($base_path); ?>/css/amazeui.min.css"/>
    <link rel="stylesheet" href="<?php echo ($base_path); ?>/css/admin.css">
    <script src="<?php echo ($base_path); ?>/js/jquery.min.js"></script>
    <script src="<?php echo ($base_path); ?>/js/app.js"></script>
</head>
<body>
<!--[if lte IE 9]>
  <p class="browsehappy">升级你的浏览器吧！ <a href="http://se.360.cn/" target="_blank">升级浏览器</a>以获得更好的体验！</p>
<![endif]-->
<header class="am-topbar admin-header">
    <div class="am-topbar-brand"><img src="<?php echo ($base_path); ?>/i/logo.png"></div>
    <div class="am-collapse am-topbar-collapse" id="topbar-collapse">
        <ul class="am-nav am-nav-pills am-topbar-nav admin-header-list">

            <li class="kuanjie">
                 <?php if(is_array($menu)): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menuinfo): $mod = ($i % 2 );++$i;?><a href="<?php echo U($menuinfo['url']);?>"><?php echo ($menuinfo["name"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
                <a href="<?php echo U('Login/loginout');?>">退出</a>
            </li>
            <li class="am-hide-sm-only" style="float: right;">
                <a href="javascript:;" id="admin-fullscreen">
                    <span class="am-icon-arrows-alt"></span>
                    <span class="admin-fullText">开启全屏</span>
                </a>
            </li>
        </ul>
    </div>
</header>
<div class="am-cf admin-main">
    <div class="nav-navicon admin-main admin-sidebar">
        <div class="sideMenu am-icon-dashboard" style="color:#aeb2b7; margin: 10px 0 0 0;"> 欢迎您：<?php echo ($username); ?></div>
        <div class="sideMenu">
            <?php if(is_array($menu)): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menuinfo): $mod = ($i % 2 );++$i;?><h3 class="am-icon-flag <?php echo ($menuinfo["open"]); ?>"><em></em> <a href="#"><?php echo ($menuinfo["name"]); ?></a></h3>
                <ul>
                <?php if(is_array($menuinfo['list'])): $i = 0; $__LIST__ = $menuinfo['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menulist): $mod = ($i % 2 );++$i;?><li><a href="/admin/<?php echo ($menulist["url"]); ?>"><?php echo ($menulist["name"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
                </ul><?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <!-- sideMenu End -->
        <script type="text/javascript">
            jQuery(".sideMenu").slide({
            titCell:"h3", //鼠标触发对象
            targetCell:"ul", //与titCell一一对应，第n个titCell控制第n个targetCell的显示隐藏
            effect:"slideDown", //targetCell下拉效果
            delayTime:300 , //效果时间
            triggerTime:150, //鼠标延迟触发时间（默认150）
            defaultPlay:true,//默认是否执行效果（默认true）
            returnDefault:true //鼠标从.sideMen移走后返回默认状态（默认false）
            });
        </script>
    </div>
    <div class=" admin-content">
        <div class="daohang">
            <ul>
                <li><button type="button" class="am-btn am-btn-default am-radius am-btn-xs"> 首页 </li>
            </ul>
        </div>
        <div class="admin">
            <div class="admin-index">
                <dl data-am-scrollspy="{animation: 'slide-right', delay: 100}">
                    <dt class="qs"><i class="am-icon-users"></i></dt>
                    <dd>20</dd>
                    <dd class="f12">团队数量</dd>
                </dl>
                <dl data-am-scrollspy="{animation: 'slide-right', delay: 300}">
                    <dt class="cs"><i class="am-icon-area-chart"></i></dt>
                    <dd>10</dd>
                    <dd class="f12">项目总数</dd>
                </dl>
                <dl data-am-scrollspy="{animation: 'slide-right', delay: 900}">
                    <dt class="ls"><i class="am-icon-cny"></i></dt>
                    <dd>***</dd>
                    <dd class="f12">全部收入</dd>
                </dl>
            </div>
            <div class="admin-biaoge">
                <div class="xinxitj">项目概况</div>
                <table class="am-table">
                    <thead>
                        <tr>
                            <th>项目名称</th>
                            <th>交易所</th>
                            <th>最新价格</th>
                            <th>目前收益</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(is_array($prolist)): $i = 0; $__LIST__ = $prolist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$proinfo): $mod = ($i % 2 );++$i;?><tr>
                                <td><?php echo ($proinfo["pro_name"]); ?></td>
                                <td><?php echo ($proinfo["pro_trade_net"]); ?></td>
                                <td>¥<?php echo ($proinfo["pro_ico_lastprice"]); ?></td>
                                <td><?php echo ((isset($proinfo["pro_money_percent"]) && ($proinfo["pro_money_percent"] !== ""))?($proinfo["pro_money_percent"]):0); ?>%</td>
                                <td><a href="#">更新价格</a></td>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="shuju" style="margin-bottom: 40px;">
                <div class="shujuone">
                    <dl>
                        <?php if(is_array($chenben)): foreach($chenben as $key=>$basebipro): ?><dt>投入<?php echo ($basebipro["basebiname"]); ?>：  <?php echo ($basebipro["sum"]); ?></dt><?php endforeach; endif; ?>
                        <dt>投入USDT:</dt>
                    </dl>
                    <ul>
                        <h2><?php echo ($prv_percent); ?>%</h2>
                        <li>平均收益率</li>
                    </ul>
                </div>
                <div class="shujutow">
                    <dl>
                        <?php if(is_array($nowprice)): foreach($nowprice as $key=>$basebinowinfo): ?><dt>现值<?php echo ($basebinowinfo["basebiname"]); ?>：  <?php echo ($basebinowinfo["sum"]); ?></dt><?php endforeach; endif; ?>
                        <dt>现值USDT:</dt>
                    </dl>
                    <ul>
                        <h2><?php echo ($allpercent); ?>%</h2>
                        <li>总收益率</li>
                    </ul>
                </div>
            </div>
            <script type="text/javascript">jQuery(".slideTxtBox").slide();</script> 


		    <div class="foods">
		    	<ul>版权所有@2018 .模板收集自 <a href="http://www.liulinyan.com/" target="_blank" title="PHP中文网">燕子网</a> 
		        -  More Templates
		        <a href="http://www.php.cn/" title="网页模板" target="_blank">网页模板</a>
		      </ul>
		    	<dl><a href="" title="返回头部" class="am-icon-btn am-icon-arrow-up"></a></dl>  	
		    </div>
		</div>
	</div>
</div>
<!--[if lt IE 9]>
<script src="http://libs.baidu.com/jquery/1.11.1/jquery.min.js"></script>
<script src="http://cdn.staticfile.org/modernizr/2.8.3/modernizr.js"></script>
<script src="<?php echo ($base_path); ?>/js/polyfill/rem.min.js"></script>
<script src="<?php echo ($base_path); ?>/js/polyfill/respond.min.js"></script>
<script src="<?php echo ($base_path); ?>/js/amazeui.legacy.js"></script>
<![endif]--> 
<!--[if (gte IE 9)|!(IE)]><!--> 
<script src="<?php echo ($base_path); ?>/js/amazeui.min.js"></script>
<!--<![endif]-->
</body>
</html>