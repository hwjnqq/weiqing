<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/5
 * Time: 9:25
 */

namespace We7\Web;


interface ICache {

	public function set($key, $value);
}