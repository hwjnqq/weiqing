<?php
/**
 * 把创始人在ims_users中的founder_groupid标识为1
 */
require '../../framework/bootstrap.inc.php';

global $_W;
$founder_ids = explode(',', $_W['config']['setting']['founder']);
$uids = pdo_getall('users', array('uid' => $founder_ids), 'uid');
if (!empty($uids)) {
	foreach ($uids as $user_id) {
		pdo_update('users', array('founder_groupid' => 1), array('uid' => $user_id['uid']));
	}
}