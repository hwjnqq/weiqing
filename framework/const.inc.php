<?php 
/**
 * 验证规则
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

define('REGULAR_EMAIL', '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/i');
define('REGULAR_MOBILE', '/^1\d{10}$/');
define('REGULAR_USERNAME', '/^[\x{4e00}-\x{9fa5}a-z\d_\.]{3,15}$/iu');
/*
 * 模板引用相关
 */
//导入全局变量，并直接显示模板页内容。
define('TEMPLATE_DISPLAY', 0);
//导入全局变量，并返回模板页内容的字符串
define('TEMPLATE_FETCH', 1);
//返回模板编译文件的包含路径
define('TEMPLATE_INCLUDEPATH', 2);

//订阅号
define('ACCOUNT_SUBSCRIPTION', 1);
//订阅号-认证
define('ACCOUNT_SUBSCRIPTION_VERIFY', 3);
//服务号
define('ACCOUNT_SERVICE', 2);
//服务号-认证
define('ACCOUNT_SERVICE_VERIFY', 4);
//正常接入公众号
define('ACCOUNT_TYPE_OFFCIAL_NORMAL', 1);
//授权接入公众号
define('ACCOUNT_TYPE_OFFCIAL_AUTH', 3);
//正常接入小程序
define('ACCOUNT_TYPE_APP_NORMAL', 4);

//DIY小程序
define('WXAPP_DIY', 1);
//选择模版小程序
define('WXAPP_TEMPLATE', 2);
//跳转模块小程序
define('WXAPP_MODULE', 3);

//授权登录接入
define('ACCOUNT_OAUTH_LOGIN', 3);
//api接入
define('ACCOUNT_NORMAL_LOGIN', 1);

define('WEIXIN_ROOT', 'https://mp.weixin.qq.com');

//系统线上操作
define('ACCOUNT_OPERATE_ONLINE', 1);
//管理员操作
define('ACCOUNT_OPERATE_MANAGER', 2);
//店员操作
define('ACCOUNT_OPERATE_CLERK', 3);
//操作员
define('ACCOUNT_MANAGE_TYPE_OPERATOR', 1);
define('ACCOUNT_MANAGE_NAME_OPERATOR', 'operator');
//管理员
define('ACCOUNT_MANAGE_TYPE_MANAGER', 2);
define('ACCOUNT_MANAGE_NAME_MANAGER', 'manager');
//所有者
define('ACCOUNT_MANAGE_TYPE_OWNER', 3);
define('ACCOUNT_MANAGE_NAME_OWNER', 'owner');
//创始人
define('ACCOUNT_MANAGE_NAME_FOUNDER', 'founder');
//系统卡券
define('SYSTEM_COUPON', 1);
//微信卡券
define('WECHAT_COUPON', 2);
//卡券类型
define('COUPON_TYPE_DISCOUNT', '1');//折扣券
define('COUPON_TYPE_CASH', '2');//代金券
define('COUPON_TYPE_GROUPON', '3');//团购券
define('COUPON_TYPE_GIFT', '4');//礼品券
define('COUPON_TYPE_GENERAL', '5');//优惠券
define('COUPON_TYPE_MEMBER', '6');//会员卡
define('COUPON_TYPE_SCENIC', '7');//景点票
define('COUPON_TYPE_MOVIE', '8');//电影票
define('COUPON_TYPE_BOARDINGPASS', '9');//飞机票
define('COUPON_TYPE_MEETING', '10');//会议票
define('COUPON_TYPE_BUS', '11');//汽车票

define('ATTACH_FTP', 1);//远程附件类型：ftp
define('ATTACH_OSS', 2);//远程附件类型：阿里云
define('ATTACH_QINIU', 3);//远程附件类型：七牛
define('ATTACH_COS', 4);//远程附件类型：腾讯云对象存储

define('ATTACH_TYPE_IMAGE', 1);
define('ATTACH_TYPE_VOICE', 2);
define('ATTACH_TYPE_VEDIO', 3);
define('ATTACH_TYPE_NEWS', 4);

define('ATTACH_SAVE_TYPE_FIXED', 1);
define('ATTACH_SAVE_TYPE_TEMP', 2);

define('STATUS_OFF', 0); //关闭状态
define('STATUS_ON', 1); //开启状态
define('STATUS_SUCCESS', 0); //ajax返回成功状态，增强语义

define('CACHE_EXPIRE_SHORT', 60);
define('CACHE_EXPIRE_MIDDLE', 300);
define('CACHE_EXPIRE_LONG', 3600);
define('CACHE_KEY_LENGTH', 100); //缓存键的最大长度

define('CACHE_KEY_MODULE_SETTING', 'module_setting:%s:%s');
define('CACHE_KEY_MODULE_INFO', 'module_info:%s');
define('CACHE_KEY_ACCOUNT_MODULES', 'unimodules:%s:%s');
define('CACHE_KEY_ACCOUNT_MODULES_BINDING', 'unimodules:binding:%s');
define('CACHE_KEY_MEMBER_INFO', 'memberinfo:%s');
define('CACHE_KEY_UNI_GROUP', 'uni_group');
define('CACHE_KEY_ACCOUNT_SWITCH', 'lastaccount:%s');

define('MODULE_SUPPORT_WXAPP', 2);
define('MODULE_SUPPORT_ACCOUNT', 2);

//权限类型
define('PERMISSION_ACCOUNT', 'system');
define('PERMISSION_WXAPP', 'wxapp');
define('PERMISSION_SYSTEM', 'site');