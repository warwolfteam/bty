<?php 	
namespace app\user\behavior;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use app\util\Tools;
use app\user\controller\Login;


class UserInfo extends Controller{
	public $UserInfo = '';

	public function __construct(){
		
		$this->UserInfo = Login::isUser();
		if(!$this->UserInfo){
			$this->error('未登录，请先登录','/user/login.html');
		};
	}

	/**
	 * 获取用户有效服务器数量
	 */
	public function HostCount(){
		$countfind = Db::name('hostlist')->where('etime','>',time())->where('userid',$this->UserInfo['id'])->count();
		if($countfind){
			return $countfind;
		}else{
			return 0;
		}
	}

}