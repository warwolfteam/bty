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

class Shop extends Controller{
  private $UserInfo = '';
  public function __construct(){
    $this->UserInfo = Login::isUser();
    if(!$this->UserInfo){
      $this->error('未登录，请先登录','/user/login.html');
    };
  }

  public function index(){
    $getSeach = Request::instance()->get();
    $local = @$getSeach['local'];
    $tag = @$getSeach['tag'];

    $key = $tag ? $tag : $local;
    $type = $tag ? 'tag' : 'local';

    $hostList = $this->hostSeach($key,$type);
    $localList = $this->localList();
    $hotHost = $this->host();
    $hostTag = $this->tag();
    $colorTag = ['default','success','info','warning','primary','danger'];

    $view = new View();
    $view->assign('colorTag',$colorTag);
    $view->assign('hostTag',$hostTag);
    $view->assign('hotHost',$hotHost);
    $view->assign('userInfo',$this->UserInfo);
    $view->assign('localList',array_unique($localList));
    $view->assign('hostList',$hostList);

    return $view->fetch();
  }

  private function hostSeach($key='',$type='local'){
    $hostLocal = Db::name('host')->where('status',1)->where($type,'like','%'.$key.'%')->select();
    return $hostLocal;
  }

  private function local($search=''){
    $hostLocal = Db::name('host')->where('status',1)->where('local','like','%'.$search.'%')->select();
    return $hostLocal;
  }

  private function tag(){
    $hostTag = Db::name('host')->where('status',1)->column('tag');
    $tagArr =  [];
    foreach ($hostTag as $key => $value) {
      $v = explode(',', $hostTag[$key]);
      $tagArr = array_merge($tagArr,$v);
    }
    $tagArr = array_unique($tagArr);
    return $tagArr;
  }

  private function localList(){
    $localList = Db::name('host')->where('status',1)->column('local');
    return $localList;
  }

  private function host(){
    $hotHost = Db::name('host')->where('status',1)->where('hot',1)->select();
    return $hotHost;
  }

  public function buy($id){
    if(!preg_match("/^[1-9][0-9]*$/",$id)){
      return $this->error('非法请求','/user/shop.html');
    }
    $hostFind = Db::name('host')->where('id',$id)->where('status',1)->find();
    if(!$hostFind){
      return $this->error('主机不存在','/user/shop.html');
    }
    if(Request::instance()->isAjax()){
      $quantity = Request::instance()->post('quantity');
      if(!preg_match("/^[1-9][0-9]*$/",$quantity)){
        return Tools::buildFailed('-1','非法请求');
      }
      return $this->buyHost($hostFind,$quantity);
    }else{
      $userGroup = Db::name('usergroup')->where('id',$this->UserInfo['usergroup'])->find();
      $view = new View();
      $view->assign('userGroup',$userGroup);
      $view->assign('hostInfo',$hostFind);
      $view->assign('userInfo',$this->UserInfo);

      $view->hostTag = explode(',', $hostFind['tag']);
      $view->service = explode(',', $hostFind['service']);
      return $view->fetch();
    }
  }

  private function buyHost($hostInfo,$quantity){
    $user = $this->UserInfo;
    $discount = $this->getDiscount();
    $shouldment = $hostInfo['money']*$quantity;
    //支付金额 = 主机单价 x 月份 - 用户组优惠金额
    $payment = ($shouldment-$discount) >0 ? $hostInfo['money']*$quantity-$discount : 0;
    $orderArr = array(
      'userid' => $user['id'],
      'hostid' => $hostInfo['id'],
      'hostip' => $hostInfo['ip'],
      'quantity' => $quantity,
      'shouldment' => $shouldment,
      'discount' => $discount,//优惠金额暂留
      'status' => 0,//订单创建，但未支付
      'ctime' => time(),
    );
    $orderUp = Db::name('order')->insert($orderArr);
    $orderId = Db::name('order')->getLastInsID();

    if(!$orderUp){
      return Tools::buildFailed('-1','订单写入失败，请重新购买');
    }
    
    if($user['money']<$payment){
      //余额不足但订单已创建，跳转到订单列表
      return Tools::buildFailed('-2','余额不足');
    }
    
    if($payment!=0){
      $symoney = $user['money']-$payment;
      $syUp = Db::name('user')->where('id',$user['id'])->setField('money',$symoney);
      if(!$syUp){
        return Tools::buildFailed('-2','扣款失败');
      }
    }
    
    $quantity = $quantity*30;
    Pub::billSet($user['id'],$payment,'购买主机：'.$hostInfo['name'].' '.$quantity.'天');
    // 更新订单状态为已支付
    $orderstatusUp = Db::name('order')->where('id',$orderId)->update(['status'=>1,'paytime'=>time(),'payment'=>$payment]);
    
    if(!$orderstatusUp){
      return Tools::buildFailed('-2','付款成功，但订单状态更新失败，请联系管理员进行处理');
    }
    //这里使用宝塔操作生成主机
    $Hostbuild = new Hostbuild();
    return $Hostbuild->btSet($orderId);
  }

