<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/18
 * Time: 13:30
 */
define('IN_SYS', true);
include_once __DIR__.'/../../framework/bootstrap.inc.php';

pdo()->update('test_laraveldb',array('age'=>'3'),array('id'=>113));



