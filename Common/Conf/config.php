<?php
return array(
	//'配置项'=>'配置值'

	//Rbac配置
    'RBAC_SUPERADMIN'=>'cszb_admin',         //超级管理员名称
    'ADMIN_AUTH_KEY'=>'superadmin',     //超级管理员识别，存放在Session中
    'USER_AUTH_ON'=>true,               //是否开启权限认证
    'USER_AUTH_TYPE'=>2,                //验证类型 1-登陆时验证 2-实时验证
    'USER_AUTH_KEY'=>'uid',             //存储在session中的识别号
    'NOT_AUTH_MODULE'=>'Index',              //无需验证的控制器
    'NOT_AUTH_ACTION'=>'index',              //无需验证的方法
    'RBAC_ROLE_TABLE'=>'role',      //角色表名称
    'RBAC_USER_TABLE'=>'think_role_user', //角色与用户的中间表名称（注意）
    'RBAC_ACCESS_TABLE'=>'think_access',  //权限表名称
    'RBAC_NODE_TABLE'=>'think_node',      //节点表名称


    'ADMIN_LEFT_MENU' => array(
        'Index' => array(
            'name'=>'后台首页','mod'=>'Index','icon'=>'fa-home','url'=>'Index/index',
            'list'=>array( 
                array('name' => '后台首页', 'url' => 'Index/index','act'=>'index')
            )),
        'Project' => array(
            'name'=>'项目管理','mod'=>'Project','icon'=>'fa-home','url'=>'project/index',
            'list'=>array( 
                array('name' => '项目列表', 'url' => 'project/project_list','act'=>'project_list'),
                array('name' => '项目添加', 'url' => 'project/project_edit','act'=>'project_edit')
            )),
        'Rbac' => array(
            'name'=>'权限管理','mod'=>'Rbac','icon'=>'fa-home','url'=>'rbac/node_list',
            'list'=>array( 
                array('name' => '角色列表', 'url' => 'rbac/role_list','act'=>'role_list'),
                array('name' => '权限列表', 'url' => 'rbac/node_list','act'=>'node_list'),
                array('name' => '用户列表', 'url' => 'rbac/user_list','act'=>'user_list')
            )), 
        'Sysconfig' => array(
            'name'=>'系统设置','mod'=>'Sysconfig','icon'=>'fa-columns','url'=>'Sysconfig/index',
            'list'=>array( 
                array('name' => '提醒设置', 'url' => 'sysconfig/index','act'=>'index'),
                array('name' => '价格获取日志','url'=>'sysconfig/sys_pricelog','act'=>'sys_pricelog')
            ))
    )
);
