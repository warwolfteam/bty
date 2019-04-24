<?php 
use think\Route;


Route::miss('miss/index');

$afterBehavior = [
	'\app\pub\Site',
];

Route::group('user', function () use ($afterBehavior) {
	Route::rule('login','user/login/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('retrieve','user/retrieve/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('reset_pwd','user/retrieve/reset_pwd', 'get', ['after_behavior' => $afterBehavior]);
	Route::rule('logout','user/login/logout', 'get', ['after_behavior' => $afterBehavior]);
	Route::rule('register','user/register/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('verify','user/login/verify', 'get', ['after_behavior' => $afterBehavior]);
	Route::rule('index','user/index/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('shop','user/shop/index', 'get', ['after_behavior' => $afterBehavior]);
	Route::rule('buy/:id','user/shop/buy', 'get|post', ['after_behavior' => $afterBehavior],['id' => '\d+']);
	Route::rule('order','user/order/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('host/:id','user/order/host', 'get', ['after_behavior' => $afterBehavior]);
	Route::rule('pay','user/pay/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('notify_pay','user/pay/notify_pay', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('return_pay','user/pay/return_pay', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('ajax_pay','user/pay/ajax_pay', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('getpaynum','user/pay/getpaynum', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('rlogin/:id','user/index/rootLogin', 'get', ['after_behavior' => $afterBehavior]);
	Route::rule('renew/:id','user/shop/renew', 'get|post', ['after_behavior' => $afterBehavior],['id' => '\d+']);
	Route::rule('/','user/index/index', 'get|post', ['after_behavior' => $afterBehavior]);

});

Route::group('admin', function () use ($afterBehavior) {

	Route::rule('index','admin/index/index', 'GET', ['after_behavior' => $afterBehavior]);
	Route::rule('login','admin/login/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('verify','admin/login/verify', 'get', ['after_behavior' => $afterBehavior]);
	Route::rule('logout','admin/login/logout', 'get', ['after_behavior' => $afterBehavior]);
	Route::rule('group','admin/group/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('host','admin/host/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('edit/:id','admin/host/edit', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('delete/:id','admin/host/delete', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('inc','admin/host/inc', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('setting','admin/setting/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('user','admin/user/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('info/:id','admin/user/info', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('upadmin','admin/setting/upadmin', 'post', ['after_behavior' => $afterBehavior]);
	Route::rule('upwebinfo','admin/setting/upwebinfo', 'post', ['after_behavior' => $afterBehavior]);
	Route::rule('upsmtp','admin/setting/upsmtp', 'post', ['after_behavior' => $afterBehavior]);
	Route::rule('upepayinfo','admin/setting/Upepayinfo', 'post', ['after_behavior' => $afterBehavior]);
	Route::rule('redeem','admin/redeem/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('order','admin/order/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('/','admin/index/index', 'GET', ['after_behavior' => $afterBehavior]);
});

Route::group('index', function () use ($afterBehavior) {
	Route::rule('/','index/index/index', 'GET', ['after_behavior' => $afterBehavior]);
});

Route::rule('/','index/index/index', 'GET', ['after_behavior' => $afterBehavior]);


Route::group('vhost', function () use ($afterBehavior) {
	Route::rule('deployment','vhost/index/deployment', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('WebSiteStop','vhost/index/WebSiteStop', 'get|post', ['after_behavior' => $afterBehavior]);

	Route::rule('WebSiteStart','vhost/index/WebSiteStart', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('phpSet','vhost/index/phpSet', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('login','vhost/login/index', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('logout','vhost/login/logout', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('index','vhost/index/index', 'get', ['after_behavior' => $afterBehavior]);
	Route::rule('logout','vhost/login/logout', 'get', ['after_behavior' => $afterBehavior]);
	Route::rule('domain','vhost/index/domain', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('pass','vhost/index/pass', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('speed','vhost/index/speed', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('file','vhost/index/file', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('r301','vhost/index/Rewrite301', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('rewrite','vhost/index/rewrite', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('siteback','vhost/index/siteback', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('ftp','vhost/index/ftp', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('sql','vhost/index/sql', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('sqlback','vhost/index/sqlback', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('ssl','vhost/index/ssl', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('protection','vhost/index/protection', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('sitelog','vhost/index/sitelog', 'get|post', ['after_behavior' => $afterBehavior]);
	Route::rule('httpauth','vhost/index/httpauth', 'get|post', ['after_behavior' => $afterBehavior]);

	Route::rule('/','vhost/index/index', 'get|post', ['after_behavior' => $afterBehavior]);
});
