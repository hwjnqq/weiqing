<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/8
 * Time: 15:24
 */




class We7Module extends We7Entity {

	protected $table = 'modules';
	protected $primaryKey = 'id';
	/**
	 *  是否是系统模块
	 * @return mixed
	 */
	public function isSystem() {
		return $this->issystem;
	}

	/**
	 *  是否支持小程序
	 * @return bool
	 */
	public function isSupportWxApp() {
		return $this->wxapp_support == 2;
	}

	/**
	 *  是否支持 是否支持普通应用
	 * @return bool
	 */
	public function isSupportApp() {
		return $this->app_support == 2;
	}


}