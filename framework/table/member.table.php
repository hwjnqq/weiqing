<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class MemberTable extends We7Table {

	public function creditsRecordList()
	{
		global $_W;
		$this->query->from('mc_credits_record', 'r')
				->select('r.*, u.username as username')
				->leftjoin('users', 'u')
				->on(array('r.operator' => 'u.uid'))
				->where('r.uniacid', $_W['uniacid']);
		$this->query->orderby('r.id', 'desc');
		return $this->query->getall();
	}

	public function searchCreditsRecordUid($uid) {
		$this->query->where('r.uid', $uid);
		return $this;
	}

	public function searchCreditsRecordType($type) {
		$this->query->where('r.credittype', $type);
		return $this;
	}
}