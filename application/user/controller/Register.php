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
use app\user\controller\Login;
use app\user\behavior\Pub;

class Register extends Controller{

	public function __construct(){
		if(Login::isUser()){
			$this->error('已登录','/user/index.html');
		};

	}

	public function index(){
		$userInfoArr = Request::instance()->post();
		if($userInfoArr){
			if(!empty($userInfoArr['username'])&&!empty($userInfoArr['password'])&&!empty($userInfoArr['password2'])&&!empty($userInfoArr['verify'])){

				if(!$userInfoArr['verify']){
					$this->error("请填写验证码");
				}
				if($userInfoArr['password']!==$userInfoArr['password2']){
					$this->error("两次密码不一致");
				}
				$login = new Login();
				if(!$login->verifyCheck($userInfoArr['verify'])){
					$this->error("验证码有误，请重新填写",'/user/register.html');
				}
				if(Db::name('user')->where('username',$userInfoArr['username'])->value('id')){
					$this->error("该用户名已存在",'/user/register.html');
				}
				$invitation = @$userInfoArr['inv'];
				$userArr = array(
					'username' => $userInfoArr['username'],
					'password' => password_hash($userInfoArr['password'],PASSWORD_DEFAULT),
					'rip' => Request::instance()->ip(),
					'rtime' => time(),
					'status' => 1,
					'usergroup' => 1,
					'invitation' => Strs::randString(6,'-1'),
				);

				$userInc = Db::name('user')->insert($userArr);
				if($userInc){
					$userId = Db::name('user')->getLastInsID();
					if($invitation){
						$this->invitationAdd($invitation,$userId,'5');
					}
					$this->success('注册成功','/user/login.html');
				}else{
					$this->error('注册失败','/user/register.html');
				}
			}else{
				$this->error("所有内容都不能为空");
			}
		}else{
			$view = new View();
			return $view->fetch();
		}

	}

	/**
	 * 邀请模块
	 * @param  [type] $invitation 邀请码
	 * @param  [type] $userId     注册ID
	 * @param  [type] $step       金币奖励
	 * @return [type]             [description]
	 */
	private function invitationAdd($invitation,$userId,$step){
		$invFind = Db::name('user')->where('invitation',$invitation)->find();
		if($invFind){
			$invUser = @$invFind['invitationuser'];
			if(!$invUser){
				$invArr = $userId;
			}else{
				$invArr = $invUser.','.$userId;
			}
			Db::name('user')->update(['invitationuser'=>$invArr,'id'=>$invFind['id']]);
			Db::name('user')->where('id',$invFind['id'])->setInc('money', $step);
			Pub::billSet($invFind['id'],$step,'邀请奖励',$type=2);
		}else{
			return false;
		}
	}
}