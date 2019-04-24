<?php 
namespace app\index\controller;

use think\Controller;

class Miss extends Controller{

	function index(){
		return $this->error('地址异常');
	}
}