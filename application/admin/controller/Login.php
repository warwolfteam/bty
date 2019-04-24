<?php
namespace app\admin\controller;

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
			$this->success('已登录','/admin/index.html');
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
				$this->error("验证码有误，请重新填写",'/admin/login.html');
			}
			$find = Db::name('admin')->where('username', $username)->find();
			if ($find == null) {
				$this->error("登录失败，账号不存在",'/admin/login.html');
			}
			if(!in_array('0.0.0.0',explode(',',$find['Oip']))){
				if(!in_array($ip,explode(',',$find['Oip']))){
					$this->error("非白名单IP");
				}
			}
			if (!password_verify($password, $find['password'])) {
				$this->error("登录失败，密码错误",'/admin/login.html');
			} else {
				$randToken = Tools::userMd5($username.time());
				Session::set('adminToken',$randToken);
				//Cookie('userid', $randToken, 60 * 60 * 60 * 3);
				Db::name('admin')->update(['Ltime'=>time(),'id'=>1,'Lip'=>$ip,'token'=>$randToken]);
				$this->success("登录成功",'/admin/index.html');
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
		$this->success("退出成功", '/admin/login.html');
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
		
		$userToken = Session::get('adminToken');
		if(!$userToken){
			return false;
		}else{
			$find = Db::name('admin')->where('token',$userToken)->find();
			if($find['token']){
				return $find;
			}else{
				return false;
			}
		}
	}
}
