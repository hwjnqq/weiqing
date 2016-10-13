<?php
/**
 * 微外卖模块微站定义
 * @author 微擎团队&灯火阑珊
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
mload()->model('deliveryer');
$do = 'index';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
checkdeliveryer();
header('location:' . $this->createMobileUrl('dyorder'));
die;