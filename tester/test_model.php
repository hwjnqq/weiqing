<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/8
 * Time: 17:09
 */

use Testify\Testify;




require '../framework/bootstrap.inc.php';
require IA_ROOT . '/framework/library/testify/Testify.php';



spl_autoload_register(function($classname){
	$lowerclass = strtolower($classname);
	if(startsWith($lowerclass,'we7')) {
		$path = IA_ROOT.'/'.'framework/entity/'.$lowerclass.'.entity.php';
		include $path;
	}
});
load()->model('user');
load()->classs('query');

$user = We7User::with('group')->where('uid',173)->first();
dump($user);


//$query = new Query();
//$data = $query->from('users_founder_group')->where('id',2)->get();
//dump(iunserializer($data['package']));
////dump(($user->group));
//exit;


$tester = new Testify('测试数据库');

$tester->test('测试查询', function(){
	global $tester;
	/**
	 * @var $table AccountTable
	 */
	$table = table('account');
	var_dump($table->find(1185));

});

$tester->test('测试插入',function() {
	/**
	 * @var $table2 AccountTable
	 */
	$table2 = table('account');
	$table2->groupid = 0;
	$table2->defaultacid = 100460;
	$table2->name = 'table 测试';
	$table2->description = 'table测试';
	$table2->rank = 0;
	$table2->createtime = 0;
	$table2->title_initial = 'T';
	$table2->save();
});
