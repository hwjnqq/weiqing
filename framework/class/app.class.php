<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/25
 * Time: 14:39
 */

class We7App extends We7Container {



	public function __construct() {
		$this->bootstrap();
	}

	public function bootstrap() {
		$this->registerBaseService();
	}

	protected function registerBaseService() {
		$config = We7Config::instance();
		$this['config'] = $config;
		$this['setting'] = We7Setting::query()->getall();
	}

}