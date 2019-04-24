<?php
namespace app\user\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use app\util\Tools;
use app\util\Captcha;

class Login extends Controller
{
	public function index()
	{
		if(Login::isUser()){
			$this->success('已登录','/user/index.html');
		};
		//$userUpdate = Db::name('admin')->where('id',1)->update(['password'=>password_hash('admin000',PASSWORD_DEFAULT)]);
		$username = Request::instance()->post('username');
		$password = Request::instance()->post('password');
		$verify = Request::instance()->post('verify');
		$ip = Request::instance()->ip();
		if ($username !== null) {
			if(!$verify){
				$this->error("请填写验证码");
			}
			if(!$this->verifyCheck($verify)){
				$this->error("验证码有误，请重新填写",'/user/login.html');
			}
			$find = Db::name('user')->where('username', $username)->find();
			$userGroup = Db::name('usergroup')->where('id', $find['usergroup'])->find();
			switch ($find['status']) {
				case '1':
					break;
				case '0':
					return $this->error('账号被禁用');
					break;
				default:
					# code...
					break;
			}
			if ($find == null) {
				$this->error("登录失败，账号不存在",'/user/login.html');
			}else {
				if (!password_verify($password, $find['password'])) {
					$this->error("登录失败，密码错误",'/user/login.html');
				} else {
					Session::set('USERGROUP',$userGroup);
					Session::set('USERINFO',$find);
					$randToken = Tools::userMd5($username.md5((int)(time()/7200)));

					Session::set('userToken',$randToken);
					//Cookie('userid', $randToken, 60 * 60 * 60 * 3);
					Db::name('user')->update(['ltime'=>time(),'id'=>$find['id'],'lip'=>$ip,'token'=>$randToken]);
					return $this->success("登录成功",'./index');
				}
			}
		} else {
			return view('index');
		}
	}


	/**
	 * 输出验证码
	 * @return [type] [description]
	 */
	public function verify()
	{
		$cap = new Captcha();
		return $cap->entry();
	}

	/**
	 * 退出登录
	 * @return [type] [description]
	 */
	public function logout()
	{
		Session::clear();
		Cookie::clear();
		$this->success("退出成功", '/index.html');
	}

	/**
	 * 验证码效验
	 * @param  [type] $verify [description]
	 * @return [type]         [description]
	 */
	public function verifyCheck($verify){
		$captcha = new Captcha();
		if($captcha->check($verify)){
			return true;
		}else{
			return false;
		};
	}


	public static function isUser(){
		$userToken = Session::get('userToken');
		if(!$userToken){
			return false;
		}else{
			$find = Db::name('user')->where('token',$userToken)->find();
			if($find['token']){
				if($userToken!=Tools::userMd5($find['username'].md5((int)(time()/7200)))){
					return false;
				}
				return $find;
			}else{
				return false;
			}
		}
	}
}
