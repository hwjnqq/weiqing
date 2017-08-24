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
class We7UniAccount extends We7Entity {

	public static function current() {
		global $_W;
		$uniacid = $_W['uniacid'];
		$data = self::query()->where('uni_account')->where('uniacid',$uniacid)->get();
		$account = new We7UniAccount();
		if ($data) {
			$account->attributes = $data;
			$account->uniacid = $uniacid;
			return $account;
		}
		return false;
	}
	/**
	 *  是否支持自定义菜单
	 */
	public function isSupportMenu() {
		return $this->isWechat() && $this->account_wechat && $this->account_wechat->isSupportMenu();
	}
	/**
	 *  是否是微信
	 * @return bool
	 */
	public function isWechat() {
		return $this->account && $this->isWechat();
	}
	/**
	 *  是否是 小程序
	 * @return bool
	 */
	public function isWxApp() {
		return $this->account && $this->isWxApp();
	}
	/**
	 *  是否已接入
	 */
	public function isConnect() {
		return $this->account && $this->isConnect();
	}
	/**
	 *  是否已逻辑删除
	 */
	public function isDeleted() {
		return $this->account && $this->isDeleted();
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
		if($key == 'setting') {
			return $this->setting();
		}
		return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
	}

	/**
	 *  微信信息表
	 *  account_wechat
	 */
	public function account_wechat() {
		return We7AccountWechat::query()->where('uniacid', $this->uniacid)->first();
	}

	/**
	 *  获取Account 表信息
	 * @return \Ambigous|string
	 */
	public function account() {
		return We7Account::query()->where('uniacid', $this->uniacid)->first();
	}


	/**
	 *  获取当前公众号的 快捷菜单模块
	 * @param bool $enable
	 */
	public function account_modules($enable = null) {
		$query = new Query();
		$account_modules = $query->from('uni_account_modules')
			->where('uniacid', $this->uniacid)->getall('module');
		return We7Module::query()->where('name', array_keys($account_modules))->getall();
	}

	/**
	 *  公众号设置
	 * @return mixed
	 */
	public function setting() {
		return We7UniSetting::query()->where('uniacid', $this->uniacid)->first();
	}











}