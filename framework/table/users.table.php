<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class UsersTable extends We7Table {

	public function searchUsersList() {
		global $_W;
		$this->query->from('users', 'u')
				->select('u.*, p.avatar as avatar')
				->leftjoin('users_profile', 'p')
				->on(array('u.uid' => 'p.uid'))
				->orderby('uid', 'DESC');
		if (user_is_vice_founder()) {
			$this->query->where('u.owner_uid', $_W['uid']);
		}
		return $this->query->getall();
	}

	/**
	 *  获取用户所能操作的所有公众号的uniacid
	 */
	public function userOwnedAccount($uid) {
		$uniacid_list = $this->query->from('uni_account_users')->where('uid', $uid)->getall('uniacid');
		return array_keys($uniacid_list);
	}

	public function searchWithStatus($status) {
		$this->query->where('u.status', $status);
		return $this;
	}

	public function searchWithType($type) {
		$this->query->where('u.type', $type);
		return $this;
	}

	public function searchWithFounder($founder_groupids) {
		$this->query->where('u.founder_groupid', $founder_groupids);
		return $this;
	}

	public function searchWithName($user_name) {
		$this->query->where('u.username LIKE', "%{$user_name}%");
		return $this;
	}

	public function searchWithOwnerUid($owner_uid) {
		$this->query->where('u.owner_uid', $owner_uid);
		return $this;
	}

	public function accountUsersNum($uid) {
		return $this->query->from('uni_account_users')->where('uid', $uid)->count();
	}

	public function usersGroup() {
		return $this->query->from('users_group')->getall('id');
	}

	public function usersFounderGroup() {
		return $this->query->from('users_founder_group')->getall('id');
	}
}