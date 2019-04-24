<?php
namespace app\user\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use app\util\Tools;
use app\util\Strs;
use app\util\Smtp;
use app\util\Captcha;
use app\user\controller\Login;


class Retrieve extends Controller
{
	public function index(){
		if(Login::isUser()){
			$this->success('已登录','/user/index.html');
		};
		if($postEmail = Request::instance()->post('email')){
			$verify = Request::instance()->post('verify');
			if(!$verify){
				$this->error("请填写验证码");
			}
			$login = new Login();
			if(!$login->verifyCheck($verify)){
				$this->error("验证码有误，请重新填写",'/user/retrieve.html');
			}
			if($userFind = Db::name('user')->where('email',$postEmail)->find()){
				$setEmail = $this->setEmail($postEmail);
				if(!$setEmail){
					return $this->error('系统配置读取失败');
				}
				$sendemail = new Smtp($setEmail['host'],$setEmail['port'],true,$setEmail['username'], $setEmail['password'],1);
				$emailTitle = Session::get('webname').'系统账号,正在找回密码';

				$regtime = time();
				$token_exptime = time()+60*60*24;
				$email_token = md5(Strs::randString(8).$postEmail.$regtime);

				$emailmsg = "亲爱的" . $userFind['username']. "：<br/>您正在进行找回密码操作。<br/>请点击链接重新设置您的密码。<br/>
				<a href='http://".$_SERVER['SERVER_NAME']."/user/reset_pwd.html?reset_pwd_verify=" . $email_token . "' target='_blank'>http://".$_SERVER['SERVER_NAME']."/user/reset_pwd.html?reset_pwd_verify=" . $email_token . "</a>
				<br/>如果以上链接无法点击，请将它复制到你的浏览器地址栏中进入访问，该链接24小时内有效。<br/>如果此次密码找回请求非你本人所发，说明有人盯上你的账号了。<br/>
				";
				$result = $sendemail->send($userFind['email'],$setEmail['username'],$emailTitle,$emailmsg,$setEmail['fromname']);
				
				if($result){
					if($this->setSql($userFind['id'],$email_token,$token_exptime)){
						return $this->success('密码找回的邮件已发送到您的邮箱，请注意查收','/user/login.html');
					}else{
						return $this->error('数据读取失败');
					}
				}else{
					return $this->error('邮件发送失败');
				}
			}else{
				return $this->error('没有找到该邮件绑定的账号');
			}
		}
		$view = new View();
		return $view->fetch();
	}

	/**
	 * 获取发信邮箱配置参数
	 * @param [type] $email [description]
	 */
	private function setEmail($email){
		$getEmailConfig = Db::name('smtp')->where('id',1)->find();
		if($getEmailConfig){
			return $getEmailConfig;
		}else{
			return false;
		}
	}

	private function setSql($id,$Retrieve,$token_exptime){
		if(Db::name('user')->update(['id'=>$id,'Retrieve'=>$Retrieve,'token_exptime'=>$token_exptime])){
			return true;
		}else{
			return false;
		}
	}

	public function reset_pwd(){
		$reset_pwd_verify = Request::instance()->get('reset_pwd_verify');
		if(!$reset_pwd_verify){
			return $this->error('error');
		}
		$RetrieveFind =Db::name('user')->where('Retrieve',$reset_pwd_verify)->find();
		if(!$RetrieveFind){
			return $this->error('error');
		}
		if(time()>$RetrieveFind['token_exptime']){
			return $this->error('链接已失效');
		}
		$randPass = Strs::randString(8);
		$newPass = password_hash($randPass,PASSWORD_DEFAULT);
		$RetrieveUpdate = Db::name('user')->update(['id'=>$RetrieveFind['id'],'Retrieve'=>'','token_exptime'=>'','password'=>$newPass]);
		if($RetrieveUpdate){
			$view = new View();
			$view->newPass = $randPass;
			return $view->fetch();
		}else{
			return $this->error('数据读取错误');
		}
		
	}





}