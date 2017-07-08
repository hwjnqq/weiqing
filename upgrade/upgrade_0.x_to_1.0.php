<?php
/**
 * 升级微擎1.0脚本
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../framework/bootstrap.inc.php';
require IA_ROOT . '/web/common/bootstrap.sys.inc.php';
require IA_ROOT . '/web/common/common.func.php';
require IA_ROOT . '/framework/library/pinyin/pinyin.php';
load()->func('file');

$pinyin = new Pinyin_Pinyin();

$change_permission_list = array(
	array(
		'original' => array('platform_reply_basic', 'platform_reply_news', 'platform_reply_music', 'platform_reply_images', 'platform_reply_voice', 'platform_reply_video', 'platform_reply_wxcard', 'platform_reply_userapi'),
		'new' => array('platform_reply')
	),
	array(
		'original' => array('platform_special'),
		'new' => array('platform_reply_special'),
	),
	array(
		'original' => array('platform_qr'),
		'new' => array('platform_qr', 'platform_url2qr'),
	),
	array(
		'original' => array('material_mass'),
		'new' => array('platform_mass_task'),
	),
	array(
		'original' => array('material_display', 'material_manage'),
		'new' => array('platform_material'),
	),
	array(
		'original' => array('mc_members_manage'),
		'new' => array('mc_member_page'),
	),
);

$users_permission = pdo_getall('users_permission', array('type' => 'system'));
if (!empty($users_permission)) {
	foreach ($users_permission as $user_permission) {
		$user_original_permission = $users_permission['permission'];
		$permission = explode('|', $user_permission['permission']);
		if (empty($permission) || !is_array($permission)) {
			$permission = array();
		}
		foreach ($change_permission_list as $change_permission) {
			//判断用户权限里是否有要改变的权限
			if (array_intersect($permission, $change_permission['original'])) {
				//去掉废弃的权限
				$permission = array_diff($permission, $change_permission['original']);
				//添加新的权限
				foreach ($change_permission['new'] as $new_permission) {
					$permission[] = $new_permission;
				}
			}
		}
		$permission = implode('|', $permission);
		if ($permission != $user_original_permission) {
			pdo_update('users_permission', array('permission' => $permission), array('id' => $user_permission['id']));
		}
	}
}

//添加图文素材的素材顺序字段
if (!pdo_fieldexists('wechat_news', 'displayorder')) {
	pdo_query('ALTER TABLE '. tablename('wechat_news')." ADD `displayorder` INT(2) NOT NULL DEFAULT '0';");
}

//转移模块快捷菜单数据到uni_account_modules表，废弃之前存在uni_settings中的shortcuts
//uni_settings表中存放于字段中，不能存放太多数据，也不方便修改
if (!pdo_fieldexists('uni_account_modules', 'shortcut')) {
	pdo_query("ALTER TABLE ".tablename('uni_account_modules')." ADD `shortcut` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';");
}
if (!pdo_fieldexists('uni_account_modules', 'displayorder')) {
	pdo_query("ALTER TABLE ".tablename('uni_account_modules')." ADD `displayorder` INT UNSIGNED NOT NULL DEFAULT '0';");
}
if (pdo_fieldexists('uni_settings', 'shortcuts')) {
	//pdo_query("ALTER TABLE ".tablename('uni_settings')." DROP `shortcuts`;");
}
//修改用户avater字段长度
if(pdo_fieldexists('users_profile', 'avatar')) {
	pdo_query("ALTER TABLE ".tablename('users_profile')." CHANGE `avatar` `avatar` VARCHAR(255) NOT NULL DEFAULT '';");
}
//新增系统管理->用户管理按修改时间排序
if(!pdo_fieldexists('users_profile', 'edittime')) {
	pdo_query("ALTER TABLE ". tablename('users_profile') ." ADD `edittime` INT(10) NOT NULL COMMENT '修改时间' AFTER `createtime`;");
}
$shortcuts = pdo_getall('uni_settings', array(), array('shortcuts', 'uniacid'));
if (!empty($shortcuts)) {
	foreach ($shortcuts as $row) {
		if (!empty($row['shortcuts'])) {
			$row['shortcuts'] = iunserializer($row['shortcuts']);
			if (!empty($row['shortcuts'])) {
				foreach ($row['shortcuts'] as $module) {
					$module_profile = pdo_get('uni_account_modules', array('module' => $module['name'], 'uniacid' => $row['uniacid']));
					if (!empty($module_profile)) {
						pdo_update('uni_account_modules', array('shortcut' => '1'), array('id' => $module_profile['id']));
					} else {
						$data = array(
							'uniacid' => $row['uniacid'],
							'module' => $module['name'],
							'enabled' => 1,
							'shortcut' => 1,
							'settings' => '',
						);
						pdo_insert('uni_account_modules', $data);
					}
				}
			}
		}
	}
}

//增加模块和公众号拼音索引
if (!pdo_fieldexists('modules', 'title_initial')) {
	pdo_query("ALTER TABLE ". tablename('modules') ." ADD `title_initial` VARCHAR(1) NOT NULL DEFAULT '';");
}
$modules = pdo_getall('modules', array(), array('name', 'mid', 'title'));
if (!empty($modules)) {
	foreach ($modules as $module) {
		$title = $pinyin->get_first_char($module['title']);
		pdo_update('modules', array('title_initial' => $title), array('mid' => $module['mid']));
	}
}

//uni_account是否存在letter字段，否则添加并更新（切换公众号拼音索引功能）
if(!pdo_fieldexists('uni_account', 'title_initial')) {
	$add_letter = pdo_query("ALTER TABLE ". tablename('uni_account') . " ADD `title_initial` VARCHAR(1) NOT NULL DEFAULT '';");
	if($add_letter) {
		$sql = '';
		$all_account = pdo_fetchall("SELECT uniacid,name FROM ". tablename('uni_account'));
		foreach ($all_account as $all_value) {
			$letter = '';
			$letter = $pinyin->get_first_char($all_value['name']);
			$sql .= "UPDATE ". tablename('uni_account'). " SET `title_initial` = '". $letter . "' WHERE `uniacid` = {$all_value['uniacid']};";
		}
		$run = pdo_run($sql);
	}
}

//切换公众号置顶功能
if(!pdo_fieldexists('uni_account', 'rank')) {
	pdo_query("ALTER TABLE ". tablename('uni_account') ." CHANGE `rank` `rank` INT(10) NULL DEFAULT '0';");
}

if (!pdo_fieldexists('core_menu', 'group_name')) {
	pdo_query("ALTER TABLE ". tablename('core_menu'). " ADD `group_name` VARCHAR(30) NOT NULL DEFAULT '';");
}

if (!pdo_fieldexists('core_menu', 'icon')) {
	pdo_query("ALTER TABLE ". tablename('core_menu'). " ADD `icon` VARCHAR(20) NOT NULL DEFAULT '';");
}
//增大缓存表字段长度
pdo_query("ALTER TABLE ". tablename('core_cache'). " CHANGE `value` `value` LONGTEXT NOT NULL;");

//自动回复功能调整
if(!pdo_fieldexists('rule', 'containtype')) {
	pdo_query("ALTER TABLE ". tablename('rule') ." ADD `containtype` VARCHAR(100) NOT NULL DEFAULT '';");
}

//删除文件
$delete_file = '["app\/source\/activity\/coupon.ctrl.php","app\/source\/activity\/goods.ctrl.php","app\/source\/mc\/card.ctrl.php","framework\/builtin\/basic\/preview-custom.jpg","framework\/builtin\/core\/module.cer","framework\/builtin\/paycenter\/inc\/mobile\/trading-record.inc.php","framework\/builtin\/reply\/icon.jpg","framework\/builtin\/reply\/index.html","framework\/builtin\/reply\/module.php","framework\/builtin\/reply\/preview.jpg","framework\/builtin\/reply\/processor.php","framework\/builtin\/reply\/template\/display.html","framework\/class\/laiwang.account.class.php","framework\/function\/global.func.php.orig","framework\/model\/coupon.mod.php","framework\/model\/wxapp.mod.php","web\/authorized_keys","web\/resource\/css\/common.wxapp.css","web\/resource\/images\/app\/wapeditor.js","web\/resource\/images\/bbb.jpg","web\/resource\/images\/jquery.minicolors.png","web\/resource\/js\/app\/district.js","web\/resource\/js\/app\/fileUploader.js","web\/resource\/js\/app\/wechatDistrict.js","web\/resource\/js\/app\/wechatFileUploader.js","web\/resource\/js\/app\/wechat_fileUploader.js","web\/root","web\/source\/account\/post_user.ctrl.php","web\/source\/account\/system_display.ctrl.php","web\/source\/system\/common.ctrl.php","web\/source\/activity\/clerk.ctrl.php","web\/source\/activity\/consume.ctrl.php","web\/source\/activity\/coupon.ctrl.php","web\/source\/activity\/exchange.ctrl.php","web\/source\/activity\/market.ctrl.php","web\/source\/activity\/store.ctrl.php","web\/source\/mc\/card.ctrl.php","web\/source\/mc\/creditrule.ctrl.php","web\/source\/paycenter\/wxmicro.ctrl.php","web\/source\/platform\/material_post.ctrl.php","web\/source\/system\/content-provider.ctrl.php","web\/source\/system\/module_group.ctrl.php","web\/source\/wxapp\/__init.php","web\/source\/wxapp\/account.ctrl.php","web\/source\/wxapp\/create.ctrl.php","web\/source\/wxapp\/manage.ctrl.php","web\/test.php","web\/test.rdb","web\/test2.rdb","web\/themes\/default\/account\/system-display.html","web\/themes\/default\/platform\/material_post.html","web\/themes\/default\/system\/module_group.html","web\/themes\/default\/system\/account.html","web\/themes\/default\/system\/select_module_group.html","web\/themes\/newstyle\/system\/sysinfo.html","app\/resource\/css\/common.css","app\/resource\/css\/mui.min.css","app\/resource\/images\/bg-banner.png","app\/resource\/js\/app\/moment.js","app\/resource\/js\/lib\/calendar.js","app\/resource\/js\/lib\/underscore-min.js","app\/source\/activity\/__init.php","framework\/builtin\/userapi\/api\/gold.php","framework\/builtin\/userapi\/api\/test.php","framework\/model\/frame.mod.php","web\/resource\/css\/bootstrap-theme.min.css","web\/resource\/css\/emoji.css","web\/resource\/css\/font-awesome.min.css","web\/resource\/fonts\/glyphicons-halflings-regular.eot","web\/resource\/fonts\/glyphicons-halflings-regular.svg","web\/resource\/fonts\/glyphicons-halflings-regular.ttf","web\/resource\/fonts\/glyphicons-halflings-regular.woff","web\/resource\/images\/bg_repno.gif","web\/resource\/images\/cancel-custom-off.png","web\/resource\/images\/cancel-custom-on.png","web\/resource\/images\/gw-bg.jpg","web\/resource\/images\/gw-logo.png","web\/resource\/images\/gw-qr.jpg","web\/resource\/images\/gw-yx.png","web\/resource\/images\/icon_audio.png","web\/resource\/images\/media.jpg","web\/resource\/images\/money.png","web\/resource\/images\/star-off-big.png","web\/resource\/images\/star-on-big.png","web\/resource\/images\/subscribe.gif","web\/resource\/js\/app\/biz.js","web\/resource\/js\/app\/config.js","web\/resource\/js\/app\/coupon.js","web\/resource\/js\/app\/domReady.js","web\/resource\/js\/app\/industry.js","web\/resource\/js\/app\/location.js","web\/resource\/js\/app\/material.js","web\/resource\/js\/app\/trade.js","web\/resource\/js\/app\/wapeditor.js","web\/resource\/js\/lib\/angular-sanitize.min.js","web\/resource\/js\/lib\/angular.min.js","web\/resource\/js\/lib\/bootstrap-filestyle.min.js","web\/resource\/js\/lib\/chart.min.js","web\/resource\/js\/lib\/json2.js","web\/resource\/js\/lib\/raty.min.js","web\/source\/account\/default.ctrl.php","web\/source\/account\/delete.ctrl.php","web\/source\/account\/groups.ctrl.php","web\/source\/account\/permission.ctrl.php","web\/source\/account\/summary.ctrl.php","web\/source\/account\/switch.ctrl.php","web\/source\/account\/welcome.ctrl.php","web\/source\/activity\/__init.php","web\/source\/activity\/desk.ctrl.php","web\/source\/activity\/module.ctrl.php","web\/source\/cloud\/device.ctrl.php","web\/source\/cloud\/diagnose.ctrl.php","web\/source\/cron\/__init.php","web\/source\/cron\/display.ctrl.php","web\/source\/cron\/entry.ctrl.php","web\/source\/extension\/__init.php","web\/source\/extension\/menu.ctrl.php","web\/source\/extension\/module.ctrl.php","web\/source\/extension\/platform.ctrl.php","web\/source\/extension\/service.ctrl.php","web\/source\/extension\/subscribe.ctrl.php","web\/source\/extension\/theme.ctrl.php","web\/source\/material\/__init.php","web\/source\/material\/display.ctrl.php","web\/source\/material\/mass.ctrl.php","web\/source\/material\/post.ctrl.php","web\/source\/mc\/broadcast.ctrl.php","web\/source\/mc\/credit.ctrl.php","web\/source\/mc\/fangroup.ctrl.php","web\/source\/mc\/fields.ctrl.php","web\/source\/mc\/group.ctrl.php","web\/source\/mc\/mass.ctrl.php","web\/source\/mc\/notice.ctrl.php","web\/source\/mc\/passport.ctrl.php","web\/source\/mc\/plugin.ctrl.php","web\/source\/mc\/tplnotice.ctrl.php","web\/source\/mc\/uc.ctrl.php","web\/source\/paycenter\/__init.php","web\/source\/paycenter\/card.ctrl.php","web\/source\/platform\/special.ctrl.php","web\/source\/profile\/deskmenu.ctrl.php","web\/source\/profile\/jsauth.ctrl.php","web\/source\/profile\/printer.ctrl.php","web\/source\/site\/info.ctrl.php","web\/source\/site\/solution.ctrl.php","web\/source\/stat\/__init.php","web\/source\/stat\/card.ctrl.php","web\/source\/stat\/cash.ctrl.php","web\/source\/stat\/credit1.ctrl.php","web\/source\/stat\/credit2.ctrl.php","web\/source\/stat\/paycenter.ctrl.php","web\/source\/system\/content_provider.ctrl.php","web\/source\/system\/account.ctrl.php","web\/source\/system\/cron.ctrl.php","web\/source\/system\/sysinfo.ctrl.php","web\/source\/system\/tools.ctrl.php","web\/source\/system\/welcome.ctrl.php","web\/source\/user\/permission.ctrl.php","web\/source\/user\/register.ctrl.php","web\/source\/utility\/bindcall.ctrl.php","web\/source\/utility\/icon.ctrl.php","web\/source\/utility\/checkattach.ctrl.php","web\/source\/utility\/checkupgrade.ctrl.php","web\/source\/utility\/code.ctrl.php","web\/source\/utility\/coupon.ctrl.php","web\/source\/utility\/emoji.ctrl.php","web\/source\/utility\/fans.ctrl.php","web\/source\/utility\/notice.ctrl.php","web\/source\/utility\/subscribe.ctrl.php","web\/source\/utility\/sync.ctrl.php","web\/source\/utility\/verifycode.ctrl.php","web\/themes\/default\/account\/groups.html","web\/themes\/default\/account\/guide.html","web\/themes\/default\/account\/permission.html","web\/themes\/default\/account\/post.html","web\/themes\/default\/account\/select.html","web\/themes\/default\/account\/summary.html","web\/themes\/default\/account\/welcome.html","web\/themes\/default\/article\/news-show.html","web\/themes\/default\/article\/notice-show.html","web\/themes\/default\/cloud\/device.html","web\/themes\/default\/cloud\/diagnose.html","web\/themes\/default\/common\/footer-cms.html","web\/themes\/default\/common\/footer-gw.html","web\/themes\/default\/common\/header-cms.html","web\/themes\/default\/common\/header-gw.html","web\/themes\/default\/cron\/display.html","web\/themes\/default\/extension\/designer.html","web\/themes\/default\/extension\/desitemp.html","web\/themes\/default\/extension\/menu.html","web\/themes\/default\/extension\/module-permission.html","web\/themes\/default\/extension\/module-tabs.html","web\/themes\/default\/extension\/module.html","web\/themes\/default\/extension\/permission.html","web\/themes\/default\/extension\/platform.html","web\/themes\/default\/extension\/post.html","web\/themes\/default\/extension\/select-account.html","web\/themes\/default\/extension\/select-groups.html","web\/themes\/default\/extension\/service-post.html","web\/themes\/default\/extension\/service-tabs.html","web\/themes\/default\/extension\/service.html","web\/themes\/default\/extension\/subscribe.html","web\/themes\/default\/extension\/switch.html","web\/themes\/default\/extension\/theme-tabs.html","web\/themes\/default\/extension\/theme.html","web\/themes\/default\/extension\/web.html","web\/themes\/default\/home\/welcome-mc.html","web\/themes\/default\/home\/welcome-platform.html","web\/themes\/default\/home\/welcome-setting.html","web\/themes\/default\/home\/welcome-site.html","web\/themes\/default\/home\/welcome-solution.html","web\/themes\/default\/material\/display.html","web\/themes\/default\/material\/mass.html","web\/themes\/default\/material\/post.html","web\/themes\/default\/material\/send.html","web\/themes\/default\/mc\/broadcast.html","web\/themes\/default\/mc\/coupon-model.html","web\/themes\/default\/mc\/credit.html","web\/themes\/default\/mc\/fansgroup.html","web\/themes\/default\/mc\/fields.html","web\/themes\/default\/mc\/group.html","web\/themes\/default\/mc\/notice.html","web\/themes\/default\/mc\/passport.html","web\/themes\/default\/mc\/plugin.html","web\/themes\/default\/mc\/tplnotice.html","web\/themes\/default\/mc\/trade.html","web\/themes\/default\/mc\/uc.html","web\/themes\/default\/paycenter\/payinfo.html","web\/themes\/default\/paycenter\/wechat.html","web\/themes\/default\/platform\/resource.html","web\/themes\/default\/platform\/service.html","web\/themes\/default\/platform\/special-display.html","web\/themes\/default\/platform\/special-message.html","web\/themes\/default\/platform\/stat-history.html","web\/themes\/default\/platform\/stat-keyword_hit.html","web\/themes\/default\/platform\/stat-keyword_miss.html","web\/themes\/default\/platform\/stat-keyword_search.html","web\/themes\/default\/platform\/stat-rule_hit.html","web\/themes\/default\/platform\/stat-rule_miss.html","web\/themes\/default\/platform\/stat-rule_search.html","web\/themes\/default\/platform\/stat-setting.html","web\/themes\/default\/platform\/stat-trend.html","web\/themes\/default\/profile\/deskmenu.html","web\/themes\/default\/profile\/jsauth.html","web\/themes\/default\/profile\/module_setting.html","web\/themes\/default\/profile\/permission.html","web\/themes\/default\/profile\/printer.html","web\/themes\/default\/profile\/work.html","web\/themes\/default\/site\/article.html","web\/themes\/default\/site\/category.html","web\/themes\/default\/site\/info.html","web\/themes\/default\/site\/multi.html","web\/themes\/default\/site\/slide.html","web\/themes\/default\/site\/solution.html","web\/themes\/default\/site\/style.html","web\/themes\/default\/stat\/card.html","web\/themes\/default\/stat\/cash.html","web\/themes\/default\/stat\/credit1.html","web\/themes\/default\/stat\/credit2.html","web\/themes\/default\/stat\/paycenter.html","web\/themes\/default\/system\/content_provider.html","web\/themes\/default\/system\/cron.html","web\/themes\/default\/system\/sysinfo.html","web\/themes\/default\/system\/welcome.html","web\/themes\/default\/user\/access.html","web\/themes\/default\/user\/edit.html","web\/themes\/default\/user\/fields.html","web\/themes\/default\/user\/group.html","web\/themes\/default\/user\/menu.html","web\/themes\/default\/user\/register.html","web\/themes\/default\/user\/select.html","web\/themes\/default\/utility\/emoji.html","web\/themes\/default\/utility\/icon.html","web\/themes\/default\/utility\/emulator.html","web\/themes\/default\/utility\/fans.html","web\/themes\/index.html","app\/source\/activity","web\/source\/activity","web\/source\/cron","web\/source\/extension","web\/source\/material","web\/source\/paycenter","web\/source\/stat","web\/themes\/default\/cron","web\/themes\/default\/cron","web\/themes\/default\/material","web\/themes\/default\/paycenter","web\/themes\/default\/stat","framework\/builtin\/reply"]';
$delete_file = json_decode($delete_file, true);
foreach ($delete_file as $file) {
	if (file_exists(IA_ROOT."/".$file)) {
		$patch_dir = IA_ROOT.'/data/patch/'. date('Ymd'). "/". date("Hi"). "_deletefile/";
		if (!is_dir(dirname($patch_dir. $file))) {
			mkdirs(dirname($patch_dir. $file));
		}
		if (!is_dir(IA_ROOT. "/". $file)) {
			$file_content = file_get_contents(IA_ROOT. "/". $file);
			file_put_contents($patch_dir. $file, $file_content);
			unlink(IA_ROOT."/".$file);
		} else {
			rmdir(IA_ROOT."/".$file);
		}
	}
}

//更新we7_coupon的is_system为2,强制开启模块
pdo_update('modules', array('issystem' => 2), array('name' => 'we7_coupon'));