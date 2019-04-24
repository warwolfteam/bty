<?php 
namespace app\vhost\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use app\util\Tools;
use app\vhost\controller\Login;
use app\bt\Bt;


class Index extends Controller{
	public $USERINFO = '';
	public $HOSTINFO = '';
	public $VHOST = '';

	public $BTURL = '';
	public $BTOKEN = '';

	public function __construct(){
		
		$this->VHOST = Login::isLogin();
		if(!$this->VHOST){
			$this->error('未登录，请先登录','/vhost/login.html');
		};
		$this->HOSTINFO = Db::name('host')->where('id',$this->VHOST['hostid'])->find();

		$this->BTURL = $this->HOSTINFO['bturl'];
		$this->BTOKEN = $this->HOSTINFO['btoken'];

		$this->USERINFO = Db::name('user')->where('id',$this->VHOST['userid'])->find();
		$this->USERINFO['usergroup'] = Db::name('usergroup')->where('id',$this->USERINFO['usergroup'])->value('name');
	}

	public function index(){
		if(!$this->HOSTINFO){
			return $this->error('该主机不存在，请联系管理员','/user/order.html');
		}
		// 获取该服务器已经安装的PHP版本
		$phpversion_list = $this->phpversion_list($this->HOSTINFO['id']);
		

		$Websites = @$this->getWebSataus()['data'][0];
		if(!$Websites){
			return $this->error('主机不存在，请联系管理员','/user/order.html');
		}

		//获取当前网站的PHP版本
		$getSitePHPVer = @$this->GetSitePHPVersion()['phpversion'];



		$view = new View();
		$view->sqlurl = $this->HOSTINFO['sqlurl'];
		$view->assign('USERINFO',$this->USERINFO);
		$view->assign('WEBINFO',$Websites);
		$view->assign('btHostInfo',$this->VHOST);
		$view->assign('phpversion_list',$phpversion_list);
		$view->assign('getSitePHPVer',$getSitePHPVer);
		$view->assign('hostInfo',$this->HOSTINFO);

		
		return $view->fetch();
	}


	private function phpversion_list($hostid){
		$bt = new Bt($this->BTURL,$this->BTOKEN);
		return $bt->GetPHPVersion();
	}

	/**
	 * 修改网站运行的php版本
	 * @return [type] [description]
	 */
	public function phpSet(){
		$phpVer = Request::instance()->post('v');
		$bt = new Bt($this->BTURL,$this->BTOKEN);
		$setPHP = $bt->SetPHPVersion($this->VHOST['domain'],$phpVer);
		if(isset($setPHP['status'])&&$setPHP['status']=='true'){
			return $this->success($setPHP['msg'],'/vhost/index.html');
		}else{
			return $this->error('修改失败，出现意外情况：'.$setPHP['msg']);
		}
	}

	/**
	 * 获取PHP版本列表
	 */
	private function GetSitePHPVersion(){
		$bt = new Bt($this->BTURL,$this->BTOKEN);
		$getPHP = $bt->GetSitePHPVersion($this->VHOST['domain']);
		return $getPHP;
	}

	/**
	 * 网站停止运行
	 */
	public function WebSiteStop(){
		$bt = new Bt($this->BTURL,$this->BTOKEN);
		$stop = $bt->WebSiteStop($this->VHOST['btid'],$this->VHOST['domain']);
		if($stop&&$stop['status']=='true'){
			Db::name('hostlist')->where('id',$this->VHOST['id'])->update(['status'=>0]);
			return Tools::buildSuccess(200,$stop['msg']);
		}else{
			return Tools::buildFailed('-1','请求失败：'.$stop['msg']);
		}
	}

	/**
	 * 网站开启
	 */
	public function WebSiteStart(){
		$bt = new Bt($this->BTURL,$this->BTOKEN);
		$stop = $bt->WebSiteStart($this->VHOST['btid'],$this->VHOST['domain']);
		if($stop&&$stop['status']=='true'){
			Db::name('hostlist')->where('id',$this->VHOST['id'])->update(['status'=>1]);
			return Tools::buildSuccess(200,$stop['msg']);
		}else{
			return Tools::buildFailed('-1','请求失败：'.$stop['msg']);
		}
	}

