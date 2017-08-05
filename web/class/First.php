<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/5
 * Time: 9:09
 */

namespace We7\Web;


class First {
	/** @var  $cache ICache */
	private $cache;
	public function __construct(Second $second,ICache $cache) {
		echo 'first'.PHP_EOL;
		$this->cache = $cache;
		$cache->set('hello','world');
	}

	public function save() {
		$data = $this->saveDB();
		$this->cache->set('cache_key', $data);
		return $data;
	}

	private function saveDB() {
		return 'db';
	}
}