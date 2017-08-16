<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/9
 * Time: 9:04
 */
class We7User {


	protected $uid;
	private $attributes;

	public static function current() {
		global $_W;
		$userdata = pdo_get('users',array('uid' => $_W['uid']));
		unset($userdata['password'], $userdata['salt']);
		$user = new We7User();
		$user->uid = $_W['uid'];
		$user->fill($userdata);
		return $user;
	}


	public function fill($attributes) {
		$this->attributes = $attributes;
		return $this;
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




	/**
	 *  获取 当前用户在某个公众账号下的角色
	 * @param $uniacid
	 */
	public function forUniAccount($uniacid) {

	}

	public function query() {
		return new Query(pdo());
	}

	/**
	 *   所有小程序
	 */
	public function wxApps() {
		return $this->accounts()->where('type',4);
	}

	/**
	 *   所有小程序
	 */
	public function wechats() {
		return $this->accounts()->where('type',array(1,3));
	}

	/**
	 *  所有公众号和小程序
	 *  暂不支持分页
	 */
	public function accounts() {
		$query = We7UniAccount::query();
		$query->from('uni_account','a')
			->leftjoin('account','b')
			->on('a.uniacid','b.uniacid')
			->on('a.default_acid','b.acid')
			->where('b.isdeleted <>', 1);

		if(! $this->isFounder()) {
			$query->leftjoin('uni_account_users','c')
				->on('a.uniacid','c.uniacid')
				->where('a.defaultacid <>', 0)
				->where('c.uid', $this->uid)
				->orderby('c.rank','DESC');
		}else {
			$query->where('a.default_acid <>',0);
		}
		$query->orderby('rank','DESC');
		return $query;

	}


	public static function __callStatic($method, $params) {
		$user = new We7User();
		return call_user_func_array(array($user, $method), $params);
	}



	public function __set($key, $value) {
		$this->attributes[$key] = $value;
	}

	public function __get($key) {
		if($key == 'accounts') {
			return $this->accounts()->getall();
		}
		return isset($this->attributes[$key]) ? $this->g[$key] : null;
	}
}