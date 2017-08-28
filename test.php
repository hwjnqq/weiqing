<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/25
 * Time: 15:30
 */
//$content = '<img src="http://1.1.1.1/a/12/33/333.44488998.55.bmp"></img>';
//$content = htmlentities($content);
//var_dump($content);
//preg_match('/&lt;img.*?src=&quot;?(.+\.(jpg|gif|bmp|png))&quot;/', $content, $match);
$http = 'http://www.baidu.com@127.0.0.1/a/b/c';
var_dump(parse_url($http));
$pattern = "/^(10|172|192|127)/";
if (preg_match($pattern, '10.')) {
	echo 'true';
}
echo 'aaa';
