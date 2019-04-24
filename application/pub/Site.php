<?php 
namespace app\pub;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use think\Session;
use think\View;
use app\util\Tools;

class Site{

	public function run(){
		$webInfo = Db::name('webinfo')->find();
		define('WEB_NAME',$webInfo['webname']);
		define('WEB_KEY',$webInfo['webkey']);
		define('WEB_DES',$webInfo['webdes']);
	}
}