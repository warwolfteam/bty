# Bty1.0

#### 介绍
全网首款宝塔IDC分销系统
该版本为开源、如需付费版请联系QQ1170535111
我们尽可能为您提供最优质稳定的主机资源
QQ群：774688083
如果你觉得这个项目能够帮到你，欢迎fork和start
博客介绍：[https://www.youngxj.cn/577.html](https://www.youngxj.cn/577.html)

#### 宝塔活动
宝塔一键全能管理服务器，1分钱体验525.6元的插件，还有专业版奖励，立即一起来组队：[宝塔一分钱组团活动](https://www.bt.cn/team.html?MzY3MXp6)
参与我的队伍更有机会获得3个月专业版哦。


#### 软件架构
1. 本系统基于Thinkphp5开发完成
2. flatlab-bootstrap3

#### 参与人员
1. Youngxj
2. 阿珏

#### 项目说明

该系统基于宝塔开放API底层控制器，进行主机控制，目前可以完成大部分主机操作功能如：新增主机、修改主机配置、删除主机、查找主机、以及宝塔面板能开放使用的主机操作功能，由于宝塔面板的单一用户性，所以开发这款可以由个人操作的IDC分销系统。

#### 项目特色

1. 独立的用户管理、后台配置
2. 使用宝塔API接入服务器可实现正常的网站操作，实现网站开通、域名绑定、ssl证书、防盗链、一键部署、网站防篡改、网站监控报表、防火墙等功能
3. 配合宝塔强大的生态系统，安装更多插件后可使用到许多有趣的功能
4. 内置彩虹易支付，简单方便完成会员充值操作


#### 安装教程

1. 上传并解压源码到网站根目录(暂不支持二级目录)
2. 上传并导入数据库文件bty.sql
3. 修改application/database.php中数据库信息

		// 服务器地址
		'hostname'        => '',
		// 数据库名
		'database'        => '',
		// 用户名
		'username'        => '',
		// 密码
		'password'        => '',

4. 将运行目录设置为/public
5. 添加Thinkphp伪静态规则

	Apche:

		<IfModule mod_rewrite.c>
		Options +FollowSymlinks -Multiviews
		RewriteEngine on
		RewriteCond %{REQUEST_FILENAME} !-d
		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteRule ^(.*)$ index.php?/$1 [QSA,PT,L]
		</IfModule>

	Nginx:

		location / {
		if (!-e $request_filename){
			rewrite  ^(.*)$  /index.php?s=$1  last;   break;
			}
		}

	IIS:

		<rewrite>
		<rules>
		<rule name="OrgPage" stopProcessing="true">
		<match url="^(.*)$" />
		<conditions logicalGrouping="MatchAll">
		<add input="{HTTP_HOST}" pattern="^(.*)$" />
		<add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
		<add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
		</conditions>
		<action type="Rewrite" url="index.php/{R:1}" />
		</rule>
		</rules>
		</rewrite>

#### 宝塔服务器对接教程

1. 宝塔安装：请在分销服务器中安装宝塔最新面板 （[宝塔官网](https://www.bt.cn/?invite_code=MV93YXNpa2I=) | [注册](https://www.bt.cn/register.html?referee=3671)）
2. 环境安装：当面板安装完成后请登录网页版宝塔面板管理后台进行环境的安装，小杰推荐如下配置 Centos7.2 | PHP5.3-7.3 | Mysql5.5 | Redis4.0.9 | Nginx1.14.0 | Pure-Ftpd 1.0.47 | phpMyAdmin 4.4 | 宝塔一键部署源码 1.1
3. 宝塔Api密钥：宝塔面板-API接口-开启并获取接口密钥-填写IP白名单为当前分销系统搭建的服务器IP
4. 测试域名：必填！！！！！需要泛域名解析一个顶级域名到该服务器IP上，填写到分销系统主机管理-添加主机-测试域名中
5. 数据库管理地址：宝塔面板-数据库-phpMyAdmin，然后复制地址http://x.x.x.x:888/phpmyadmin_xxxxxxxxxx/index.php填写到分销系统主机管理-添加主机-数据库管理地址中
6. 性能标签：在添加服务器时请按照标准信息进行填写，否则容易出现一些意想不到的问题。描述主机性能尽量使用2G4H6M等简明扼要的关键词
7. 环境标签：服务器安装及已安装的插件环境，描述主机环境尽量使用Centos7.2 PHP5.3-7.3等简明扼要的关键词
8. 提醒：由于宝塔是未限制空间大小制度，所以目前不能限制用户空间及数据库的使用大小。
9. 如果遇到主机开通失败报错，请先检查主机信息是否正确，之后确认本系统运行的IP是否在API接口白名单中。

#### 使用说明
0. PHP 版本要求: PHP 5 >= 5.5.0, PHP 7
1. 后台地址为/admin(暂不支持修改)，请遵循后台首页管理员须知进行主机的添加
2. 后台账号：admin 密码：admin000
3. 修改系统设置中的站点域名
4. 本系统由 Youngxj 编写，请遵守开源协议使用守则，允许二次开发使用。
5. 销售主机的服务器上请安装最新版宝塔面板（[宝塔官网](https://www.bt.cn/?invite_code=MV93YXNpa2I=) | [注册](https://www.bt.cn/register.html?referee=3671)）
6. 本系统暂不支持空间大小、数据库大小、流量使用总量控制

#### 项目截图

![后台管理](https://images.gitee.com/uploads/images/2019/0318/114722_56d7086c_1511092.jpeg)
![主机控制](https://images.gitee.com/uploads/images/2019/0318/114722_c9594554_1511092.jpeg)

#### 付费版预览

![后台管理](https://images.gitee.com/uploads/images/2019/0317/160129_f134dd53_1511092.jpeg)

