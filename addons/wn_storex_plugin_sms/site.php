<?php
/**
 * 万能小店短信服务模块
 *
 * @author 万能君
 * @url www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class Wn_storex_plugin_smsModuleSite extends WeModuleSite {

	public function doWebSmsset() {
		global $_W, $_GPC;
		$ops = array('post', 'display');
		$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

		if ($op == 'display') {
			$sms_info = pdo_get('storex_plugin_smsset', array('uniacid' => $_W['uniacid']));
			if (empty($sms_info)) {
				$sms_info['status'] = 1;
			}
		}

		if ($op == 'post') {
			if ($_W['ispost'] && $_W['isajax']) {
				$sms_post = $_GPC['params'];
				if (empty($sms_post['appkey']) || empty($sms_post['appsecret']) || empty($sms_post['sign'])) {
					message(error(-1, '请完整填写信息'), '', 'ajax');
				}
				$sms_info = pdo_get('storex_plugin_smsset', array('uniacid' => $_W['uniacid']), array('id'));
				if (!empty($sms_info)) {
					pdo_update('storex_plugin_smsset', $sms_post, array('id' => $sms_info['id']));
				} else {
					$sms_post['uniacid'] = $_W['uniacid'];
					pdo_insert('storex_plugin_smsset', $sms_post);
				}
				message(error(0, '设置成功'), '', 'ajax');
			}
		}

		include $this->template('smsset');
	}

	public function doWebSmsnotice() {
		global $_W, $_GPC;
		$ops = array('post', 'display', 'lists', 'log_delete');
		$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

		if ($op == 'display') {
			$sms_notice = pdo_get('storex_plugin_smsnotice', array('uniacid' => $_W['uniacid']));
			$sms_notice['notice'] = iunserializer($sms_notice['notice']);
			if (empty($sms_notice['notice'])) {
				$sms_notice['notice']['clerk']['status'] = 1;
				$sms_notice['notice']['user']['status'] = 1;
			}
		}

		if ($op == 'post') {
			if ($_W['ispost'] && $_W['isajax']) {
				$sms_post = $_GPC['params'];
				if (!empty($sms_post) && is_array($sms_post)) {
					foreach ($sms_post as $key => $value) {
						if (empty($value['sms_template_code']) || empty($value['status'])) {
							message(error(-1, '请完整填写信息'), '', 'ajax');
						}
					}
				}
				$sms_notice = pdo_get('storex_plugin_smsnotice', array('uniacid' => $_W['uniacid']), array('id'));
				if (!empty($sms_notice)) {
					pdo_update('storex_plugin_smsnotice', array('notice' => iserializer($sms_post)), array('id' => $sms_notice['id']));
				} else {
					pdo_insert('storex_plugin_smsnotice', array('notice' => iserializer($sms_post), 'uniacid' => $_W['uniacid']));
				}
				message(error(0, '设置成功'), '', 'ajax');
			}
		}

		if ($op == 'lists') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 10;
			$params = array(':uniacid' => $_W['uniacid']);
			$condition = " WHERE uniacid = :uniacid";
			if (!empty($_GPC['mobile'])) {
				$params[':mobile'] = trim($_GPC['mobile']);
				$condition .= " AND mobile = :mobile";
			}
			$sms_logs = pdo_fetchall("SELECT * FROM " . tablename('storex_plugin_sms_logs') . $condition . " ORDER BY time DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
			// $sms_logs = pdo_getall('storex_plugin_sms_logs', array('uniacid' => $_W['uniacid']), '', '', 'time DESC');
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('storex_plugin_sms_logs') . $condition, $params);
			$pager = pagination($total, $pindex, $psize);
			if (!empty($sms_logs) && is_array($sms_logs)) {
				foreach ($sms_logs as &$value) {
					if ($value['status'] == 2 && empty($value['message'])) {
						$value['message'] = '发送成功';
					}
				}
			}
			unset($value);
		}

		if ($op == 'log_delete') {
			if (empty($_GPC['id'])) {
				itoast('参数错误', '', 'error');
			}
			$id = pdo_get('storex_plugin_sms_logs', array('id' => intval($_GPC['id'])));
		}

		include $this->template('smsnotice');
	}

}