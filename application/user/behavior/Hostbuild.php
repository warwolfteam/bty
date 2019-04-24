<?php 

namespace app\user\behavior;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use app\util\Tools;
use app\util\Strs;
use app\user\controller\Login;
use app\user\controller\Index;
use app\bt\Bt;

class Hostbuild{
	private $USERINFO = '';
	public function __construct(){
		if(!Login::isUser()){
			$this->error('未登录，请先登录','/user/login.html');
		};
		$this->USERINFO = Login::isUser();
	}

	/**
	 * 订单复验
	 * 服务器复验
	 * 开通主机
	 * 写入主机信息
	 * 更新订单
	 * @param  [type] $activation order订单号
	 * @param  [type] $status     当前订单状态
	 * @return [type]             [description]
	 */
	public function btSet($activation,$status=1){
		if(!preg_match("/^[1-9][0-9]*$/",$activation)){
			return Tools::buildFailed('-1','非法请求');
		}
		$orderFind = Db::name('order')->where(['id'=>$activation,'status'=>$status])->find();
		if(!$orderFind){
			return Tools::buildFailed('-1','开通失败：该订单不存在');
		}
		$hostInfo = Db::name('host')->where('id',$orderFind['hostid'])->find();
		if(!$hostInfo){
			return Tools::buildFailed('-1','开通失败：服务器不存在');
		}
	  	// 连接宝塔进行创建网站操作
		$btWebSet = $this->bt($this->USERINFO['id'],$hostInfo,$orderFind['quantity']);
		if(!$btWebSet){
			return Tools::buildFailed('-2','开通失败:主机创建失败');
		}
		if(isset($btWebSet['code'])){
			return Tools::buildFailed('-2',@$btWebSet['msg']);
		}
	  	// 主机信息录入到表
		$hostListId = $this->hostListInc($hostInfo,$btWebSet);
		if(!$hostListId){
			return Tools::buildFailed('-2','开通失败:主机信息录入失败');
		}
	  	// 改变订单状态为开通
		if(!$this->orderUp($orderFind['id'],$hostListId)){
			return Tools::buildFailed('-2','开通成功:订单状态更新失败');
		}
		return Tools::buildSuccess('200','主机已成功开启，详细信息请在控制台查看');
	}

	/**
	 * 链接宝塔API开通网站
	 * @param  [type]  $userid   [description]
	 * @param  [type]  $hostInfo [description]
	 * @param  integer $quantity [description]
	 * @return [type]            [description]
	 */
	private function bt($userid,$hostInfo,$quantity=1){

	  	//网站绑定的泛解析域名
		$defaultDomain = $hostInfo['domain'];
		$btUrl = $hostInfo['bturl'];
		$bToken = $hostInfo['btoken'];
		$bt = new Bt($btUrl,$bToken);
		$userRandId = strtolower(Strs::randString(6)).'_'.$userid;

		$phpversion_list = $bt->GetPHPVersion();

		$arrKey = $phpversion_list?count($phpversion_list)-1:'0';
		$phpversion = trim($phpversion_list[$arrKey]['version']);

		$hostSetInfo = array(
			'webname' => '{"domain":"'.$userRandId.'.'.$defaultDomain.'","domainlist":[],"count":0}',
			'path' => '/www/wwwroot/'.$userRandId,
			'type_id' => '2',
			'type' => 'PHP',
			'version' => $phpversion?$phpversion:'00',
			'port' => '80',
			'ps' => '用户'.$userid.'的网站',
			'ftp' => "true",
			'ftp_username' => $userRandId,
			'ftp_password' => Strs::randString(12),
			'sql' => "true",
			'codeing' => 'utf8',
			'datauser' => $userRandId,
			'datapassword' => Strs::randString(12),
		);
	    //使用宝塔创建网站
		$btInfo = $bt->AddSite($hostSetInfo);

		if(!isset($btInfo['siteStatus'])&&@$btInfo['siteStatus']!=true){
			return ['code'=>'-2','msg'=>'主机创建失败->'.@$btInfo['msg']];
		}

		if($hostSetInfo['sql']=='true'){
			if(!isset($btInfo['databaseStatus'])||$btInfo['databaseStatus']==false){
				return ['code'=>'-3','msg'=>'数据库创建失败：'.@$btInfo['msg'].' 请工单进行人工添加'];
			}
		}
		if($hostSetInfo['ftp']=='true'){
			if(!isset($btInfo['ftpStatus'])||$btInfo['ftpStatus']==false){
				return ['code'=>'-4','msg'=>'数据库创建失败：'.@$btInfo['msg'].' 请工单进行人工添加'];
			}
		}
	    //查询创建的网站ID
		$btWeb = $this->WebQuery($bt,$userRandId.'.'.$hostInfo['domain']);
		if(!$btWeb){
			return ['code'=>'-5','msg'=>'主机不存在：'.@$btWeb['msg']];
		}
	    //开通时间设置
	    $quantity = $quantity*30;
		$timeSet = $bt->WebSetEdate($btWeb['id'],date('Y-m-d',strtotime("+$quantity day")));
		if(!$timeSet['status']){
			return ['code'=>'-6','msg'=>'开通时间设置失败'];
		} 
	    //宝塔面板开通网站产生的额外信息
		$hostSetInfo['btid'] = $btWeb['id'];
		$hostSetInfo['domain'] = $userRandId.'.'.$defaultDomain;
		$hostSetInfo['stime'] = time();
		$hostSetInfo['etime'] = strtotime("+$quantity day");
		return $hostSetInfo;
	}

	/**
	 * 回调查询宝塔网站ID
	 * @param [type] $bt  [description]
	 * @param [type] $key [description]
	 */
	private function WebQuery($bt,$key){
		return $bt->Websites($key)['data'][0];
	}

	/**
	 * 主机信息录入
	 * @param  [type] $hostInfo [description]
	 * @param  [type] $btInfo   [description]
	 * @return [type]           [description]
	 */
	private function hostListInc($hostInfo,$btInfo){
		$hostArr = array(
			'userid' => $this->USERINFO['id'],
			'hostid' => $hostInfo['id'],
			'btid' => $btInfo['btid'],
			'domain' => $btInfo['domain'],
			'status' => 1,
			'stime' => $btInfo['stime'],
			'etime' => $btInfo['etime'],
			'ftpname' => $btInfo['ftp_username'],
			'ftpkey' => $btInfo['ftp_password'],
			'sqlname' => $btInfo['datauser'],
			'sqlkey' => $btInfo['datapassword'],
			'username' => 'u'.Strs::randString(6),
			'password' => 'p'.Strs::randString(6),
		);
		if(Db::name('hostlist')->insert($hostArr)){
			return Db::name('hostlist')->getLastInsID();;
		}else{
			return false;
		}
	}

	/**
	 * 修改订单状态以及主机ID
	 * @param  [type] $id         订单ID
	 * @param  [type] $hostlistid 主机列表ID
	 * @return [type]             [description]
	 */
	private function orderUp($id,$hostlistid){
		return Db::name('order')->where('id',$id)->update(['status'=>2,'hostlistid'=>$hostlistid]);
	}
}