  /**
   * 获取用户所在用户组优惠金额
   * @return [type] [description]
   */
  private function getDiscount(){
    return Db::name('usergroup')->where('id',$this->UserInfo['usergroup'])->value('discount');
  }

  /**
   * 续费管理
   * @param  [type] $id [description]
   * @return [type]     [description]
   */
  public function renew($id){
    if(!preg_match("/^[1-9][0-9]*$/",$id)){
      return $this->error('非法请求','/user/order.html');
    }
    $bthostFind = Db::name('hostlist')->where('id',$id)->where('userid',$this->UserInfo['id'])->find();
    if(!$bthostFind){
      return $this->error('服务器不存在','/user/order.html');
    }

    $hostFind = Db::name('host')->where('id',$bthostFind['hostid'])->where('status',1)->find();
    if(!$hostFind){
      return $this->error('主机不存在','/user/order.html');
    }

    if(Request::instance()->isAjax()){
      $quantity = Request::instance()->post('quantity');
      if(!preg_match("/^[1-9][0-9]*$/",$quantity)){
        return Tools::buildFailed('-1','非法请求');
      }
      return $this->renewHost($bthostFind,$hostFind,$quantity);
    }else{
      $userGroup = Db::name('usergroup')->where('id',$this->UserInfo['usergroup'])->find();
      $view = new View();
      $view->assign('bthostFind',$bthostFind);
      $view->assign('userGroup',$userGroup);
      $view->assign('hostInfo',$hostFind);
      $view->assign('userInfo',$this->UserInfo);

      $view->hostTag = explode(',', $hostFind['tag']);
      $view->service = explode(',', $hostFind['service']);

      return $view->fetch();
    }
  }

  /**
   * 续费服务器
   * @param  [type] $hostInfo [description]
   * @param  [type] $quantity [description]
   * @return [type]           [description]
   */
  private function renewHost($bthostFind,$hostInfo,$quantity){
    $user = $this->UserInfo;
    $discount = $this->getDiscount();
    //支付金额 = 主机单价 x 月份 - 用户组优惠金额
    $payment = ($hostInfo['money']*$quantity-$discount) >0 ? $hostInfo['money']*$quantity-$discount : 0;
    
    if($user['money']<$payment){
      return Tools::buildFailed('-2','余额不足');
    }
    
    if($payment!=0){
      $symoney = $user['money']-$payment;
      $syUp = Db::name('user')->where('id',$user['id'])->setField('money',$symoney);
      if(!$syUp){
        return Tools::buildFailed('-2','扣款失败,续费失败');
      }
    }
    $quantity = $quantity*30;
    if(Pub::billSet($user['id'],$payment,'续费主机：'.$hostInfo['name'].' '.$quantity.'天')){
      return $this->btSetTime($hostInfo['bturl'],$hostInfo['btoken'],$bthostFind,$quantity);
    }else{
      return Tools::buildFailed('-2','账单写入失败');
    }

  }

  /**
   * 连接宝塔进行时间修改
   * @param  [type] $btUrl    [description]
   * @param  [type] $bToken   [description]
   * @param  [type] $btId     [description]
   * @param  [type] $etime    [description]
   * @param  [type] $quantity [description]
   * @return [type]           [description]
   */
  private function btSetTime($btUrl,$bToken,$bthostFind,$quantity){

    $bt = new Bt($btUrl,$bToken);
    $etime = date('Y-m-d',$bthostFind['etime']);
    $endtime = strtotime("$etime+$quantity day");
    $ltime = date('Y-m-d',$endtime);

    $renewBt = $bt->WebSetEdate($bthostFind['btid'],$ltime);

    if(!$renewBt){
      return Tools::buildFailed('-1','网站时间设置失败');
    }
    if(Db::name('hostlist')->where('id',$bthostFind['id'])->where('userid',$this->UserInfo['id'])->update(['etime'=>$endtime])){
      return Tools::buildSuccess(200,'网站续费成功');
    }else{
      return Tools::buildFailed('-1','主机时间修改失败');
    }
    

  }

}