<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/framework/class/loader.class.php : v 5a1adce731a4 : 2015/02/05 07:16:41 : Gorden $
 */
defined('IN_IA') or exit('Access Denied');

/**
 * @return Loader
 */
function load() {
	static $loader;
	if(empty($loader)) {
		$loader = new Loader();
	}
	return $loader;
}

/**
 * 加载一个表抽象对象
 * @param string $name 服务名称
 * @return We7Table 表模型
 */
function table($name) {
	$table_classname = "\\We7\\Table\\";
	$subsection_name = explode('_', $name);
	if (count($subsection_name) == 1) {
		$table_classname .= ucfirst($subsection_name[0]) . "\\" . ucfirst($subsection_name[0]);
	} else {
		foreach ($subsection_name as $key => $val) {
			if ($key == 0) {
				$table_classname .= ucfirst($val) . '\\';
			} else {
				$table_classname .= ucfirst($val);
			}
		}
	}

	if (in_array($name, array(
		'modules_rank',
		'modules_bindings',
		'modules_plugin',
		'modules_cloud',
		'modules_recycle',
		'modules',
		'modules_ignore',
		'account_xzapp',
		'account_aliapp',
		'account_wxapp',
		'uni_account_modules',
		'system_stat_visit',
		'core_profile_fields',
		'article_comment',
		'wxapp_versions',
		'article_category',
		'article_news',
		'article_notice',
		'attachment_group',
		'core_sendsms_log',
		'core_settings',
		'cover_reply',
		'message_notice_log',
		'phoneapp_versions',
		'qrcode',
		'rule',
		'rule_keyword',
		'site_article_comment',
		'site_templates',
		'site_multi',
		'site_styles',
		'site_styles_vars',
		'users',
		'users_permission',
		'users_profile',
		'stat_visit',
		'uni_verifycode',
	))) {
		return new $table_classname;
	}

	load()->classs('table');
	load()->table($name);
	$service = false;

	$class_name = "{$name}Table";
	if (class_exists($class_name)) {
		$service = new $class_name();
	}
	return $service;
}

/**
 * php文件加载器
 *
 * @method boolean func($name)
 * @method boolean model($name)
 * @method boolean classs($name)
 * @method boolean web($name)
 * @method boolean app($name)
 * @method boolean library($name)
 */
class Loader {

	private $cache = array();
	private $singletonObject = array();
	private $libraryMap = array(
		'agent' => 'agent/agent.class',
		'captcha' => 'captcha/captcha.class',
		'pdo' => 'pdo/PDO.class',
		'qrcode' => 'qrcode/phpqrcode',
		'ftp' => 'ftp/ftp',
		'pinyin' => 'pinyin/pinyin',
		'pkcs7' => 'pkcs7/pkcs7Encoder',
		'json' => 'json/JSON',
		'phpmailer' => 'phpmailer/PHPMailerAutoload',
		'oss' => 'alioss/autoload',
		'qiniu' => 'qiniu/autoload',
		'cos' => 'cosv4.2/include',
		'cosv3' => 'cos/include',
		'sentry' => 'sentry/Raven/Autoloader',
	);
	private $loadTypeMap = array(
		'func' => '/framework/function/%s.func.php',
		'model' => '/framework/model/%s.mod.php',
		'classs' => '/framework/class/%s.class.php',
		'library' => '/framework/library/%s.php',
		'table' => '/framework/table/%s.table.php',
		'web' => '/web/common/%s.func.php',
		'app' => '/app/common/%s.func.php',
	);
	private $accountMap = array(
		'account' => 'account/account',
		'weixin.account' => 'account/weixin.account',
		'weixin.platform' => 'account/weixin.platform',
		'aliapp.account' => 'account/aliapp.account',
		'phoneapp.account' => 'account/phoneapp.account',
		'webapp.account' => 'account/webapp.account',
		'wxapp.account' => 'account/wxapp.account',
		'wxapp.platform' => 'account/wxapp.platform',
		'wxapp.work' => 'account/wxapp.work',
		'xzapp.account' => 'account/xzapp.account',
		'xzapp.platform' => 'account/xzapp.platform',
	);

	public function __construct() {
		$this->registerAutoload();
	}

	public function registerAutoload() {
		spl_autoload_register(array($this, 'autoload'));
		//spl_autoload_register(array($this, 'autoloadBiz'));
	}

	public function autoload($class) {
		$section = array(
			'Table' => '/framework/table/',
		);
		//兼容旧版load()方式加载类
		$classmap = array(
			'We7Table' => 'table',
		);
		if (isset($classmap[$class])) {
			load()->classs($classmap[$class]);
		} elseif (preg_match('/^[0-9a-zA-Z\-\\\\_]+$/', $class)
			&& (stripos($class, 'We7') === 0 || stripos($class, '\We7') === 0)
			&& stripos($class, "\\") !== false) {
				$group = explode("\\", $class);
				$path = IA_ROOT . $section[$group[1]];
				unset($group[0]);
				unset($group[1]);
				$file_path = $path . implode('/', $group) . '.php';
				if(is_file($file_path)) {
					include $file_path;
				}
				//如果没有找到表，默认路由到Core命名空间，兼容之前命名不标准
				$file_path = $path . 'Core/' .  implode('', $group) . '.php';
				if(is_file($file_path)) {
					include $file_path;
				}
		}
	}

	public function __call($type, $params) {
		global $_W;
		$name = $cachekey = array_shift($params);
		if (!empty($this->cache[$type]) && isset($this->cache[$type][$cachekey])) {
			return true;
		}
		if (empty($this->loadTypeMap[$type])) {
			return true;
		}
		//第三方库文件因为命名差异，支持定义别名
		if ($type == 'library' && !empty($this->libraryMap[$name])) {
			$name = $this->libraryMap[$name];
		}
		if ($type == 'classs' && !empty($this->accountMap[$name])) {
			//兼容升级写法，后续直接去掉if判断
			$filename = sprintf($this->loadTypeMap[$type], $this->accountMap[$name]);
			if (file_exists(IA_ROOT . $filename)) {
				$name = $this->accountMap[$name];
			}
		}
		$file = sprintf($this->loadTypeMap[$type], $name);
		if (file_exists(IA_ROOT . $file)) {
			include IA_ROOT . $file;
			$this->cache[$type][$cachekey] = true;
			return true;
		} else {
			trigger_error('Invalid ' . ucfirst($type) . $file, E_USER_WARNING);
			return false;
		}
	}

	/**
	 * 获取一个服务单例，目录是在framework/class目录下
	 * @param unknown $name
	 */
	function singleton($name) {
		if (isset($this->singletonObject[$name])) {
			return $this->singletonObject[$name];
		}
		$this->singletonObject[$name] = $this->object($name);
		return $this->singletonObject[$name];
	}

	/**
	 * 获取一个服务对象，目录是在framework/class目录下
	 * @param unknown $name
	 */
	function object($name) {
		$this->classs(strtolower($name));
		if (class_exists($name)) {
			return new $name();
		} else {
			return false;
		}
	}
}
