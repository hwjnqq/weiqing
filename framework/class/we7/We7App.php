<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/25
 * Time: 14:39
 */
namespace We7\Core;
/**
 *
 * @property-read We7Request $request
 * @property-read We7Config $config
 * Class We7App
 */
class We7App extends We7Container {


	public $w = array();
	public function __construct() {

		$this->bootstrap();
	}



	public function bootstrap() {
		static::$instance = $this;
		$this->initloader();
		$this->registerBaseService();
		$this->registerExceptionHandler();
		$this->checkinstall();
		$this->initglobal();//初始化全局变量
	}


	/**
	 *  检查是否 已安装
	 */
	private function checkinstall() {
		if(!$this->config->isloaded()) {
			if(file_exists(IA_ROOT . '/install.php')) {
				header('Content-Type: text/html; charset=utf-8');
				require IA_ROOT . '/framework/version.inc.php';
				echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
				echo "·如果你还没安装本程序，请运行<a href='".(strpos($_SERVER['SCRIPT_NAME'], 'web') === false ? './install.php' : '../install.php')."'> install.php 进入安装&gt;&gt; </a><br/><br/>";
				echo "&nbsp;&nbsp;<a href='http://www.we7.cc' style='font-size:12px' target='_blank'>Power by WE7 " . IMS_VERSION . " &nbsp;微擎公众平台自助开源引擎</a>";
				exit();
			} else {
				header('Content-Type: text/html; charset=utf-8');
				exit('配置文件不存在或是不可读，请检查“data/config”文件或是重新安装！');
			}
		}
	}

	protected function registerBaseService() {
		$config = We7Config::instance();
		$this['config'] = $config;
		$this['request'] = We7Request::createRequest();
	}

	/**
	 *  注册错误处理函数
	 */
	protected function registerExceptionHandler() {
		define('DEVELOPMENT', $this->config->isDev());
		if(DEVELOPMENT) {
			ini_set('display_errors', '1');
			error_reporting(E_ALL ^ E_NOTICE);
		} else {
			error_reporting(0);
		}
	}

	private function initloader() {
		require IA_ROOT . '/framework/version.inc.php';
		require IA_ROOT . '/framework/const.inc.php';
		require IA_ROOT . '/framework/class/loader.class.php';

		load()->func('global');
		load()->func('compat');
		load()->func('pdo');
		load()->classs('account');
		load()->model('cache');
		load()->model('account');
		load()->model('setting');
		load()->library('agent');
	}

	/**
	 *  旧方式兼容
	 */
	private function initglobal() {
		define('CLIENT_IP', $this->request->ip());
		$this->w['config'] = $this->config;
		// config类去处理
//		$this['config']['db']['tablepre'] = !empty($this['config']['db']['master']['tablepre']) ? $this['config']['db']['master']['tablepre'] : $this['config']['db']['tablepre'];
		$this->w['timestamp'] = TIMESTAMP;
		$this->w['charset'] = $this->config->charset();
		$this->w['clientip'] = CLIENT_IP;

		define('ATTACHMENT_ROOT', IA_ROOT .'/attachment/');

		load()->func('cache');

		if($this->config->isSetHttps()) {
			$this->w['ishttps'] = $this->config->isHttps();
		} else {
			$this->w['ishttps'] = $this->request->isHttps();
		}

		$this->w['isajax'] = $this->request->isAjax();
		$this->w['ispost'] = $this->request->isPost();
		$this->w['sitescheme'] = $this->w['ishttps'] ? 'https://' : 'http://';
		$this->w['script_name'] = htmlspecialchars(scriptname());
		$this->w['siteroot'] = $this->request->siteroot();
		$this->w['siteurl'] = $this->request->siteurl();
		$this->w['os'] = $this->request->os();
		$this->w['container'] = $this->request->container();
		$this->initsetting();


	}

	protected function initsetting() {
//		setting_load();
		if (empty($this->w['setting']['upload'])) {
			$this->w['setting']['upload'] = array_merge($this->w['config']['upload']);
		}
		$this->w['attachurl'] = $this->w['attachurl_local'] = $this->w['siteroot'] . $this->w['config']['upload']['attachdir'] . '/';
		if (!empty($this->w['setting']['remote']['type'])) {
			if ($this->w['setting']['remote']['type'] == ATTACH_FTP) {
				$this->w['attachurl'] = $this->w['attachurl_remote'] = $this->w['setting']['remote']['ftp']['url'] . '/';
			} elseif ($this->w['setting']['remote']['type'] == ATTACH_OSS) {
				$this->w['attachurl'] = $this->w['attachurl_remote'] = $this->w['setting']['remote']['alioss']['url'].'/';
			} elseif ($this->w['setting']['remote']['type'] == ATTACH_QINIU) {
				$this->w['attachurl'] = $this->w['attachurl_remote'] = $this->w['setting']['remote']['qiniu']['url'].'/';
			} elseif ($this->w['setting']['remote']['type'] == ATTACH_COS) {
				$this->w['attachurl'] = $this->w['attachurl_remote'] = $this->w['setting']['remote']['cos']['url'].'/';
			}
		}
	}


}