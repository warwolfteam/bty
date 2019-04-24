<?php 
namespace app\admin\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use app\util\Tools;
use app\util\Strs;
use app\user\behavior\Pub;
use app\admin\controller\Login;

class User extends Controller{
	private $UserInfo = '';

	public function __construct(){
		
		$this->UserInfo = Login::isUser();
		if(!$this->UserInfo){
			$this->error('未登录，请先登录','/admin/login.html');
		};
	}

	public function index(){

		if (Request::instance()->isAjax()){
			$userId = Request::instance()->post('id');
			$reType = Request::instance()->post('type');
			if(!preg_match("/^[1-9][0-9]*$/",$userId)){
				return Tools::buildFailed(-1,'非法请求');
			}
			if($reType=='del'){
				if($this->delUser($userId)){
					return Tools::buildSuccess(200,'删除成功');
				}else{
					return Tools::buildFailed(-1,'删除失败');
				}
			}
			if($reType=='update'){
				$username = Request::instance()->post('username');
				$password = Request::instance()->post('password');
				if(isset($password)&&$password){
					$password = password_hash($password,PASSWORD_DEFAULT);
				}
				$email = Request::instance()->post('email');
				$money = Request::instance()->post('money');
				$usergroup = Request::instance()->post('usergroup');
				if($this->updateUser($userId,$username,$password,$email,$money,$usergroup)){
					return Tools::buildSuccess(200,'修改成功');
				}else{
					return Tools::buildFailed(-1,'修改失败');
				}
			}
			if($reType=='dis'){
				$dis = Request::instance()->post('dis');
				if($this->disUser($userId,$dis)){
					return Tools::buildSuccess(200,'修改成功');
				}else{
					return Tools::buildFailed(-1,'修改失败');
				}
			}
			if($reType=='inc'){
				$username = Request::instance()->post('username');
				$password = Request::instance()->post('password');
				$email = Request::instance()->post('email');
				$usergroup = Request::instance()->post('usergroup');
				if($this->incUser($username,$password,$email,$usergroup)){
					return Tools::buildSuccess(200,'新增成功');
				}else{
					return Tools::buildFailed(-1,'新增失败');
				}
			}
		}else{
			$searchValue = Request::instance()->get('search');
			//http://localhost/admin/user.html?1&search=ad
			return $this->userList($searchValue);
		}

	}

	public function info($id=0){

		if(!preg_match("/^[1-9][0-9]*$/",$id)){
			return Tools::buildFailed(-1,'非法请求');
		}

		$userInfo = Db::name('user')->where('id',$id)->find();

		if (Request::instance()->isAjax()){
			$userUp = Request::instance()->post();
			if(!$userUp){
				return Tools::buildFailed(-1,'信息为空');
			}
			$userArr = array(
				'id' => @$userInfo['id'],
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
		}
		$userInfo['rtime'] = date("Y-m-d",$userInfo['rtime']);
		$userInfo['ltime'] = date("Y-m-d",$userInfo['ltime']);
		$userInfo['invcount'] = $userInfo['invitationuser']?count(explode(',', $userInfo['invitationuser'])):'0';

		$hostList = Db::name('hostlist')->where('status',1)->where('userid',$id)->select();

		$view = new View();
		$view->assign('billList',$this->billList($id));
		$view->assign('hostList',$hostList);
		$view->UserHostCount = count($hostList);
		$view->assign('userInfo',$userInfo);
		$view->WebSite = Pub::get_webInfo();
		return $view->fetch();
	}

	private function userList($search=''){
		$pageCount = Db::name('user')->where('username','like',$search.'%')->count();

		$pageStep = 10;	 //页数步长
		$pageNum = ceil($pageCount/$pageStep);	
		if(preg_match("/^[1-9][0-9]*$/",key(input('get.')))){
			$page = key(input('get.'));
		}else{
			$page = 1;
		}
		$fy = ($page-1) * $pageStep;	//起始数量

		$userList = Db::name('user')->where('username','like',$search.'%')->limit($fy,$pageStep)->order('id desc')->select();
		foreach ($userList as $key => $value) {
			$group = Db::name('usergroup')->where('id',$userList[$key]['usergroup'])->find();
			$userList[$key]['usergroupid'] = $group['id'];
			$userList[$key]['usergroup'] = $group['name'];
			$userList[$key]['ltime'] = date("Y/m/d H:i:s",$userList[$key]['ltime']);
		}
		
		$usergroup = Db::name('usergroup')->select();
		
		$view = new View();


		$view->pageNum = Tools::multipage($pageNum,$page,$pageStep,'&search='.$search);


		$view->assign('userList',$userList);
		$view->assign('usergroup',$usergroup);
		$view->assign('userInfo',$this->UserInfo);
		return $view->fetch();
	}

	/**
	 * 删除用户
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	private function delUser($id){
		$delUser = Db::name('user')->where('id',$id)->delete();
		if($delUser){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 修改用户状态
	 * @param  [type] $id     [description]
	 * @param  [type] $status [description]
	 * @return [type]         [description]
	 */
	private function disUser($id,$status){
		$disUser = Db::name('user')->update(['status' => $status,'id'=>$id]);
		if($disUser){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 新增用户
	 * @param  [type] $username  [description]
	 * @param  [type] $password  [description]
	 * @param  [type] $email     [description]
	 * @param  [type] $usergroup [description]
	 * @return [type]            [description]
	 */
	private function incUser($username,$password,$email,$usergroup){
		$userArr['username']=$username;
		$userArr['password']=password_hash($password,PASSWORD_DEFAULT);
		$userArr['email']=$email;
		$userArr['rtime']=time();
		$userArr['rip']=Request::instance()->ip();
		$userArr['usergroup']=$usergroup;
		$userArr['status']=1;
		$userArr['invitation']=Strs::randString(6,'-1');
		$incUser = Db::name('user')->insert($userArr);
		if($incUser){
			return true;
		}else{
			return false;
		}

	}

	/**
	 * 更新用户信息
	 * @param  [type] $id        [description]
	 * @param  [type] $username  [description]
	 * @param  [type] $password  [description]
	 * @param  [type] $email     [description]
	 * @param  [type] $money     [description]
	 * @param  [type] $usergroup [description]
	 * @return [type]            [description]
	 */
	private function updateUser($id,$username,$password,$email,$money,$usergroup){
		
		if($password){
			$userArr['password']=$password;
		}
		$userArr['id']=$id;
		$userArr['username']=$username;
		
		$userArr['email']=$email;
		$userArr['money']=$money;
		$userArr['usergroup']=$usergroup;
		$upUser = Db::name('user')->update($userArr);
		if($upUser){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 账单列表
	 * @param  [type] $userid [description]
	 * @return [type]         [description]
	 */
	private function billList($userid){
		$billFind = Db::name('bill')->where('userid',$userid)->select();
		foreach ($billFind as $key => $value) {
			$billFind[$key]['ctime'] = date("Y-m-d H:i:s",$billFind[$key]['ctime']);
		}
		return $billFind;
	}
}