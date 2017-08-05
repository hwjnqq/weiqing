<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/5
 * Time: 9:27
 */

namespace We7\Web;


class FileCache implements ICache {

	public function set($key, $value) {
		echo "文件缓存 key:$key value: $value".PHP_EOL;
	}
}