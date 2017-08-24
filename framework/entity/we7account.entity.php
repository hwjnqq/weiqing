<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/24
 * Time: 18:45
 */

class We7Account extends We7Entity {

	protected $table = 'account';
	protected $primaryKey = 'acid';

	/**
	 *  是否是微信
	 * @return bool
	 */
	public function isWechat() {
		return $this->type == 1 || $this->type == 3;
	}

	/**
	 *  是否是 小程序
	 * @return bool
	 */
	public function isWxApp() {
		return $this->type == 4;
	}
	/**
	 *  是否已接入
	 */
	public function isConnect() {
		return $this->isconnect == 1;
	}

	/**
	 *  是否已逻辑删除
	 */
	public function isDeleted() {
		return $this->isDeleted == 1;
	}
}