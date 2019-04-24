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
use app\bt\Bt;
use app\user\behavior\Hostbuild;

class Order extends Controller{

	private $UserInfo = '';
	public function __construct(){
		$this->UserInfo = Login::isUser();
		if(!$this->UserInfo){
			$this->error('未登录，请先登录','/user/login.html');
		};
	}

	public function index(){
		$user = $this->UserInfo;
		if(!$user){
			return $this->error('非法操作','/user/login.html');
		}
		if($orderId = Request::instance()->post('id')){
			if(!preg_match("/^[1-9][0-9]*$/",$orderId)){
				return Tools::buildFailed('-1','非法请求');
			}
			$orderFind = Db::name('order')->where('id',$orderId)->where('status',0)->find();
			if(!$orderFind){
				return Tools::buildFailed('-1','该订单不存在');
			}
			$hostInfo = $this->hostInfo($orderFind['hostid']);
			if(!$hostInfo){
				return Tools::buildFailed('-1','该主机已不存在');
			}
			//$payment = $hostInfo['money']*$orderFind['quantity'];
			$payment = $orderFind['shouldment'];

			if($user['money']<$payment){
				return Tools::buildFailed('-2','余额不足');
			}

			if($payment!=0){
				$symoney = $user['money']-$payment;
				$syUp = Db::name('user')->where('id',$user['id'])->setField('money',$symoney);
				if(!$syUp){
					return Tools::buildFailed('-2','扣款失败');
				}
			}
			$quantity = $orderFind['quantity']*30;
			Pub::billSet($user['id'],$payment,'购买主机：'.$hostInfo['name'].' '.$quantity.'天');
			$orderstatusUp = Db::name('order')->where('id',$orderId)->update(['status'=>1,'paytime'=>time(),'payment'=>$payment]);
			if(!$orderstatusUp){
				return Tools::buildFailed('-2','付款成功，但订单状态更新失败，请联系管理员进行处理');
			}

			//这里使用宝塔操作生成主机，返回主机信息
			$Hostbuild = new Hostbuild();
			return $Hostbuild->btSet($orderId);
			//return Tools::buildSuccess('200','付款成功',['money'=>$symoney]);
			
		}elseif ($activation = Request::instance()->post('activation')) {
			//开通主机
			
			$Hostbuild = new Hostbuild();
			return $Hostbuild->btSet($activation);
		}else{
			$orderList = Db::name('order')->where('userid',$user['id'])->limit(10)->select();

			foreach ($orderList as $key => $value) {
				$orderList[$key]['hostname'] = $this->hostInfo($orderList[$key]['hostid'])['name'];
				$orderList[$key]['ctime'] = date("Y-m-d H:i:s",$orderList[$key]['ctime']);
				if($orderList[$key]['status']!=0){
					$orderList[$key]['paytime'] = date("Y-m-d H:i:s",$orderList[$key]['paytime']);
				}else{
					$orderList[$key]['paytime'] = '';
				}
			}
			$view = new View();
			$view->assign('orderList',$orderList);
			$view->assign('userInfo',$this->UserInfo);
			return $view->fetch();
		}
		
	}

	/**
	 * 获取服务器信息
	 * @param  [type] $hostid [description]
	 * @return [type]         [description]
	 */
	private function hostInfo($hostid){
		return Db::name('host')->where('id',$hostid)->find();

	}


		/**
	 * 已购买的主机信息
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function host($id){
		if(!preg_match("/^[1-9][0-9]*$/",$id)){
			return $this->error('非法请求');
		}
		$bthostInfo = Db::name('hostlist')->where('id',$id)->where('userid',$this->UserInfo['id'])->find();
		if(!$bthostInfo){
			return $this->error('没有找到该主机信息');
		}
		$hostInfo = Db::name('host')->where('id',$bthostInfo['hostid'])->where('status',1)->find();
		if(!$hostInfo){
			return $this->error('没有找到该服务器信息，可能被管理员删除');
		}
		//剩余天数
		$syts = (int)(($bthostInfo['etime'] - time()) / 86400);

		$bthostInfo['syts'] = $syts<30 ? '<span class="text-danger">'.$syts.'</span>' : '<span class="text-info">'.$syts.'</span>';

		$bthostInfo['stime'] = date("Y-m-d H:i:s",$bthostInfo['stime']);
		$bthostInfo['etime'] = date("Y-m-d H:i:s",$bthostInfo['etime']);
		
		

		$view = new View();
		$view->assign('hostInfo',$hostInfo);
		$view->assign('userInfo',$this->UserInfo);
		$view->assign('bthostInfo',$bthostInfo);

		$view->hostTag = explode(',', $hostInfo['tag']);
		$view->service = explode(',', $hostInfo['service']);
		return $view->fetch();
	}
}