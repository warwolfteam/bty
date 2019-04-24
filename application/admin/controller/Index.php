<?php 
namespace app\admin\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Config;
use think\Request;
use think\Session;
use think\View;
use app\util\Tools;
use app\admin\controller\Login;

class Index extends Controller{
	private $UserInfo = '';

	public function __construct(){
		
		$this->UserInfo = Login::isUser();
		if(!$this->UserInfo){
			$this->error('未登录，请先登录','/admin/login.html');
		};
		//var_dump(Config::get('config.bty_version'));
	}

	public function index(){
		$userCount = Db::name('user')->count();
		$hostCount = Db::name('host')->count();
		$bthostCount = Db::name('hostlist')->count();
		$orderCount = Db::name('order')->count();
		$view = new View();

		$view->assign('userCount',$userCount ? $userCount : '');
		$view->assign('hostCount',$hostCount ? $hostCount : '');
		$view->assign('bthostCount',$bthostCount ? $bthostCount : '');
		$view->assign('orderCount',$orderCount ? $orderCount : '');
		
		$view->assign('userInfo',$this->UserInfo);
		return $view->fetch();
	}


}