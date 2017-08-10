<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/9
 * Time: 9:06
 */

/***
 * Class We7UniAccount
 * @property-read $account
 * @property-read $account_wechat
 * @property-read $user;
 * @property-read $account_modules
 */
class We7UniAccount {
	private $uniacid;

	protected $attributes;

	public static function current() {
		global $_W;
		$uniacid = $_W['uniacid'];
		$data = pdo_get('uni_account',array('uniacid'=>$uniacid));
		if ($data) {
			return self::fill($data);
		}
		return false;
	}

	public static function fill(array $attributes = array()) {
		$we7Account = new We7UniAccount();
		$we7Account->attributes = $attributes;
		return $we7Account;
	}
	/**
	 *  是否支持自定义菜单
	 */
	public function isSupportMenu() {
		if($this->isWechat()) {
			if($this->account_wechat) {
				return $this->account_wechat['level'] > 0;
			}
		}
		return false;
	}
	/**
	 *  是否是微信
	 * @return bool
	 */
	public function isWechat() {
		if($this->account) {
			return $this->account['type'] == 1 || $this->account['type'] == 3;
		}
	}
	/**
	 *  是否是 小程序
	 * @return bool
	 */
	public function isWxApp() {
		if($this->account) {
			return $this->account['type'] == 4;
		}
	}
	/**
	 *  是否已接入
	 */
	public function isConnect() {
		if($this->account) {
			return $this->account['type'] == 1;
		}
	}
	/**
	 *  是否已逻辑删除
	 */
	public function isDeleted() {
		if($this->account) {
			return $this->account['isDeleted'] == 1;
		}
	}

	public function __get($key) {
		if($key == 'account') {
			return $this->account();
		}
		if($key == 'account_wechat') {
			return $this->account_wechat();
		}
		if($key == 'account_modules') {
			return $this->account_modules();
		}
		return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
	}

	/**
	 *  account_wechat
	 */
	public function account_wechat() {
		static $account_wechat;
		if (!$account_wechat) {
			$account_wechat = pdo_get('account_wechats',array('uniacid'=>$this->uniacid));
		}
		return $account_wechat;
	}

	/**
	 *  获取Account 表信息
	 * @return \Ambigous|string
	 */
	public function account() {
		static $account;
		if (!$account) {
			$account = pdo_get('account',array('uniacid'=>$this->uniacid));
		}
		return $account;
	}


	/**
	 *  获取当前公众号的 快捷菜单模块
	 * @param bool $enable
	 */
	public function account_modules() {

		static $modules = array();
		if (!$modules) {
			$tablename = tablename('uni_account_modules');
			$modules = pdo_fetchall("SELECT * from $tablename where uniacid = :uniacid", array('uniacid'=>$this->uniacid));
		}
		return $modules;
	}




}