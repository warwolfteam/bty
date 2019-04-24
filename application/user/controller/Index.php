<?php
namespace app\user\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use app\util\Tools;
use app\user\controller\Login;
use app\user\behavior\Pub;
use app\util\Hashids;
use app\user\behavior\UserInfo;

class Index extends UserInfo{
	// private $UserInfo = '';

	// public function __construct(){
		
	// 	$this->UserInfo = Login::isUser();
	// 	if(!$this->UserInfo){
	// 		$this->error('未登录，请先登录','/user/login.html');
	// 	};
	// 	// 数字唯一加密
	// 	// $Hashids = new Hashids('wag', 4);
	// 	// $Hashids = new Hashids('yan');
	// 	// $Hashids = new Hashids();
	// 	// echo $Hashids->encode('123');
	// }

	public function index(){
		$user = $this->UserInfo;
		if(!$user){
			return $this->error('非法操作','/user/login.html');
		}
		//ajax方式修改个人信息
		if (Request::instance()->isAjax()){
			$userUp = Request::instance()->post();
			if(isset($userUp['email'])&&$userUp['email']){
				if(Db::name('user')->where('email',$userUp['email'])->find()){
					return Tools::buildFailed(-1,'该邮箱已被绑定');
				}
			}
			$userArr = array(
				'id' => @$user['id'],
				'username' => @$userUp['username'],
				'email' => @$userUp['email'],
				'qq' => @$userUp['qq'],
				'phone' => @$userUp['phone'],
			);
			if(isset($userUp['password'])&&$userUp['password']){
				$userArr['password'] = password_hash($userUp['password'],PASSWORD_DEFAULT);
				$userArr['token'] = '';
			}
			if(Db::name('user')->update($userArr)){
				return Tools::buildSuccess(200,'修改成功');
			}else{
				return Tools::buildFailed(-1,'修改失败');
			}
		}else{
			//输出个人信息
			$userInfo = $this->UserInfo;
			$userInfo['usergroup'] = Db::name('usergroup')->where('id',$userInfo['usergroup'])->value('name');
			$userInfo['rtime'] = date("Y-m-d",$userInfo['rtime']);
			$userInfo['ltime'] = date("Y-m-d",$userInfo['ltime']);
			$userInfo['invcount'] = $userInfo['invitationuser']?count(explode(',', $userInfo['invitationuser'])):'0';
			//输出个人已开通主机列表
			$hostList = Pub::hostList($user['id'],5);

			$view = new View();
			//输出个人账单列表
			$view->assign('billList',Pub::billList($user['id']));
			$view->UserHostCount = $hostList?@count($hostList):0;
			$view->Hostcount = $this->HostCount();
			$view->assign('hostList',$hostList);
			$view->assign('userInfo',$userInfo);
			$view->WebSite = Pub::get_webInfo();
			return $view->fetch();
		}
		
	}

	/**
	 * 主机管理免密登录
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function rootLogin($id){
		if(!preg_match("/^[1-9][0-9]*$/",$id)){
			return $this->error('非法请求');
		}
		$bthostInfo = Db::name('hostlist')
		->where('id',$id)
		->where('userid',$this->UserInfo['id'])
		->find();
		if(!$bthostInfo){
			return $this->error("登录失败");
		}
		$randToken = json_encode($bthostInfo);
		Session::set('vhostToken',$randToken);
		return $this->success("登录成功",'/vhost/index.html');
	}
}