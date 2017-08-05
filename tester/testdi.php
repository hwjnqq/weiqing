<?php
use Testify\Testify;
require '../framework/bootstrap.inc.php';
require IA_ROOT . '/framework/library/testify/Testify.php';

load()->func('communication');
load()->model('material');
load()->classs('container');


// 自动加载 web/class 的文件
spl_autoload_register(function ($class){
	$prefix = 'We7\\Web';
	if ( ! substr($class, 0, 7) === $prefix) {
		return;
	}
	$class = substr($class, strlen($prefix));
	$location = __DIR__ . '/../web/class' . str_replace('\\', '/', $class) . '.php';
	if (is_file($location)) {
		require_once($location);
	}
});

$tester = new Testify('微擎1.x测试用例');
/**
 * {"media_id":"UgGyzOLsgOJs57hLpQ-Z3SC-2FIYLju7jar57w2WMnE","url":"http:\/\/mmbiz.qpic.cn\/mmbiz_png\/GiaZj7Tr2pg816UtmOWR2zUJ2d5q3DJsy0efpAL8aGRcBWkTW2aGIcfaN2icqqQ3CCrIicgHTlKLYm7LicUCQShMhw\/0?wx_fmt=png"}
)
Array
(
[media_id] => UgGyzOLsgOJs57hLpQ-Z3SC-2FIYLju7jar57w2WMnE
[url] => http://mmbiz.qpic.cn/mmbiz_png/GiaZj7Tr2pg816UtmOWR2zUJ2d5q3DJsy0efpAL8aGRcBWkTW2aGIcfaN2icqqQ3CCrIicgHTlKLYm7LicUCQShMhw/0?wx_fmt=png
)
--------------
 */
$tester->test('测试依赖注入', function() {

	global $tester;

	$we7 = new Container();
	// 递归依赖注入 示例
	$we7->bind('We7\Web\ICache',function(){
		return new \We7\Web\FileCache();
	});
	/** @var $first \We7\Web\First */
	$first = $we7->make('We7\Web\First');
	$first->save();

	// 缓存 示例
	$we7->bind('cache', function($container){
		return $container->make('We7\Web\FileCache');
	});
	/** @var $cache \We7\Web\ICache */
	$cache =$we7->make('cache');
	$cache->set('we7','best');


	// 单类示例
	 $we7->singleton('We7\Web\Single');
	 $single = $we7->make('We7\Web\Single');
	 $single2 = $we7->make('We7\Web\Single');
	 $tester->assertTrue($single === $single2);

	 // first 不是单类
	 $first2 = $we7->make('We7\Web\First');
	 $tester->assertFalse($first === $first2);

});

$tester->run();