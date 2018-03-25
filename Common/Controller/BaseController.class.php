<?php

namespace Common\Controller;

//use Common\Model\ManagesModel;
use Think\Controller;
use Org\Util\Cookie;
use Common\Controller\ISecurityController;
use Common\Common\Tables;

abstract class BaseController extends Controller implements ISecurityController
{	
	protected function _initialize()
	{
		//$menuConfig = include ROOT.'/Common/Conf/menu.php';
        //$this->main_menu = $menuConfig['main'];
	    //$this->menu = $menuConfig['menu'];
		$this->ensureSecurity();
	}
		
	protected function assignPageHeader($title, $keyword)
	{
		$this->assign('title', $title)
			 ->assign('keywords', $keyword);
	}
	
	protected function set_secure_cookie($name, $value = null, $options = null)
	{
		cookie_encrypt($name, $value, $options);
	}
	
	protected  function ensureSecurity()
	{
		$issue = true;
		if ($this->mustBeSignedIn()) {
			$issue = $this->authenticate();
			if (true === $issue) {
				if (!$this->skipAuthorization()) {
					$issue = $this->authorize();
				}
			}
		}
		if (true !== $issue) {
			$this->onSecurityError($issue);
		}
	}
	
	public function mustBeSignedIn()
	{
		return false;
	}
	
	public function authenticate()
	{
		return true;
	}
	
	public function skipAuthorization()
	{
		return true;
	}
	
	public function authorize()
	{	
		return true;
	}
	
	public function onSecurityError($issues)
	{
		throw new \Exception('Not implement');
	}
	
	/**
	 * 格式货为标准数据
	 * @param array  $array 原始数组
	 * @param string $kkey 数组索引
	 * @param string $kval 索引对应值
	 * @return array(array($key=>$val),array($key=>$val))
	 */
	public function getArrayCol(array $array,$kkey,$kval)
	{
	    $newAry = array();
	    if(count($array) != count($array,1)){
	        foreach($array as $k=>$v){
	            $newAry[$v[$kkey]] = $v[$kval];
	        }
	        return $newAry;
	    }
	    return $array;
	}
	
	/**
	 * 移除条件中空参数
	 * @param array $where
	 */
	public function shiftEmpty(array $where){
	    foreach($where as $k=>$v){
	        if(!is_array($v[1]) && !$v[1] ){
	            unset($where[$k]);
	        }
	    }
        return $where;
	}
	
	/**
	 *   判断是否是预约管理管
	 */
	public function isSuperName($name){
        $manageModel = new ManagesModel();
        $managers = $manageModel->getBespokeAdmins();
        $superNames = array_column($managers, 'account');
	    return in_array($name, $superNames);
	}
	
	/**
	 * 获取shopId
	 * @return number
	 */
	protected function getShopId() {
		return getShopId();
	}
	
	protected function get_shops_by_loc($limit = 10){
		$area = getlocation(get_real_ip()); //Temporary
		$where = [];
		
		$model = M(Tables::BASE_REGION);	
		if (!empty($area['city'])) {
			$where['_string'] = 'region_type = 2 AND LOCATE(\''.$area['city']. '\', region_name) = 1';
		} 
		
		if (!empty($area['province'])) { 
			$cond = 'region_type = 1 and LOCATE(\''.$area['province']. '\', region_name) = 1';
			$where['_string'] = empty($where['_string']) ? $cond : '('. $where['_string'].') or ('.$cond.')';
		}
		if (empty($where)) return [];
		
		$data = $model->where($where)->select();
		//var_dump($data);
		$where = [];
		foreach ($data as $d) {
			if ($d['region_type'] == 1) {
				$where['province_id'] = $d['region_id'];
			} else if ($d['region_type'] == 2) {
				$where['city_id'] = $d['region_id'];
			}
		}

		$resp = M(Tables::BASE_SHOP_CFG)->where($where)->order('`order` asc')->page('1,'.$limit)->select();
		return empty($resp) ? [] : $resp;
	}
	
	public function rspsJSON($result, $message='', $data=array())
	{
		$rsps = array('result'=>$result, 'msg'=>$message, 'data'=>$data); 
		$this->ajaxReturn($rsps, 'JSON');
	}

	public function _empty(){
		header("http/1.1 301 moved permanently");
		//header("location:http://" . C("OLD_SITE_DOMAIN") . get_url_endfix());
		header("location:http://" . C("OLD_SITE_DOMAIN"));
	}
	
	/**
     * 权限检测
     * @param string  $rule    检测的规则
     * @param string  $mode    check模式
     * @return boolean
     */
    final protected function checkRule($rule, $type=AuthRuleModel::RULE_URL, $mode='url'){
        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }
        static $Auth    =   null;
        if (!$Auth) {
            $Auth       =   new \Think\Auth();
        }
        if(!$Auth->check($rule,$_SESSION['admin_profile']['id'],$type,$mode)){
            return false;
        }
        return true;
    }
	/**
     * 检测是否是需要动态判断的权限
     * @return boolean|null
     *      返回true则表示当前访问有权限
     *      返回false则表示当前访问无权限
     *      返回null，则会进入checkRule根据节点授权判断权限
     *
     */
    protected function checkDynamic(){
        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }
        return null;//不明,需checkRule
    }
    /**
     * action访问控制,在 **登陆成功** 后执行的第一项权限检测任务
     *
     * @return boolean|null  返回值必须使用 `===` 进行判断
     *
     *   返回 **false**, 不允许任何人访问(超管除外)
     *   返回 **true**, 允许任何管理员访问,无需执行节点权限检测
     *   返回 **null**, 需要继续执行节点权限检测决定是否允许访问
     */
    final protected function accessControl(){
        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }
		$allow = C('ALLOW_VISIT');
		$deny  = C('DENY_VISIT');
		$check = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
        if ( !empty($deny)  && in_array_case($check,$deny) ) {
            return false;//非超管禁止访问deny中的方法
        }
        if ( !empty($allow) && in_array_case($check,$allow) ) {
            return true;
        }
        return null;//需要检测节点权限
    }
	 /**
     * 公用多图上传
     */
    public function uploadMultiImg(){
        $upload = new \Think\Upload(); // 实例化上传类
	    $upload->maxSize   =     3145728 ;// 设置附件上传大小
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	    $upload->rootPath  =      './Public/upload/common/'; // 设置附件上传根目录
	    $upload->savePath  =      ''; // 设置附件上传（子）目录
	    // 上传文件 
	    $info   =   $upload->upload();
	    if(!$info) {// 上传错误提示错误信息
	    	echo $upload->getError();
	      	exit;
	    }else{// 上传成功 获取上传文件信息
	        foreach($info as $file){
	        	echo '<img src="/Public/upload/common/'.$file['savepath'].$file['savename'].'"  class="preview">';
	        }
	    }
    }
}
