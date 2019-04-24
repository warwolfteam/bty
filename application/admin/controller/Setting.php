<?php 
namespace app\admin\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use app\util\Tools;
use app\admin\controller\Login;

class Setting extends Controller{
	private $UserInfo = '';

	public function __construct(){
		
		$this->UserInfo = Login::isUser();
		if(!$this->UserInfo){
			$this->error('未登录，请先登录','/admin/login.html');
		};
	}

	public function index(){
		$adminInfo = Db::name('admin')->where('token',$this->UserInfo['token'])->find();
		if(!$adminInfo){
			$this->error('未登录，请先登录','/admin/login.html');
		}else{
			$webInfo = Db::name('webinfo')->where('id',1)->find();
			$smtpInfo = Db::name('smtp')->where('id',1)->find();
			$view = new View();
			$view->assign('adminInfo',$adminInfo);
			$view->assign('webInfo',$webInfo);
			$view->assign('smtpInfo',$smtpInfo);
		}
		$payInfo = Db::name('payconfig')->where('id',1)->find();
		$view->assign('payInfo',$payInfo);
		$view->assign('userInfo',$this->UserInfo);
		$view->paystatus = json_decode($payInfo['status'],1);
		//echo json_encode(array('alipay'=>1,'qqpay'=>1,'wxpay'=>1,'tenpay'=>1));
		return $view->fetch();
	}

	public function Upadmin(){
		$username = Request::instance()->post('username');
		$password = Request::instance()->post('password');
		$Iplist   = Request::instance()->post('Iplist');
		if($username==''&&$password==''){
			$this->error('非法请求');
			
		}
		if($password!==''){
			$userArr['password'] = password_hash($password,PASSWORD_DEFAULT);
			$userArr['token'] = '';
		}
		$userArr['username'] = $username;
		$userArr['Oip'] = $Iplist;
		$userUpdate = Db::name('admin')->where('token',$this->UserInfo['token'])->update($userArr);
		if($userUpdate){
			Session::clear();
			Cookie::clear();
			$this->success('修改成功','/admin/login.html');
		}else{
			$this->error('修改失败');
		}
		
	}

	public function UpwebInfo(){
		$webdomain = Request::instance()->post('webdomain');
		$webname = Request::instance()->post('webname');
		$webkey = Request::instance()->post('webkey');
		$webdes   = Request::instance()->post('webdes');
		if($webdomain!==null){
			$webArr['id'] = 1;
			$webArr['webdomain'] = $webdomain;
			$webArr['webname'] = $webname;
			$webArr['webkey'] = $webkey;
			$webArr['webdes'] = $webdes;

			$webUpdate = Db::name('webinfo')->update($webArr);
			if($webUpdate){
				$this->success('修改成功');
			}else{
				$this->error('修改失败');
			}
		}else{
			$this->error('站点域名不能为空');
		}
	}

	public function Upsmtp(){
		$host = Request::instance()->post('host');
		$port = Request::instance()->post('port');
		$fromname   = Request::instance()->post('fromname');
		$username   = Request::instance()->post('username');
		$password   = Request::instance()->post('password');

		$smtpArr['id'] = 1;
		$smtpArr['host'] = $host;
		$smtpArr['port'] = $port;
		$smtpArr['fromname'] = $fromname;
		$smtpArr['username'] = $username;
		$smtpArr['password'] = $password;
		$smtpUpdate = Db::name('smtp')->update($smtpArr);
		if($smtpUpdate){
			$this->success('修改成功');
		}else{
			$this->error('修改失败');
		}
	}

	public function Upepayinfo(){
		$payArr['partner'] = Request::instance()->post('partner');
		$payArr['key'] = Request::instance()->post('key');
		$payArr['apiurl'] = Request::instance()->post('apiurl');
		$payArr['notify_url'] = Request::instance()->post('notify_url');
		$payArr['return_url'] = Request::instance()->post('return_url');
		$payArr['ssl'] = Request::instance()->post('ssl')?1:0;

		$paycArr['alipay'] = Request::instance()->post('alipay')?1:0;
		$paycArr['qqpay'] = Request::instance()->post('qqpay')?1:0;
		$paycArr['wxpay'] = Request::instance()->post('wxpay')?1:0;
		$paycArr['tenpay'] = Request::instance()->post('tenpay')?1:0;

		$payArr['status'] = json_encode($paycArr);

		$payUpdate = Db::name('payconfig')->where('id',1)->update($payArr);
		if($payUpdate){
			$this->success('修改成功');
		}else{
			$this->error('修改失败');
		}
	}

}