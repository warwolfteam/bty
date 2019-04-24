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
use app\util\Strs;

class Redeem extends Controller{
	private $UserInfo = '';

	public function __construct(){
		
		$this->UserInfo = Login::isUser();
		if(!$this->UserInfo){
			$this->error('未登录，请先登录','/admin/login.html');
		};
	}

	public function index(){
		if (Request::instance()->isAjax()){
			$type = Request::instance()->post('type');
			if($id = Request::instance()->post('id')&&$type=='del'){
				if($this->redeemDel($id)){
					return Tools::buildSuccess(200,'删除成功');
				}else{
					return Tools::buildSuccess(200,'删除失败');
				}
			}
			if($type=='inc'){
				$money = Request::instance()->post('money');
				$number = Request::instance()->post('number');
				if(!preg_match("/^[1-9][0-9]*$/",$money)){
					return Tools::buildFailed(-1,'非法请求');
				}
				if(!preg_match("/^[1-9][0-9]*$/",$number)){
					return Tools::buildFailed(-1,'非法请求');
				}
				if($money==0||$number==0){
					return Tools::buildFailed('-1','所有项不能为0');
				}
				if($number>20){
					return Tools::buildFailed('-1','最大生成20个');
				}
				
				for ($i=0; $i < $number; $i++) { 
					$rand = Strs::randString(4).'-'.Strs::randString(4).'-'.Strs::randString(4).'-'.Strs::randString(4);
					$randArr = ['redeem'=>$rand,'money'=>$money,'ctime'=>time()];
					Db::name('redeem')->insert($randArr);
				}
				return Tools::buildSuccess(200,'生成完毕');
			}
		}
		$searchValue = Request::instance()->get('search');
		return $this->getRedeem($searchValue);
	}

	private function getRedeem($search=''){

		$pageCount = Db::name('redeem')->where('redeem','like',$search.'%')->count();

		$pageStep = 10;	 //页数步长
		$pageNum = ceil($pageCount/$pageStep);	
		if(preg_match("/^[1-9][0-9]*$/",key(input('get.')))){
			$page = key(input('get.'));
		}else{
			$page = 1;
		}
		$fy = ($page-1) * $pageStep;	//起始数量

		$redeemList = Db::name('redeem')->where('redeem','like',$search.'%')->limit($fy,$pageStep)->order('status asc')->select();
		foreach ($redeemList as $key => $value) {
			if($redeemList[$key]['userid']){
				$group = Db::name('user')->where('id',$redeemList[$key]['userid'])->find();
				$redeemList[$key]['username'] = $group['username'];
			}else{
				$redeemList[$key]['username'] = '';
			}
			
			$redeemList[$key]['ctime'] = date("Y/m/d H:i:s",$redeemList[$key]['ctime']);
			if($redeemList[$key]['etime']){
				$redeemList[$key]['etime'] = date("Y/m/d H:i:s",$redeemList[$key]['etime']);
			}
		}
		
		$view = new View();


		$view->pageNum = Tools::multipage($pageNum,$page,$pageStep,'&search='.$search);


		$view->assign('redeemList',$redeemList);
		$view->assign('userInfo',$this->UserInfo);
		return $view->fetch();
	}

	private function redeemDel($id){
		return Db::name('redeem')->where('id',$id)->delete();
	}
}