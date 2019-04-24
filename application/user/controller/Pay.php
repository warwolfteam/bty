<?php
namespace app\user\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use think\Route;
use app\util\Tools;
use app\user\controller\Login;
use app\user\behavior\Pub;
use app\util\Strs;
use app\user\behavior\Alipaysubmit;
use app\user\behavior\Alipaynotify;

class Pay extends Controller{
	private $UserInfo = '';

		//商户ID
	private $partner = '';

		//商户KEY
	private $key = '';

		//签名方式 不需修改
	private $sign_type = '';

		//字符编码格式 目前支持 gbk 或 utf-8
	private $input_charset = '';

		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
	private $transport = '';

		//支付API地址
	private $apiurl = '';

	private $notify_url = '';
	private	$return_url = '';

	private $status = '';

	public function __construct(Request $request){
		$this->UserInfo = Login::isUser();

		/**
		 * 此处由于notify_pay特殊的回调方式，所以可以公开访问
		 */
		if($request->action()!='notify_pay'){
			if(!$this->UserInfo){
				$this->error('未登录，请先登录','/user/login.html');
			};
		}
		

		$payConfig = $this->get_payconfig();
		$webdomain = Pub::get_webInfo();
		if($payConfig){
			$this->partner = $payConfig['partner'];
			$this->key = $payConfig['key'];
			$this->transport = $payConfig['ssl']?'https':'http';
			$this->sign_type = strtoupper('MD5');
			$this->input_charset = strtolower('utf-8');
			$this->apiurl = $payConfig['apiurl'];
			$this->status = json_decode($payConfig['status'],1);
			$this->notify_url = $this->transport.'://'.$webdomain.$payConfig['notify_url'];
			$this->return_url = $this->transport.'://'.$webdomain.$payConfig['return_url'];
		}
	}

	public function index(){
		$user = $this->UserInfo;
		if(!$user){
			return $this->error('非法操作','/user/login.html');
		}

		if($redeem = Request::instance()->post('redeem')){
			if(!$redeem){
				return Tools::buildFailed('-1','兑换码为空');
			}
			// for ($i=0; $i < 50; $i++) { 
			// 	$rand = Strs::randString(4).'-'.Strs::randString(4).'-'.Strs::randString(4).'-'.Strs::randString(4);
			// 	$arr = ['redeem'=>$rand,'money'=>'20','ctime'=>time()];
			// 	Db::name('redeem')->insert($arr);
			// }
			return $this->redeem($redeem);
		}

		$pay_list = Db::name('pay')->where('userid',$this->UserInfo['id'])->limit(10)->order('id desc')->select();

		foreach ($pay_list as $key => $value) {
			$pay_list[$key]['ctime'] = date("Y-m-d H:i:s",$pay_list[$key]['ctime']);
			if($pay_list[$key]['status']!=0){
				$pay_list[$key]['ptime'] = date("Y-m-d H:i:s",$pay_list[$key]['ptime']);
			}else{
				$pay_list[$key]['ptime'] = '';
			}
		}
		$view = new View();
		$view->assign('userInfo',$user);
		$view->assign('pay_list',$pay_list);
		$view->status = $this->status;
		return $view->fetch();
	}

	/**
	 * 兑换码兑换
	 * @param  [type] $redeem 兑换码
	 * @return [type]         [description]
	 */
	public function redeem($redeem){
		if($redeem_find = Db::name('redeem')->where('redeem',$redeem)->where('status',1)->find()){
			if($redeem_del = Db::name('redeem')->where('id',$redeem_find['id'])->update(['status'=>0,'etime'=>time(),'userid'=>$this->UserInfo['id']])){
				if(Pub::balance($this->UserInfo['id'],$redeem_find['money'],1)){
					if(Pub::billSet($this->UserInfo['id'],$redeem_find['money'],'兑换'.$redeem_find['money'],3)){
						return Tools::buildSuccess('200','兑换成功:'.$redeem_find['money']);
					}else{
						return Tools::buildFailed('-1','记录失败');
					}
				}else{
					return Tools::buildFailed('-1','余额更新失败');
				}
			}else{
				return Tools::buildFailed('-1','兑换码删除失败');
			}
		}else{
			return Tools::buildFailed('-1','兑换码无效或已经被兑换');
		}
	}


