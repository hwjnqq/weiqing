<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/24
 * Time: 18:54
 */
class We7AccountWechat extends We7Entity {

	protected $table = 'account_wechats';
	protected $primaryKey = 'acid';

	/**
	 *  是否支持自定义菜单
	 */
	public function isSupportMenu() {
		return $this->level > 0;
	}

}