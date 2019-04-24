<?php 
namespace app\user\behavior;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use app\util\Tools;

class Pub{

	/**
	 * 增加个人账单列表
	 * @param  [type]  $userid 用户ID
	 * @param  [type]  $money  金额
	 * @param  [type]  $desc   详情
	 * @param  integer $type   类型 0->消费 1->充值 2->邀请 3->兑换
	 * @return [type]          [description]
	 */
	public static function billSet($userid,$money,$desc,$type=0){
		$billArr = array(
			'userid' => $userid,
			'money' => $money,
			'type' => $type,
			'desc' => $desc,
			'ctime' => time(),
		);
		$billInc = Db::name('bill')->insert($billArr);
		if(!$billInc){
			return false;
		}else{
			return true;
		}
	}

	/**
	 * 账单列表
	 * @param  [type] $userid [description]
	 * @return [type]         [description]
	 */
	public static function billList($userid,$num=10){
		$billFind = Db::name('bill')->where('userid',$userid)->limit($num)->order('id desc')->select();
		foreach ($billFind as $key => $value) {
			$billFind[$key]['ctime'] = date("Y-m-d H:i:s",$billFind[$key]['ctime']);
		}
		return $billFind;
	}

	/**
	 * 个人主机列表
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public static function hostList($userid,$num=10){
		if($hostList = Db::name('hostlist')->where('userid',$userid)->limit($num)->select()){
			return $hostList;
		}else{
			return false;
		}
	}

	/**
	 * 用户金币操作
	 * @param [type]  $userid 用户ID
	 * @param [type]  $money  金钱
	 * @param integer $type   0->减少 1->增加
	 */
	public static function balance($userid,$money,$type=0){
		switch ($type) {
			case '0':
				$balance = Db::name('user')->where('id',$userid)->setDec('money', $money);
				break;
			case '1':
				$balance = Db::name('user')->where('id',$userid)->setInc('money', $money);
				break;
			default:
				break;
		}
		if($balance){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 获取网站域名
	 * @return [type] [description]
	 */
	public static function get_webInfo(){
		if($webFind = Db::name('webinfo')->where('id',1)->value('webdomain')){
			return $webFind;
		}else{
			return false;
		}
	}
}