	/**
	 * 彩虹易支付模块
	 * @param  [type] $payArr 订单参数
	 * @return [type]         [description]
	 */
	public function epay_pay($payArr){
		$alipay_config = $this->payConfigArr();


		/**************************请求参数**************************/
		$notify_url = $this->notify_url;
        //需http://格式的完整路径，不能加?id=123这类自定义参数

        //页面跳转同步通知页面路径
		$return_url = $this->return_url;
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

        //商户订单号
		$out_trade_no = $payArr['out_trade_no'];
        //商户网站订单系统中唯一订单号，必填


		//支付方式
		$type = $payArr['type'];
        //商品名称
		$name = $payArr['name'];
		//付款金额
		$money = $payArr['money'];
		//站点名称
		$sitename = Session::get('webname');
        //必填

        //订单描述


		/************************************************************/

		//构造要请求的参数数组，无需改动
		$parameter = array(
			"pid" => trim($alipay_config['partner']),
			"type" => $type,
			"notify_url"	=> $notify_url,
			"return_url"	=> $return_url,
			"out_trade_no"	=> $out_trade_no,
			"name"	=> $name,
			"money"	=> $money,
			"sitename"	=> $sitename
		);
		$alipaySubmit = new Alipaysubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter);
		return $html_text;
	}

	/**
	 * 异步回调状态
	 * @return [type] [description]
	 */
	public function notify_pay(){

		$alipayNotify = new Alipaynotify($this->payConfigArr());
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result) {

			$out_trade_no = Request::instance()->get('out_trade_no');

			//彩虹易支付交易号

			$trade_no = Request::instance()->get('trade_no');

			//交易状态
			$trade_status = Request::instance()->get('trade_status');

			//支付方式
			$type = Request::instance()->get('type');


			if (Request::instance()->get('trade_status') == 'TRADE_SUCCESS') {
				$payStatus = $this->get_paystatus($out_trade_no);
				if($payStatus===false){
					echo '没有找到该订单，请联系管理员';
				}
				$this->payStatus($payStatus);
				$pay_type = $this->textTONum($type);
				$payInfo = $this->get_payinfo($out_trade_no);
				if(!$payInfo){
					echo 'error';
				}
				Db::name('pay')->where('out_trade_no',$out_trade_no)->update(['status'=>1,'ptime'=>time(),'type'=>$pay_type]);

				Pub::billSet($payInfo['userid'],$payInfo['money'],'在线充值'.$payInfo['money'].'元',1);

				Pub::balance($payInfo['userid'],$payInfo['money'],$type=1);

				echo "success";
			}else{
				echo 'error';
			}
		}else{
			echo 'error';
		}
	}

	/**
	 * 跳转回调支付状态
	 * @return [type] [description]
	 */
	public function return_pay(){
		
		$alipayNotify = new Alipaynotify($this->payConfigArr());
		$verify_result = $alipayNotify->verifyReturn();
		if($verify_result) {

			$out_trade_no = Request::instance()->get('out_trade_no');

			//彩虹易支付交易号

			$trade_no = Request::instance()->get('trade_no');

			//交易状态
			$trade_status = Request::instance()->get('trade_status');

			//支付方式
			$type = Request::instance()->get('type');


			if (Request::instance()->get('trade_status') == 'TRADE_SUCCESS') {
				$payStatus = $this->get_paystatus($out_trade_no);
				if($payStatus===false){
					echo '没有找到该订单，请联系管理员';
				}
				$this->payStatus($payStatus);
				$pay_type = $this->textTONum($type);
				$payInfo = $this->get_payinfo($out_trade_no);
				if(!$payInfo){
					echo 'error';
				}
				// Db::name('pay')->where('out_trade_no',$out_trade_no)->update(['status'=>1,'ptime'=>time(),'type'=>$pay_type]);
				Pub::billSet($this->UserInfo['id'],$payInfo['money'],'在线充值'.$payInfo['money'],1);

				Pub::balance($this->UserInfo['id'],$payInfo['money'],$type=1);
				Db::name('pay')->where('out_trade_no',$out_trade_no)->update(['status'=>1,'ptime'=>time(),'type'=>$pay_type]);
				echo "success";
			}else{
				echo 'error';
			}
		}else{
			echo 'error';
		}
	}


	/**
	 * 获取支付配置数据
	 * @return [type] [description]
	 */
	function get_payconfig(){
		if($payconfigFind = Db::name('payconfig')->where('id',1)->find()){
			return $payconfigFind;
		}else{
			return ;
		}
	}

	/**
	 * 获取订单号支付状态
	 * @param  [type] $out_trade_no [description]
	 * @return [type]               [description]
	 */
	function get_paystatus($out_trade_no){
		
		$payFind = Db::name('pay')->where('out_trade_no',$out_trade_no)->find();
		if(!$payFind){
			return false;
		}else{
			return $payFind['status'];
		}
	}

	/**
	 * 获取订单信息
	 * @param  [type] $out_trade_no [description]
	 * @return [type]               [description]
	 */
	function get_payinfo($out_trade_no){
		if($payinfo = Db::name('pay')->where('out_trade_no',$out_trade_no)->find()){
			return $payinfo;
		}else{
			return false;
		}
	}

	

	/**
	 * 获取ajax请求的充值参数
	 * @return [type] [description]
	 */
	function getPayNum(){
		if(Request::instance()->get('key')>1){
			return Tools::buildFailed('-1','非法操作');
		}
		$pay_money = Request::instance()->post('pay_money');
		if(!$pay_money){
			return Tools::buildFailed('-1','充值金额不能为空');
		}
		if(!preg_match("/^[1-9][0-9]*$/",$pay_money)){
			return Tools::buildFailed('-1','最低一元');
		}
		switch ($type_pay = Request::instance()->post('pay_type')) {
			case 'alipay':
			$pay_type = '支付宝';
			break;
			case 'qqpay':
			$pay_type = 'QQ钱包';
			break;
			case 'wxpay':
			$pay_type = '微信支付';
			break;
			case 'tenpay':
			$pay_type = '财付通';
			break;

			default:
			return Tools::buildFailed('-1','非法请求');
			break;
		}
		if($this->status[$type_pay]==1){
			$name = Session::get('webname')?Session::get('webname'):'Bty';
			$payArr = [
				'userid'=>$this->UserInfo['id'],
				'money'=>$pay_money,
				'ctime'=>time(),
				'out_trade_no'=>date("YmdHis").mt_rand(100,999),
				'name'=>$name.'充值'.$pay_money,
				'type'=>$type_pay,
			];
			if($payId = Db::name('pay')->insertGetId($payArr)){
				return Tools::buildSuccess('200','下单成功，请完成在线支付操作',['out_trade_no'=>$payArr['out_trade_no'],'htmlCode'=>$this->epay_pay($payArr)]);
			}
		}else{
			return Tools::buildFailed('-1','当前支付方式暂未开启');
		}
	}

	/**
	 * 支付类别转数字
	 * @param  [type] $type [description]
	 * @return [type]       [description]
	 */
	public function textTONum($type){
		switch ($type) {
			case 'alipay':
			$pay_type = '1';
			break;
			case 'qqpay':
			$pay_type = '2';
			break;
			case 'wxpay':
			$pay_type = '3';
			break;
			case 'tenpay':
			$pay_type = '4';
			break;

			default:
			$pay_type = '0';
			break;
		}
		return $pay_type;
	}

	/**
	 * 订单状态
	 * @param  [type] $payStatus [description]
	 * @return [type]            [description]
	 */
	public function payStatus($payStatus){
		if($payStatus>0){
			switch ($payStatus) {
				case '1':
				exit('已充值成功');
				break;
				case '2':
				exit('充值已取消');
				break;
				case '3':
				exit('订单异常');
				break;
			}
		}
	}

	private function payConfigArr(){
		//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
		//商户ID
		$alipay_config['partner']		= $this->partner;

		//商户KEY
		$alipay_config['key']			= $this->key;


		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


		//签名方式 不需修改
		$alipay_config['sign_type']    = $this->sign_type;

		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset']= $this->input_charset;

		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipay_config['transport']    = $this->transport;

		//支付API地址
		$alipay_config['apiurl']    = $this->apiurl;
		return $alipay_config;
	}

}