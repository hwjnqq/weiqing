<?php
/**
 * 微擎外送模块
 *
 * @author 微擎团队&灯火阑珊
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class We7_wmallModule extends WeModule {
	public function settingsDisplay($settings) {
		global $_W, $_GPC;
		if(checksubmit('submit')) {
			$title = trim($_GPC['title']) ? trim($_GPC['title']) : message('请填写系统标题');
			$public_tpl = trim($_GPC['notice']['public_tpl']) ? trim($_GPC['notice']['public_tpl']) : message('微信模板通知id不能为空');
			$config = array(
				'title' => $title,
				'notice' => array(
					'public_tpl' => $public_tpl,
				),
				'version' => intval($_GPC['version']) ? intval($_GPC['version']) : 1,
				'default_sid' => intval($_GPC['default_sid']),
			);
			$this->saveSettings($config);
			message('配置参数更新成功！', referer(), 'success');
		}
		$config = $this->module['config'];
		$stores = pdo_getall('tiny_wmall_store', array('uniacid' => $_W['uniacid']), array('title', 'id'));
		include $this->template('settings');
	}
}