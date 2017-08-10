<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/9
 * Time: 9:04
 */
class We7User extends We7Model {


	protected $primaryKey = 'uid';

	public static function current() {
		global $_W;
		$userdata = pdo_get('users',array('uid' => $_W['uid']));
		unset($userdata['password'], $userdata['salt']);
		$user = new We7User();
		$user->fill($userdata);
		return $user;
	}
	/**
	 *  是否是创始人
	 * @return mixed
	 */
	public function isFounder() {
		global $_W;
		return $_W['isfounder'];
	}
	/**
	 *  是否是副创始人
	 * @return bool
	 */
	public function isViceFounder() {
		return user_is_vice_founder($this->uid);
	}

	/**
	 *  是否可以添加公众号
	 */
	public function canAddAccount() {
		$data = uni_user_account_permission($this->uid);
		return $data['uniacid_limit'] > 0;
	}

	/**
	 *  是否有操作权限
	 * @param $permission_name
	 * @return bool
	 */
	public function can($permission_name) {
		return uni_user_permission_check($permission_name,false);
	}

	public function __get($key) {
		if($key == 'accounts') {
			return $this->accounts();
		}
		return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
	}

	/**
	 *  获取 当前用户在某个公众账号下的角色
	 * @param $uniacid
	 */
	public function forUniAccount($uniacid) {

	}
	/**
	 *  所有公众号和小程序
	 *  暂不支持分页
	 */
	public function accounts() {

		$accounts_user = pdo_getall('uni_account_users',array('uid'=>$this->uid),array(),'uniacid');
		$uniacids = array_keys($accounts_user);

		$uniAccountsArray = pdo_getall('uni_account', array('uniacid',$uniacids));
		$uniAccounts = array();
		foreach ($uniAccountsArray as $uniacid => $item) {
			$uniAccounts[] = \We7UniAccount::fill($item);
		}
		return $uniAccounts;
	}



}