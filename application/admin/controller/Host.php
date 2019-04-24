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

class Host extends Controller{
	private $UserInfo = '';

	public function __construct(){
		
		$this->UserInfo = Login::isUser();
		if(!$this->UserInfo){
			$this->error('未登录，请先登录','/admin/login.html');
		};
	}

	public function index(){
		if(Request::instance()->isPost()){
			/**
			 * 是否推荐
			 */
			if(Request::instance()->post('hot')==1){
				$reType = Request::instance()->post('type')==1 ? 1 : 0;
				$rehostid = Request::instance()->post('hostid');
				if(Db::name('host')->where('id',$rehostid)->update(['hot'=>$reType])){
					return Tools::buildSuccess(200,'修改成功');
				}else{
					return Tools::buildFailed('-1','修改失败');
				}
			}
			/**
			 * 是否启用
			 */
			if(Request::instance()->post('status')==1){
				$reType = Request::instance()->post('type')==1 ? 1 : 0;
				$rehostid = Request::instance()->post('hostid');
				if(Db::name('host')->where('id',$rehostid)->update(['status'=>$reType])){
					return Tools::buildSuccess(200,'修改成功');
				}else{
					return Tools::buildFailed('-1','修改失败');
				}
			}
		}elseif (Request::instance()->isAjax()){
			$reType = Request::instance()->post('type');
			if($reType=='inc'){
				$incStrArr = Request::instance()->post();
				$name = $incStrArr['name'];
				$ip = $incStrArr['ip'];
				$money = $incStrArr['money'];
				$group = $incStrArr['group'];
				$expiretime = $incStrArr['expiretime'];
				$bturl = $incStrArr['bturl'];
				$btoken = $incStrArr['btoken'];

				if($this->incHost($name,$ip,$money,$group,$expiretime,$bturl,$btoken)){
					return Tools::buildSuccess(200,'新增成功');
				}else{
					return Tools::buildFailed(-1,'新增失败');
				}
			}
		}else{
			$searchValue = Request::instance()->get('search');
			$hostList = Db::name('host')->where('ip','like',$searchValue.'%')->select();

			foreach ($hostList as $key => $value) {
				$hostList[$key]['expiretime'] = date("Y-m-d",$hostList[$key]['expiretime']);
			}
			$view = new View();

			$view->assign('hostList',$hostList);
			$view->assign('userInfo',$this->UserInfo);
			return $view->fetch();
		}
		
	}

	/**
	 * 新增主机操作
	 * ajax版
	 * @param  [type] $name       [description]
	 * @param  [type] $ip         [description]
	 * @param  [type] $money      [description]
	 * @param  [type] $group      [description]
	 * @param  [type] $expiretime [description]
	 * @param  [type] $bturl      [description]
	 * @param  [type] $btoken     [description]
	 * @return [type]             [description]
	 */
	private function incHost($name,$ip,$money,$group,$expiretime,$bturl,$btoken){
		$userArr['name']=$name;
		$userArr['ip']=$ip;
		$userArr['money']=$money;
		$userArr['group']=$group;
		$userArr['expiretime']=strtotime($expiretime);
		$userArr['bturl']=$bturl;
		$userArr['btoken']=$btoken;
		$userArr['status']=1;
		$incHost = Db::name('host')->insert($userArr);
		if($incHost){
			return true;
		}else{
			return false;
		}

	}


	/**
	 * 主机编辑操作
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function edit($id){
		if (Request::instance()->isPost()){
			$infoArr = Request::instance()->post();
			$infoArr['expiretime'] = strtotime($infoArr['expiretime']);

			$hostUp = Db::name('host')->update($infoArr);
			if($hostUp){
				$this->success('信息更新成功','/admin/host.html');
			}else{
				$this->error('信息更新失败');
			}
		}else{
			$hostFind = Db::name('host')->where('id',$id)->find();
			if(!$hostFind){
				return $this->error('该主机ID不存在');
			}
			$hostFind['expiretime'] = date("Y-m-d",$hostFind['expiretime']);
			$view = new View();
			$view->assign('hostInfo',$hostFind);
			$view->assign('userInfo',$this->UserInfo);
			return $view->fetch();
		}
		
	}

	/**
	 * 主机删除操作
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function delete($id){
		if(!preg_match("/^[1-9][0-9]*$/",$id)){
			return $this->error('非法请求');
		}
		$del = Db::name('host')->where('id',$id)->delete();
		if(!$del){
			return $this->error('删除失败');
		}
		return $this->success('成功删除');
	}

	/**
	 * 新增主机
	 * 单页版
	 * @return [type] [description]
	 */
	public function inc(){
		if (Request::instance()->isPost()){
			$infoArr = Request::instance()->post();
			$infoArr['expiretime'] = strtotime($infoArr['expiretime']);
			$hostUp = Db::name('host')->insert($infoArr);
			if($hostUp){
				$this->success('新增服务器成功','/admin/host.html');
			}else{
				$this->error('信息更新失败');
			}
		}else{
			$view = new View();
			$view->assign('userInfo',$this->UserInfo);
			return $view->fetch();
		}
	}
}