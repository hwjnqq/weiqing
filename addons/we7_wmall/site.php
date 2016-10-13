<?php
/**
 * 超级外卖模块微站定义
 * @author strday
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
include('model.php');
class We7_wmallModuleSite extends WeModuleSite {
	private $cache = array();
	public function __construct() {
		global $_W, $_GPC;
		mload()->model('order');
		$config = sys_config();
		if(empty($this->module['config'])) {
			$this->module['config'] = array();
		}
		$_W['we7_wmall'] = array_merge($config, $this->module['config']);
		//init_cron();
		if(defined('IN_MOBILE')) {
			//$this->appInit();
		} else {
			$this->webInit();
		}
	}

	public function webInit() {
		global $_W;
		if($_W['role'] == 'operator') {
			$GLOBALS['frames'] = operator_menu();
		}
	}

	public function checkAuth() {
		global $_W;
		load()->model('mc');
		if(!empty($_W['member']) && (!empty($_W['member']['mobile']) || !empty($_W['member']['email']))) {
			return true;
		}
		if(!empty($_W['openid'])) {
			$fan = mc_fansinfo($_W['openid'], $_W['acid'], $_W['uniacid']);
			if(_mc_login(array('uid' => intval($fan['uid'])))) {
				return true;
			}
		}

		$forward = base64_encode($_SERVER['QUERY_STRING']);
		if($_W['ispost']) {
			$result = array();
			$result['url'] = url('auth/login', array('forward' => $forward), true);
			$result['act'] = 'redirect';
			exit(json_encode($result));
		} else {
			header("location: " . url('auth/login', array('forward' => $forward)), true);
		}
		exit;
	}

	public function template($filename, $flag = TEMPLATE_DISPLAY) {
		global $_W;
		if(defined('IN_MOBILE')) {
			$dirs = explode('/', $filename);
			if(is_array($dirs) && in_array($dirs[0], array('manage', 'delivery', 'common')) && !empty($dirs[1])) {
				$filename = $filename;
			} else {
				$filename = 'default/' . $filename;
			}
		} else {
			if(substr($filename, 0, 7) != 'common/') {
				$filename = 'web/' . $filename;
			}
		}
		$compile = parent::template($filename);
		switch ($flag) {
			case TEMPLATE_DISPLAY:
			default:
				extract($GLOBALS, EXTR_SKIP);
				return $compile;
				break;
			case TEMPLATE_FETCH:
				extract($GLOBALS, EXTR_SKIP);
				ob_flush();
				ob_clean();
				ob_start();
				include $compile;
				$contents = ob_get_contents();
				ob_clean();
				return $contents;
				break;
			case TEMPLATE_INCLUDEPATH:
				return $compile;
				break;
		}
	}

	public function __call($name, $arguments) {
		$isWeb = stripos($name, 'doWeb') === 0;
		$isMobile = stripos($name, 'doMobile') === 0;
		if($isWeb || $isMobile) {
			$dir = IA_ROOT . '/addons/' . $this->modulename . '/inc/';
			if($isWeb) {
				$common = array('utility');
				$do = strtolower(substr($name, 5));
				if(in_array($do, $common)) {
					$dir .= 'web/common/';
				} else {
					$ptf = substr($do, 0, 3);
					if($ptf == 'ptf') {
						$do = substr($do, 3);
						$dir .= 'web/plateform/';
					} else {
						$dir .= 'web/store/';
					}
				}
				$fun = $do;
			}
			if($isMobile) {
				$common = array('utility');
				$do = strtolower(substr($name, 8));
				if(in_array($do, $common)) {
					$dir .= 'mobile/common/';
				} else {
					$sys = substr($do, 0, 2);
					if($sys == 'mg') {
						$do = substr($do, 2);
						$dir .= 'mobile/manage/';
					} elseif($sys == 'dy') {
						$do = substr($do, 2);
						$dir .= 'mobile/delivery/';
					} else {
						$dir .= 'mobile/store/';
					}
				}
				$fun = $do;
			}
			$file = $dir . $fun . '.inc.php';
			if(file_exists($file)) {
				require $file;
				exit;
			}
		}
		trigger_error("访问的方法 {$name} 不存在.", E_USER_WARNING);
		return null;
	}

	public function payResult($params) {
		global $_W;
		if($params['result'] == 'success' && $params['from'] == 'notify') {
			mload()->model('order');
			$data['pay_type'] = $params['type'];
			$data['final_fee'] = $params['card_fee'];
			$data['is_pay'] = 1;
			$data['paytime'] = TIMESTAMP;
			pdo_update('tiny_wmall_order', $data, array('id' => $params['tid'], 'uniacid' => $_W['uniacid']));
			$order = pdo_fetch('SELECT id, sid, order_type FROM ' . tablename('tiny_wmall_order') . ' WHERE uniacid = :aid AND id = :id', array(':aid' => $_W['uniacid'], ':id' => $params['tid']));
			order_insert_status_log($order['id'], $order['sid'], 'pay');
			order_print($order['sid'], $order['id']);
			order_clerk_notice($order['sid'], $order['id'], 'order');
		}

		if($params['from'] == 'return') {
			$order = pdo_fetch('SELECT id, sid FROM ' . tablename('tiny_wmall_order') . ' WHERE uniacid = :aid AND id = :id', array(':aid' => $_W['uniacid'], ':id' => $params['tid']));
			if($params['type'] == 'credit' || $params['type'] == 'delivery') {
				message('支付成功！', $this->createMobileUrl('myorder', array('op' => 'detail', 'id' => $order['id'])), 'success');
			} else {
				message('支付成功！', '../../app/' .$this->createMobileUrl('myorder', array('op' => 'detail', 'id' => $order['id'])), 'success');
			}
		}
	}

}