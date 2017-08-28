<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/28
 * Time: 9:21
 */

class We7Request {


	public static function createRequest() {
		return new We7Request();
	}
	/**
	 *  是否是ajax
	 */
	public function isAjax() {


	}

	public function isHttps() {
		$config =  we7app('config');
		$app = we7app();
		if ($config->isSetHttps()) {
			$app['ishttps'] = $config->isHttps();
		} else {
			$app['ishttps'] = $this->isServerHttps();
		}
	}

	/**
	 *  是否https
	 */
	private function isServerHttps() {
		return $_SERVER['SERVER_PORT'] == 443 ||
		(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ||
		strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https' ||
		strtolower($_SERVER['HTTP_X_CLIENT_SCHEME']) == 'https' //阿里云判断方式
			? true : false;
	}
}