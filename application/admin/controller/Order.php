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
use app\bt\Bt;

class Order extends Controller{
	private $UserInfo = '';

	public function __construct(){
		
		$this->UserInfo = Login::isUser();
		if(!$this->UserInfo){
			$this->error('未登录，请先登录','/admin/login.html');
		};
	}

	public function index(){
		if($post = Request::instance()->post()){
			$id = @$post['id'];
			$type = @$post['type'];
			if(!preg_match("/^[1-9][0-9]*$/",$id)){
				return Tools::buildFailed(-1,'非法请求');
			}
			if($id&&$type=='del'){
				if($this->delHost($id)){
					if(Db::name('order')->where('id',$id)->delete()){
						return $this->success('删除成功');
					}else{
						return $this->error('删除失败');
					}
				}else{
					return $this->error('删除站点失败');
				}
				
			}
		}else{
			$searchValue = Request::instance()->get('search');
			return $this->orderList($searchValue);
		}
		
	}


	/**
	 * 订单列表
	 * @param  string $search 查询词条
	 * @return [type]         [description]
	 */
	private function orderList($search=''){
		$pageCount = Db::name('order')->where('hostip','like',$search.'%')->count();

		$pageStep = 10;	 //页数步长
		$pageNum = ceil($pageCount/$pageStep);	
		if(preg_match("/^[1-9][0-9]*$/",key(input('get.')))){
			$page = key(input('get.'));
		}else{
			$page = 1;
		}
		$fy = ($page-1) * $pageStep;	//起始数量

		$orderList = Db::name('order')->where('hostip','like',$search.'%')->limit($fy,$pageStep)->order('id desc')->select();
		foreach ($orderList as $key => $value) {
			$orderList[$key]['ctime'] = date("Y/m/d H:i:s",$orderList[$key]['ctime']);
			$orderList[$key]['paytime'] = date("Y/m/d H:i:s",$orderList[$key]['paytime']);
			$orderList[$key]['user'] = $this->userName($orderList[$key]['userid']);
			$orderList[$key]['domain'] = $this->getDomain($orderList[$key]['hostlistid'])['domain'];
		}
		
		
		$view = new View();


		$view->pageNum = Tools::multipage($pageNum,$page,$pageStep,'&search='.$search);


		$view->assign('orderList',$orderList);
		$view->assign('userInfo',$this->UserInfo);
		return $view->fetch();
	}

	/**
	 * 获取用户名
	 * @param  [type] $userid 用户ID
	 * @return [type]         [description]
	 */
	private function userName($userid){
		if($name = Db::name('user')->where('id',$userid)->value('username')){
			return $name;
		}else{
			return false;
		}
	}

	/**
	 * 连接宝塔进行删除站点数据
	 * @param  [type] $orderId 订单ID
	 * @return [type]          [description]
	 */
	private function delHost($orderId){
		// 获取订单信息
		$orderInfo = Db::name('order')->where('id',$orderId)->find();
		if(!$orderInfo){
			return false;
		}
		// 获取服务器api
		$btHost = Db::name('host')->where('id',$orderInfo['hostid'])->find();
		if(!$btHost){
			return false;
		}
		// 获取主机站点名
		$btdomain = $this->getDomain($orderInfo['hostlistid'])['domain'];
		$btId = $this->getDomain($orderInfo['hostlistid'])['btid'];

		//删除主机信息
		if($this->delhostList($orderInfo['hostlistid'])){
			if(!$btdomain){
				return false;
			}
			$bt = new Bt($btHost['bturl'],$btHost['btoken']);
			$webDelete = $bt->WebDeleteSite($btId, $btdomain, 1, 1, 1);
			if($webDelete){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}

		

	}

	/**
	 * 反查订单绑定的域名
	 * @param  [type] $hostlistid hostlistid
	 * @return [type]             [description]
	 */
	private function getDomain($hostlistid){
		if($bthost = Db::name('hostlist')->where('id',$hostlistid)->find()){
			return $bthost;
		}else{
			return false;
		}
	}

	/**
	 * 删除主机信息
	 * @param  [type] $id 主机ID
	 * @return [type]     [description]
	 */
	private function delhostList($id){
		if(Db::name('hostlist')->where('id',$id)->delete()){
			return true;
		}else{
			return false;
		}
	}
}