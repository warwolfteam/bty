<?php
namespace app\vhost\controller;

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
		$username = Request::instance()->post('username');
		$password = Request::instance()->post('password');
		$verify = Request::instance()->post('verify');
		$ip = Request::instance()->ip();
		if ($username !== null) {
			if(!$verify){
				$this->error("请填写验证码");
			}
			if(!$this->verifyCheck($verify)){
				$this->error("验证码有误，请重新填写",'/vhost/login.html');
			}
			$find = Db::name('hostlist')->where('username', $username)->find();
			if ($find == null) {
				$this->error("登录失败，账号不存在",'/vhost/login.html');
			}else {
				if ($password!==$find['password']) {
					$this->error("登录失败，密码错误",'/vhost/login.html');
				} else {
					$randToken = json_encode($find);
					Session::set('vhostToken',$randToken);
					$this->success("登录成功",'./index');
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
		Session::set('vhostToken',null);
		$this->success("退出成功", '/vhost/login.html');
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

	public static function isLogin(){
		
		$vhostToken = Session::get('vhostToken');
		if(!$vhostToken){
			return false;
		}else{
			if(Db::name('hostlist')->where('id',json_decode($vhostToken,1)['id'])->find()){
				return json_decode($vhostToken,1);
			}else{
				return false;
			}
			
		}
	}
}
