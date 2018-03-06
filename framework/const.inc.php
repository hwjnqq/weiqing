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
//服务号-认证 认证媒体/政府订阅号
define('ACCOUNT_SERVICE_VERIFY', 4);
//正常接入公众号
define('ACCOUNT_TYPE_OFFCIAL_NORMAL', 1);
//授权接入公众号
define('ACCOUNT_TYPE_OFFCIAL_AUTH', 3);
//正常接入小程序
define('ACCOUNT_TYPE_APP_NORMAL', 4);
//正常接入PC
define('ACCOUNT_TYPE_WEBAPP_NORMAL', 5);
//正常接入APP
define('ACCOUNT_TYPE_PHONEAPP_NORMAL', 6);
//授权接入小程序
define('ACCOUNT_TYPE_APP_AUTH', 7);


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

//店员
define('ACCOUNT_MANAGE_NAME_CLERK', 'clerk');
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
define('ACCOUNT_MANAGE_GROUP_FOUNDER', 1);
//副创始人
define('ACCOUNT_MANAGE_TYPE_VICE_FOUNDER', 4);
define('ACCOUNT_MANAGE_NAME_VICE_FOUNDER', 'vice_founder');
define('ACCOUNT_MANAGE_GROUP_VICE_FOUNDER', 2);
//普通用户
define('ACCOUNT_MANAGE_GROUP_GENERAL', 0);
define('ACCOUNT_MANAGE_NAME_UNBIND_USER', 'unbind_user');
//admin创建用户，用户组 owner_uid=0
define('ACCOUNT_NO_OWNER_UID', 0);

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

define('ATTACHMENT_IMAGE', 'image');

define('ATTACH_SAVE_TYPE_FIXED', 1);
define('ATTACH_SAVE_TYPE_TEMP', 2);

define('STATUS_OFF', 0); //关闭状态
define('STATUS_ON', 1); //开启状态
define('STATUS_SUCCESS', 0); //ajax返回成功状态，增强语义

define('CACHE_EXPIRE_SHORT', 60);
define('CACHE_EXPIRE_MIDDLE', 300);
define('CACHE_EXPIRE_LONG', 3600);
define('CACHE_KEY_LENGTH', 100); //缓存键的最大长度

//模块配置信息
define('CACHE_KEY_MODULE_SETTING', 'module_setting:%s:%s');
//模块详细信息，包含 CACHE_KEY_MODULE_SETTING
define('CACHE_KEY_MODULE_INFO', 'module_info:%s');
//当前公众号及所有者可用的模块
define('CACHE_KEY_ACCOUNT_MODULES', 'unimodules:%s:%s');
//模块所有注册菜单
define('CACHE_KEY_ACCOUNT_MODULES_BINDING', 'unimodules:binding:%s');
//用户信息
define('CACHE_KEY_MEMBER_INFO', 'memberinfo:%s');
define('CACHE_KEY_UNI_GROUP', 'uni_group');
define('CACHE_KEY_ACCOUNT_SWITCH', 'lastaccount:%s');

//模块是否支持小程序
define('MODULE_SUPPORT_WXAPP', 2);
define('MODULE_NONSUPPORT_WXAPP', 1);
//模块是否支持公众号应用
define('MODULE_SUPPORT_ACCOUNT', 2);
define('MODULE_NONSUPPORT_ACCOUNT', 1);
//是否支持pc 1不支持  2支持
define('MODULE_NOSUPPORT_WEBAPP', 1);
define('MODULE_SUPPORT_WEBAPP', 2);
//是否支持app 1不支持  2支持
define('MODULE_NOSUPPORT_PHONEAPP', 1);
define('MODULE_SUPPORT_PHONEAPP', 2);
//是否支持系统首页 1不支持  2支持
define('MODULE_SUPPORT_SYSTEMWELCOME', 2);
define('MODULE_NONSUPPORT_SYSTEMWELCOME', 1);

//权限类型
define('PERMISSION_ACCOUNT', 'system');
define('PERMISSION_WXAPP', 'wxapp');
define('PERMISSION_SYSTEM', 'site');

