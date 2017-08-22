<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
 
defined('IN_IA') or exit('Access Denied');

class AccountTable extends We7Table {
	
	public function searchAccountList() {
		global $_W;
		$this->query->from('uni_account', 'a')->select('a.uniacid')->leftjoin('account', 'b')
				->on(array('a.uniacid' => 'b.uniacid', 'a.default_acid' => 'b.acid'))
				->where('b.isdeleted !=', '1')->orderby('a.uniacid', 'desc');
		
		//普通用户和副站长查询时，要附加可操作公众条件
		if (empty($_W['isfounder']) || user_is_vice_founder()) {
			$this->query->leftjoin('uni_account_users', 'c')->on(array('a.uniacid' => 'c.uniacid'))
						->where('a.default_acid !=', '0')->where('c.uid', $_W['uid'])
						->orderby('c.rank', 'desc');
		} else {
			$this->query->where('a.default_acid !=', '0')->orderby('a.rank', 'desc');
		}
		$list = $this->query->getall('a.uniacid');
		return $list;
	}
	
	public function searchWithKeyword($title) {
		$this->query->where('a.name LIKE', "%{$title}%");
		return $this;
	}
	
	public function searchWithType($types = array()) {
		$this->query->where(array('b.type' => $types));
		return $this;
	}
	
	public function searchWithLetter($letter) {
		if (!empty($letter)) {
			$this->query->where('a.title_initial', $letter);
		} else {
			$this->query->where('a.title_initial', '');
		}
		return $this;
	}
}