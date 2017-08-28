<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/9
 * Time: 9:04
 * @property-read $maxaccount 最多可添加的公众号数量
 * @property-read $maxwxapp  最多可添加的小程序数量
 * @property-read We7FounderGroup $group     用户所在组
 */
class We7User extends We7Entity {

	protected $table = 'users';
	protected $primaryKey = 'uid';
	protected $uid;

	public static function current() {
		global $_W;
		$userdata = pdo_get('users',array('uid' => $_W['uid']));
		unset($userdata['password'], $userdata['salt']);
		$user = new We7User();
		$user->uid = $_W['uid'];
		$user->fill($userdata);
		return $user;
	}

	/**
	 *  是否超级管理
	 * @return mixed
	 */
	public function isSuper() {
		global $_W;
		return $_W['isfounder'];
	}
	/**
	 *  是否是创始人 不管 创始人还是副创始人
	 * @return mixed
	 */
	public function isFounder() {
		return $this->isViceFounder();
	}
	/**
	 *  是否是副创始人
	 * @return bool
	 */
	public function isViceFounder() {
		return $this->founder_groupid == ACCOUNT_MANAGE_GROUP_VICE_FOUNDER;
	}

	/**
	 *  是否可以添加公众号
	 */
	public function can_add_account() {
		$data = uni_user_account_permission($this->uid);
		return $data['uniacid_limit'] > 0;
	}

	/**
	 *  用户所在组
	 */
	public function group() {
		if($this->isFounder()) {
			return $this->founder_group();
		}
		return $this->user_group();
	}

	/**
	 *  副创始人组
	 */
	public function founder_group() {
		if($this->isFounder()) {
			return We7FounderGroup::query()->where('id',$this->groupid);
		}
	}

	/**
	 *  用户组
	 */
	public function user_group() {
		if(! $this->isFounder()) {
			return We7UserGroup::query()->where('id', $this->groupid);
		}
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

		if(! $this->isSuper()) {
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





	public function __get($key) {
		if($key == 'accounts') {
			return $this->accounts()->getall();
		}
		if($key == 'group') {
			$result = $this->group()->first();
			return $result;
		}
		if($key === 'maxaccount') {
			return $this->maxaccount();
		}
		if ($key == 'maxweapp') {
			return $this->maxwxapp();
		}
		return parent::__get($key);
	}

	/**
	 *  获取最多添加公众号数量
	 * @return mixed
	 */
	private function maxaccount() {
		$group = $this->group;
		return $group->maxaccount;
	}

	/**
	 *  最多添加小程序数量
	 * @return mixed
	 */
	private function maxwxapp() {
		$group = $this->group;
		return $group->maxweapp;
	}
}