	public function getWebSataus(){
		$bt = new Bt($this->BTURL,$this->BTOKEN);
		return $webstatus = $bt->Websites($this->VHOST['domain']);
	}


	/**
	 * 绑定域名
	 * @return [type] [description]
	 */
	public function domain(){
		$bt = new Bt($this->BTURL,$this->BTOKEN);
		if($post_str = Request::instance()->post()){
			if(!empty($post_str['domain'])){
				if($post_str['dirs'] == '/'){
					$modify_status = $bt->WebAddDomain($this->VHOST['btid'],$this->VHOST['domain'],$post_str['domain']);
				}else{
					$modify_status = $bt->AddDirBinding($this->VHOST['btid'],$post_str['domain'],$post_str['dirs']);
					
				}
				
				if(isset($modify_status)&&$modify_status['status']=='true'){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}else{
				return $this->error('域名不能为空');
			}
		}elseif ($get_str = Request::instance()->get()) {
			if(isset($get_str['delete'])&&$get_str['delete']!=''){
				if($modify_status = $bt->WebDelDomain($this->VHOST['btid'],$this->VHOST['domain'],$get_str['delete'],80)){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}
			if(isset($get_str['deletedir'])&&$get_str['deletedir']!=''){
				if($modify_status = $bt->DelDirBinding($get_str['deletedir'])){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}
		}else{
			//获取域名绑定列表
			$domainList = $bt->WebDoaminList($this->VHOST['btid']);
			//获取子目录绑定信息
			$dirList = $bt->GetDirBinding($this->VHOST['btid']);

			$view = new View();
			$view->assign('dirList',$dirList);
			$view->assign('domainList',$domainList);
			$view->assign('USERINFO',$this->USERINFO);
			$view->assign('btHostInfo',$this->VHOST);
			$view->assign('hostInfo',$this->HOSTINFO);

			return $view->fetch();
		}
	}


	/**
	 * 更新控制面板密码
	 * @return [type] [description]
	 */
	public function pass(){

		if($post_str = Request::instance()->post()){
			if(!empty($post_str['password'])&&!empty($post_str['username'])){
				$modify_status = $this->userUp($this->VHOST['id'],$post_str['username'],$post_str['password']);
				if(isset($modify_status)&&$modify_status){
					Session::set('vhostToken',null);
					return $this->success('修改成功','/vhost/login.html');
				}else{
					return $this->error('修改失败');
				}
			}else{
				return $this->error('账号密码不能为空');
			}
		}else{

			$view = new View();

			$view->assign('USERINFO',$this->USERINFO);
			$view->assign('btHostInfo',$this->VHOST);
			$view->assign('hostInfo',$this->HOSTINFO);

			return $view->fetch();
		}
	}

	/**
	 * 更新数据库中的用户信息
	 * @param  [type] $username 用户名
	 * @param  [type] $password  密码
	 * @return [type]          bool
	 */
	private function userUp($bthostid,$username,$password){
		if(Db::name('hostlist')->where('id',$bthostid)->update(['username'=>$username,'password'=>$password])){
			return true;
		}else{
			return false;
		}
	}


	/**
	 * 带宽限制
	 */
	public function Speed(){
		
		$bt = new Bt($this->BTURL,$this->BTOKEN);
		if($post_str = Request::instance()->post()){
			if(!empty($post_str['perserver'])&&!empty($post_str['perip'])&&!empty($post_str['limit_rate'])){
				$modify_status = $bt->SetLimitNet($this->VHOST['btid'],$post_str['perserver'],$post_str['perip'],$post_str['limit_rate']);
				if(isset($modify_status)&&$modify_status['status']=='true'){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}else{
				return $this->error('都不能为空');
			}
		}elseif ($get_str = Request::instance()->get()) {
			if(isset($get_str['speed'])&&$get_str['speed']=='off'){
				if($modify_status = $bt->CloseLimitNet($this->VHOST['btid'])){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}
		}else{
			$netInfo = $bt->GetLimitNet($this->VHOST['btid']);
			if(empty($netInfo['limit_rate'])&&empty($netInfo['perip'])&&empty($netInfo['perserver'])){
				$netInfo['status'] = false;
			}else{
				$netInfo['status'] = true;
			}
			$view = new View();
			$view->assign('netInfo',$netInfo);
			$view->assign('USERINFO',$this->USERINFO);
			$view->assign('btHostInfo',$this->VHOST);
			$view->assign('hostInfo',$this->HOSTINFO);

			return $view->fetch();
		}
	}


	/**
	 * 设置网站默认文件
	 * @return [type] [description]
	 */
	public function File(){
		
		$bt = new Bt($this->BTURL,$this->BTOKEN);
		if($post_str = Request::instance()->post()){
			if(!empty($post_str['Dindex'])){
				$modify_status = $bt->WebSetIndex($this->VHOST['btid'],$post_str['Dindex']);
				if(isset($modify_status)&&$modify_status['status']=='true'){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}else{
				return $this->error('不能为空');
			}
		}else{
			$indexFile = $bt->WebGetIndex($this->VHOST['btid']);

			$view = new View();
			$view->assign('indexfile',$indexFile);
			$view->assign('USERINFO',$this->USERINFO);
			$view->assign('btHostInfo',$this->VHOST);
			$view->assign('hostInfo',$this->HOSTINFO);

			return $view->fetch();
		}
	}

	/**
	 * 网站301跳转
	 */
	public function Rewrite301(){

		$bt = new Bt($this->BTURL,$this->BTOKEN);
		if($post_str = Request::instance()->post()){
			if(!empty($post_str['domains'])&&!empty($post_str['toUrl'])){
				$modify_status = $bt->Set301Status($this->VHOST['domain'],$post_str['toUrl'],$post_str['domains'],1);
				if(isset($modify_status)&&$modify_status['status']=='true'){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}else{
				return $this->error('不能为空');
			}
		}elseif ($get_str = Request::instance()->get()) {
			if(isset($get_str['rewrite'])&&$get_str['rewrite']=='off'){
				if($modify_status = $bt->Set301Status($this->VHOST['domain'],'http://baidu.cpom$request_uri','all',0)){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}
		}else{
			$rewriteInfo = $bt->Get301Status($this->VHOST['domain']);
			$rewriteInfo['domain'] = explode(',',$rewriteInfo['domain']);

			$view = new View();
			$view->assign('rewriteInfo',$rewriteInfo);
			$view->assign('USERINFO',$this->USERINFO);
			$view->assign('btHostInfo',$this->VHOST);
			$view->assign('hostInfo',$this->HOSTINFO);

			return $view->fetch();
		}
	}	

	/**
	 * 网站伪静态规则设置
	 * @return [type] [description]
	 */
	public function rewrite(){

		$bt = new Bt($this->BTURL,$this->BTOKEN);
		if($post_str = Request::instance()->post()){
			
			if(isset($post_str['rewrite'])&&!empty($post_str['submit'])){
				if($post_str['dirdomain']=='/'){
					$modify_status = $bt->SaveFileBody($this->VHOST['domain'],$post_str['rewrite'],'utf-8');
				}else{

					$bt->GetDirRewrite($post_str['dirdomain'],1);
					$GetDirRewrite = $bt->GetDirRewrite($post_str['dirdomain']);
					if(!$GetDirRewrite||$GetDirRewrite['status']!='true'){
						return $this->error('设置失败：'.@$GetDirRewrite['msg']);
					}else{
						$dir_path = $GetDirRewrite['filename'];
					}
					$modify_status = $bt->SaveFileBody($dir_path,$post_str['rewrite'],'utf-8',1);
				}
				if(isset($modify_status)&&$modify_status['status']=='true'){
					return $this->success(@$modify_status['msg']);
				}else{
					return $this->error('设置失败：'.@$modify_status['msg']);
				}
				exit();
			}
			if(isset($post_str['rewrites'])&&!empty($post_str['rewrites'])){
				if($post_str['rewrites']=='0.当前'){
					$rewrite = $this->VHOST['domain'];
					$type = 1;
				}else{
					$rewrite = $post_str['rewrites'];
					$type = 0;
				}
				if($post_str['dirdomain']=='/'){
					$modify_status = $bt->GetFileBody($rewrite,$type);
				}else{
					if($post_str['rewrites']=='0.当前'){
						$modify_status = $bt->GetDirRewrite($post_str['dirdomain']);
						
					}else{
						$modify_status = $bt->GetFileBody($rewrite,$type);
						
					}
				}
				if(isset($modify_status)&&$modify_status['status']=='true'){
					return Tools::buildSuccess(200,'请求成功',@$modify_status['data']);
				}else{
					return Tools::buildFailed('-1','请求失败：'.@$modify_status['msg']);
				}
				exit();

			}
		}else{
			//获取内置伪静态规则名
			$rewriteList = $bt->GetRewriteList($this->VHOST['domain']);
			//获取当前网站伪静态规则
			$rewriteInfo = $bt->GetFileBody($this->VHOST['domain'],1);
			//获取子目录绑定信息
			$dirList = $bt->GetDirBinding($this->VHOST['btid']);


			$view = new View();
			$view->assign('dirList',$dirList);
			$view->assign('rewriteList',$rewriteList);
			$view->assign('rewriteInfo',$rewriteInfo);
			$view->assign('USERINFO',$this->USERINFO);
			//$view->assign('btHostInfo',$this->VHOST);
			//$view->assign('hostInfo',$this->HOSTINFO);

			return $view->fetch();
		}
	}


	/**
	 * 网站备份
	 * @return [type] [description]
	 */
	public function Siteback(){

		$bt = new Bt($this->BTURL,$this->BTOKEN);
		$WebBackupList = $bt->WebBackupList($this->VHOST['btid']);

		$securityArray = [];
		foreach ($WebBackupList['data'] as $key => $value) {
			$securityArray[$key] = $WebBackupList['data'][$key]['id'];
		}
		if($post_str = Request::instance()->post()){
			// if(!empty($post_str['username'])&&!empty($post_str['password'])){
			// 	$modify_status = $bt->SetHasPwd($this->VHOST['btid'],$post_str['username'],$post_str['password']);
			// 	if(isset($modify_status)&&$modify_status['status']=='true'){
			// 		return $this->success($modify_status['msg']);
			// 	}else{
			// 		return $this->error('设置失败：'.$modify_status['msg']);
			// 	}
			// }else{
			// 	return $this->error('账号或密码为空');
			// }
		}elseif ($get_str = Request::instance()->get()) {
			if(isset($get_str['del'])&&$get_str['del']!=''){
				if(in_array($get_str['del'], $securityArray)){
					if($modify_status = $bt->WebDelBackup($get_str['del'])){
						return $this->success($modify_status['msg']);
					}else{
						return $this->error('删除失败：'.$modify_status['msg']);
					}
				}else{
					return $this->error('非法操作');
				}
			}
			if(isset($get_str['down'])&&$get_str['down']!=''){
				if(in_array($get_str['down'], $securityArray)){
					return $this->error('该功能正在构架中');
				}else{
					return $this->error('非法操作');
				}
			}

			if(isset($get_str['to'])&&$get_str['to']=='back'){
				if(count($WebBackupList['data'])<5){
					if($modify_status = $bt->WebToBackup($this->VHOST['btid'])){
						return $this->success($modify_status['msg']);
					}else{
						return $this->error('备份失败：'.$modify_status['msg']);
					}
				}else{
					return $this->error('仅支持备份五个，请删除后重新备份');
				}
				
			}
		}else{
			$view = new View();
			if(isset($WebBackupList['data'][0])){
				foreach ($WebBackupList['data'] as $key => $value) {
					$WebBackupList['data'][$key]['size'] = Tools::formatBytes($WebBackupList['data'][$key]['size']);
				}
			}
			$view->assign('countback',count(@$WebBackupList['data']));
			$view->assign('WebBackupList',$WebBackupList);
			$view->assign('USERINFO',$this->USERINFO);
			$view->assign('btHostInfo',$this->VHOST);
			$view->assign('hostInfo',$this->HOSTINFO);

			return $view->fetch();
		}
	}

	/**
	 * 网站FTP设置
	 */
	public function Ftp(){

		$bt = new Bt($this->BTURL,$this->BTOKEN);
		$ftpInfo = $bt->WebFtpList($this->VHOST['ftpname']);
		if(!$ftpInfo||!isset($ftpInfo['data']['0'])){
			return $this->error('模块发生错误，请联系管理员');
		}
		if($post_str = Request::instance()->post()){
			if(!empty($post_str['password'])){
				$modify_status = $bt->SetUserPassword($ftpInfo['data'][0]['id'],$ftpInfo['data'][0]['name'],$post_str['password']);
				if(isset($modify_status)&&$modify_status['status']=='true'){
					if(Db::name('hostlist')->where('ftpname',$ftpInfo['data'][0]['name'])->update(['ftpkey'=>$post_str['password']])){
						return $this->success($modify_status['msg']);
					}else{
						return $this->error('设置失败：'.$modify_status['msg'].'密码更新失败');
					}
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}else{
				return $this->error('密码不能为空');
			}
		}elseif ($get_str = Request::instance()->get()) {
			if(isset($get_str['ftp'])&&$get_str['ftp']=='off'){
				if($modify_status = $bt->SetStatus($ftpInfo['data'][0]['id'],$ftpInfo['data'][0]['name'],0)){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}
			if(isset($get_str['ftp'])&&$get_str['ftp']=='on'){
				if($modify_status = $bt->SetStatus($ftpInfo['data'][0]['id'],$ftpInfo['data'][0]['name'],1)){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}
		}else{
			$ftp = @$ftpInfo['data'][0];

			$view = new View();

			$view->assign('ftp',$ftp);
			$view->assign('USERINFO',$this->USERINFO);
			// $view->assign('btHostInfo',$this->VHOST);
			// $view->assign('hostInfo',$this->HOSTINFO);

			return $view->fetch();
		}
	}

	/**
	 * 网站SQL设置
	 */
	public function Sql(){

		$bt = new Bt($this->BTURL,$this->BTOKEN);
		$sqlInfo = $bt->WebSqlList($this->VHOST['sqlname']);
		if(!$sqlInfo||!isset($sqlInfo['data']['0'])){
			return $this->error('模块发生错误，请联系管理员');
		}
		if($post_str = Request::instance()->post()){
			if(!empty($post_str['password'])){
				$modify_status = $bt->ResDatabasePass($sqlInfo['data'][0]['id'],$sqlInfo['data'][0]['name'],$post_str['password']);
				if(isset($modify_status)&&$modify_status['status']=='true'){
					if(Db::name('hostlist')->where('sqlname',$sqlInfo['data'][0]['name'])->update(['sqlkey'=>$post_str['password']])){
						return $this->success($modify_status['msg']);
					}else{
						return $this->error('设置失败：'.$modify_status['msg'].'密码更新失败');
					}
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}else{
				return $this->error('密码不能为空');
			}
		}else{
			$sql = @$sqlInfo['data'][0];



			$view = new View();

			$view->assign('sql',$sql);
			$view->assign('USERINFO',$this->USERINFO);
			// $view->assign('btHostInfo',$this->VHOST);
			// $view->assign('hostInfo',$this->HOSTINFO);

			return $view->fetch();
		}
	}

	/**
	 * 数据库备份
	 */
	public function Sqlback(){

		$bt = new Bt($this->BTURL,$this->BTOKEN);
		//获取数据库ID
		$WebSqlList = $bt->WebSqlList($this->VHOST['sqlname']);
		if(!$WebSqlList||!isset($WebSqlList['data'][0])){
			return $this->error('模块发生错误，请联系管理员');
		}
		//获取数据库备份列表
		$WebBackupList = $bt->WebBackupList($WebSqlList['data'][0]['id'],'1','5','1');

		$securityArray = [];
		foreach ($WebBackupList['data'] as $key => $value) {
			$securityArray[$key] = $WebBackupList['data'][$key]['id'];
		}
		if($post_str = Request::instance()->post()){
			// if(!empty($post_str['username'])&&!empty($post_str['password'])){
			// 	$modify_status = $bt->SetHasPwd($this->VHOST['btid'],$post_str['username'],$post_str['password']);
			// 	if(isset($modify_status)&&$modify_status['status']=='true'){
			// 		return $this->success($modify_status['msg']);
			// 	}else{
			// 		return $this->error('设置失败：'.$modify_status['msg']);
			// 	}
			// }else{
			// 	return $this->error('账号或密码为空');
			// }
		}elseif ($get_str = Request::instance()->get()) {
			if(isset($get_str['del'])&&$get_str['del']!=''){
				if(in_array($get_str['del'], $securityArray)){
					if($modify_status = $bt->SQLDelBackup($get_str['del'])){
						return $this->success($modify_status['msg']);
					}else{
						return $this->error('删除失败：'.$modify_status['msg']);
					}
				}else{
					return $this->error('非法操作');
				}
				
			}
			if(isset($get_str['to'])&&$get_str['to']=='back'){
				if(count($WebBackupList['data'])<5){
					if($modify_status = $bt->SQLToBackup($WebSqlList['data'][0]['id'])){
						return $this->success($modify_status['msg']);
					}else{
						return $this->error('备份失败：'.$modify_status['msg']);
					}
				}else{
					return $this->error('仅支持备份五个，请删除后重新备份');
				}
				
			}
			if(isset($get_str['down'])&&$get_str['down']!=''){
				if(in_array($get_str['down'], $securityArray)){
					return $this->error('该功能正在构架中');
				}else{
					return $this->error('非法操作');
				}
			}
		}else{
			$view = new View();
			if(isset($WebBackupList['data'][0])){
				foreach ($WebBackupList['data'] as $key => $value) {
					$WebBackupList['data'][$key]['size'] = Tools::formatBytes($WebBackupList['data'][$key]['size']);
				}
			}
			$view->assign('countback',count(@$WebBackupList['data']));
			$view->assign('WebBackupList',$WebBackupList);
			$view->assign('USERINFO',$this->USERINFO);
			$view->assign('btHostInfo',$this->VHOST);
			$view->assign('hostInfo',$this->HOSTINFO);

			return $view->fetch();
		}
	}

	/**
	 * 网站SSL管理
	 */
	public function Ssl(){

		$bt = new Bt($this->BTURL,$this->BTOKEN);
		if($post_str = Request::instance()->post()){
			if(Request::instance()->isAjax()){
				if($post_str['toHttps']=='1'){
					if($HttpToHttps = $bt->HttpToHttps($this->VHOST['domain'])){
						return Tools::buildSuccess(200,$HttpToHttps['msg']);
					}else{
						return Tools::buildSuccess('-1','修改失败：'.$HttpToHttps['msg']);
					}
				}else{
					if($HttpToHttps = $bt->CloseToHttps($this->VHOST['domain'])){
						return Tools::buildSuccess(200,$HttpToHttps['msg']);
					}else{
						return Tools::buildSuccess('-1','修改失败：'.$HttpToHttps['msg']);
					}
				}
			}
			$modify_status = $bt->SetSSL(1,$this->VHOST['domain'],$post_str['key'],$post_str['csr']);
			if(isset($modify_status)&&$modify_status['status']=='true'){
				return $this->success($modify_status['msg']);
			}else{
				return $this->error('设置失败：'.$modify_status['msg']);
			}
		}elseif ($get_str = Request::instance()->get()) {
			if(isset($get_str['ssl'])&&$get_str['ssl']=='off'){	
				if($modify_status = $bt->CloseSSLConf(1,$this->VHOST['domain'])){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}
			if(isset($get_str['https'])&&$get_str['https']=='off'){	
				if($modify_status = $bt->CloseToHttps($this->VHOST['domain'])){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}
		}else{

			$GetSSL = $bt->GetSSL($this->VHOST['domain']);

			$view = new View();

			$view->assign('GetSSL',$GetSSL);
			$view->assign('USERINFO',$this->USERINFO);
			$view->assign('btHostInfo',$this->VHOST);
			$view->assign('hostInfo',$this->HOSTINFO);

			return $view->fetch();
		}
	}

	/**
	 * 网站防盗链管理
	 */
	public function Protection(){

		$bt = new Bt($this->BTURL,$this->BTOKEN);
		$GetSecurity = $bt->GetSecurity($this->VHOST['btid'],$this->VHOST['domain']);
		if($post_str = Request::instance()->post()){
			if(!empty($post_str['sec_fix'])||!empty($post_str['sec_domains'])){
				$modify_status = $bt->SetSecurity($this->VHOST['btid'],$this->VHOST['domain'],$post_str['sec_fix'],$post_str['sec_domains'],true);
				if(isset($modify_status)&&$modify_status['status']=='true'){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}else{
				return $this->error('规则不能为空');
			}
		}elseif ($get_str = Request::instance()->get()) {
			if(isset($get_str['protection'])&&$get_str['protection']=='off'){
				$modify_status = $bt->SetSecurity($this->VHOST['btid'],$this->VHOST['domain'],$GetSecurity['fix'],$GetSecurity['domains'],false);
				if(isset($modify_status)&&$modify_status['status']=='true'){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}
		}else{
			$view = new View();
			$view->assign('GetSecurity',$GetSecurity);
			$view->assign('USERINFO',$this->USERINFO);
			$view->assign('btHostInfo',$this->VHOST);
			$view->assign('hostInfo',$this->HOSTINFO);

			return $view->fetch();
		}
	}

	/**
	 * 网站日志列表
	 */
	public function Sitelog(){

		$bt = new Bt($this->BTURL,$this->BTOKEN);
		$logList = $bt->GetSiteLogs($this->VHOST['domain']);
		if($logList['msg']){
			if(isset($logList['status'])&&$logList['status']=='true'){
				$logArr = explode("\n", $logList['msg']);
			}else{
				$logArr = '';
			}
		}else{
			$logArr = '';
		}
		
		$view = new View();
		$view->assign('logList',$logArr);
		$view->assign('USERINFO',$this->USERINFO);
		$view->assign('btHostInfo',$this->VHOST);
		$view->assign('hostInfo',$this->HOSTINFO);

		return $view->fetch();
	}

	/**
	 * 网站密码访问
	 */
	public function Httpauth(){

		$bt = new Bt($this->BTURL,$this->BTOKEN);
		if($post_str = Request::instance()->post()){
			if(!empty($post_str['username'])&&!empty($post_str['password'])){
				$modify_status = $bt->SetHasPwd($this->VHOST['btid'],$post_str['username'],$post_str['password']);
				if(isset($modify_status)&&$modify_status['status']=='true'){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}else{
				return $this->error('账号或密码为空');
			}
		}elseif ($get_str = Request::instance()->get()) {
			if(isset($get_str['auth'])&&$get_str['auth']=='off'){
				if($modify_status = $bt->CloseHasPwd($this->VHOST['btid'])){
					return $this->success($modify_status['msg']);
				}else{
					return $this->error('设置失败：'.$modify_status['msg']);
				}
			}
		}else{
			//user_后期需要删除
			$vhost_url = '/www/wwwroot/'.explode('.',$this->VHOST['domain'])[0];
			$setting = $bt->GetDirUserINI($this->VHOST['btid'],$vhost_url);


			$view = new View();

			$view->assign('pass_status',$setting['pass']);
			$view->assign('USERINFO',$this->USERINFO);
			$view->assign('btHostInfo',$this->VHOST);
			$view->assign('hostInfo',$this->HOSTINFO);

			return $view->fetch();
		}
	}


	/**
	 * 一键部署网站
	 * @return [type] [description]
	 */
	public function deployment(){
		$bt = new Bt($this->BTURL,$this->BTOKEN);
		$deploymentList = $bt->deployment();
		if($get_str = Request::instance()->get()){
			if($dep = $get_str['dep']){
				$is_inarray = false;
				foreach ($deploymentList['data'] as $key => $value) {
					if(in_array($get_str['dep'],$deploymentList['data'][$key])){
						$is_inarray = true;
						break;
					}
				}
				if($is_inarray){
					if($SetupPackage = $bt->SetupPackage($get_str['dep'],$this->VHOST['domain'],@$this->GetSitePHPVersion()['phpversion'])){
						return $this->success('一键部署成功','/vhost/deployment.html');
					}else{
						return $this->error('部署失败，请稍后再试，如多次重试依然失败请联系管理员');
					}
				}else{
					return $this->error('没有该程序可以安装');
				}
				
			}
		}
		//程序列表倒叙
		$deploymentList['data'] = array_reverse($deploymentList['data']);

		$view = new View();
		$view->assign('deploymentList',$deploymentList);
		$view->assign('USERINFO',$this->USERINFO);
		return $view->fetch();
	}
}