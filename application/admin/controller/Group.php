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

class Group extends Controller{
	private $UserInfo = '';

	public function __construct(){
		
		$this->UserInfo = Login::isUser();
		if(!$this->UserInfo){
			$this->error('未登录，请先登录','/admin/login.html');
		};
	}

	public function index(){
		if(Request::instance()->isPost()){
			$postStr = Request::instance()->Post();
			$type = $postStr['type'];
			$id = @$postStr['id'];
			if($type=='update'){
				$name = $postStr['name'];
				$discount = $postStr['discount'];
				if(Db::name('usergroup')->update(['id'=>$id,'name'=>$name,'discount'=>$discount])){
					return Tools::buildSuccess(200,'修改成功');
				}else{
					return Tools::buildFailed('-1','修改失败');
				}
			}

			if($type=='del'){
				if(Db::name('usergroup')->where('id',$id)->delete()){
					return Tools::buildSuccess(200,'删除成功');
				}else{
					return Tools::buildFailed('-1','删除失败');
				}
			}
			if($type=='inc'){
				if(Db::name('usergroup')->insert(['name'=>$postStr['name'],'discount'=>$postStr['discount']])){
					return Tools::buildSuccess(200,'新增成功');
				}else{
					return Tools::buildFailed('-1','新增失败');
				}
			}

		}else{
			$groupList = Db::name('usergroup')->select();
			$view = new View();
			$view->assign('groupList',$groupList);
			$view->assign('userInfo',$this->UserInfo);
			return $view->fetch();
		}
		
	}

}