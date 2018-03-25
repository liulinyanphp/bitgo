<?php

namespace Common\Controller;

use Think\Controller;
use Org\Util\Cookie;
use Common\Controller\ISecurityController;
use Common\Service\UserStatus;

/**
 * 需要登录的Controller基类
 * 
 * @author Lly
 *
 */
abstract class SignedInController extends BaseController
{        
	public function mustBeSignedIn()
	{
		return true;
	}
	
	public function skipAuthorization()
	{
		// 暂不做授权逻辑判断
		return true;
	}
	
	public function authenticate()
	{
		//TODO: 验证用户是否登录
		return ISecurityController::NOT_SIGNED_IN;
	}
	
	public function onSecurityError($issues)
	{
		// TODO: 登录验证失败逻辑
	}
}
