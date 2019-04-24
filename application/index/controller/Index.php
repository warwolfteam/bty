<?php
namespace app\index\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use think\Config;
use app\user\controller\Login;

class Index
{

    private $UserInfo = '';

    public function __construct(){

       $this->UserInfo = Login::isUser();
   }
   public function index()
   {

       $hostList = Db::name('host')->where('status',1)->where('expiretime','>',time())->select();
       $hostTag = array();
       foreach ($hostList as $key => $value) {
          $hostList[$key]['tags'] = explode(',', $hostList[$key]['tag']);
      }
      $view = new View();
      $view->assign('UserInfo',$this->UserInfo);
      $view->assign('hostList',$hostList);
      //$view->assign('hostTag',$hostTag);
      return $view->fetch();
  }
}