//微信支付类型
define('PAYMENT_WECHAT_TYPE_NORMAL', 1);
define('PAYMENT_WECHAT_TYPE_BORROW', 2);
define('PAYMENT_WECHAT_TYPE_SERVICE', 3);
define('PAYMENT_WECHAT_TYPE_CLOSE', 4);

//平台给粉丝发消息的类型
define('FANS_CHATS_FROM_SYSTEM', 1);

//小程序数据常规分析常量
define('WXAPP_STATISTICS_DAILYVISITTREND', 2);
//DIY小程序
define('WXAPP_DIY', 1);
//选择模版小程序
define('WXAPP_TEMPLATE', 2);
//跳转模块小程序
define('WXAPP_MODULE', 3);
//网页小程序类型
define('WXAPP_CREATE_MODULE', 1);
// 打包多个模块小程序
define('WXAPP_CREATE_MUTI_MODULE', 2);
//普通小程序类型
define('WXAPP_CREATE_DEFAULT', 0);

define('MATERIAL_LOCAL', 'local');//服务器素材类型
define('MATERIAL_WEXIN', 'perm');//微信素材类型

//自定义菜单之默认菜单
define('MENU_CURRENTSELF', 1);
//自定义菜单之默认菜单的历史记录
define('MENU_HISTORY', 2);
//自定义菜单之个性化菜单
define('MENU_CONDITIONAL', 3);

//用户状态
//注册审核用户
define('USER_STATUS_CHECK', 1);
//正常用户
define('USER_STATUS_NORMAL', 2);
//禁用用户
define('USER_STATUS_BAN', 3);

//用户类型
//普通用户
define('USER_TYPE_COMMON', 1);
//店员
define('USER_TYPE_CLERK', 3);

//我的账户链接类型
define('PERSONAL_BASE_TYPE', 1);
define('PERSONAL_AUTH_TYPE', 2);
define('PERSONAL_LIST_TYPE', 3);

//商品类型
define('STORE_TYPE_MODULE', 1);
define('STORE_TYPE_ACCOUNT', 2);
define('STORE_TYPE_WXAPP', 3);
define('STORE_TYPE_WXAPP_MODULE', 4);
define('STORE_TYPE_PACKAGE', 5);
define('STORE_TYPE_API', 6);
define('STORE_TYPE_ACCOUNT_RENEW', 7);
define('STORE_TYPE_WXAPP_RENEW', 8);
//订单状态
define('STORE_ORDER_PLACE', 1);
define('STORE_ORDER_DELETE', 2);
define('STORE_ORDER_FINISH', 3);
//商品状态
define('STORE_GOODS_STATUS_OFFlINE', 0);
define('STORE_GOODS_STATUS_ONLINE', 1);
define('STORE_GOODS_STATUS_DELETE', 2);

//文章分类 一级分类和二级分类为0
define('ARTICLE_PCATE', 0);
define('ARTICLE_CCATE', 0);

//用户注册方式来源
//qq注册
define('USER_REGISTER_TYPE_QQ', 1);
//微信注册
define('USER_REGISTER_TYPE_WECHAT', 2);
//手机注册
define('USER_REGISTER_TYPE_MOBILE', 3);

//消息提醒类型
//订单消息类型
define('MESSAGE_ORDER_TYPE', 1);
//公众号过期消息类型
define('MESSAGE_ACCOUNT_EXPIRE_TYPE', 2);
//小程序过期消息类型
define('MESSAGE_WECHAT_EXPIRE_TYPE', 5);
//pc过期消息类型
define('MESSAGE_WEBAPP_EXPIRE_TYPE', 6);
//工单消息类型
define('MESSAGE_WORKORDER_TYPE', 3);
//注册消息类型
define('MESSAGE_REGISTER_TYPE', 4);
//用户账号到期
define('MESSAGE_USER_EXPIRE_TYPE', 7);
//小程序应用升级
define('MESSAGE_WXAPP_MODULE_UPGRADE', 8);

//消息是否读取 1未读  2已读
define('MESSAGE_NOREAD', 1);
define('MESSAGE_READ', 2);

//上传图片uniacid -1
define('FILE_NO_UNIACID', -1);

//模块获取用户授权方式 1.静默授权 2.用户有感知授权
define('OAUTH_TYPE_BASE', 1);
define('OAUTH_TYPE_USERINFO', 2);
