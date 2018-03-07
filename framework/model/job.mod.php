<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/framework/model/extension.mod.php : v fc9f77cc82f2 : 2015/08/31 07:00:43 : yanghf $
 */
defined('IN_IA') or exit('Access Denied');


/**
 *  任务列表
 */
function job_list() {

}


/**
 *  获取一个job
 * @param $id
 * @return mixed
 */
function job_single($id) {
	return table('job')->getById($id);
}


/**
 * 创建一个删除素材的任务
 * @param $uniacid
 */
function job_create_delete_account($uniacid) {
	global $_W;
	/* @var $job JobTable */
	$job = table('job');
	$job->createDeleteAccountJob($uniacid, $_W['uid']);
}
/**
 *  执行任务
 */
function job_execute($id) {
	$job = job_single($id);
	$type = $job['type'];
	if (intval($job['status']) == 1) {
		return $job;
	}
	$result = null;
	switch ($type) {
		case $type : $result = job_execute_delete_account($job); break;
	}
	if ($result === 0) {
		// 任务完成
		table('job')->finish($id);
	}
}

/**
 * 执行删除任务
 * @return array array(是否结束, 进度);
 */
function job_execute_delete_account($job) {

	$payload = unserialize($job['payload']);
	$uniacid = $payload['uniacid'];
	// 先查询出来数据 然后文件再删除记录
	$core_attchments = table('attachment')->where('uniacid', $uniacid)
		->searchWithPage(1, 10)->getall('id');

	array_walk($core_attchments, function($item) {
		$path = $item['attchment'];
		file_delete($path);
	});

	$wechat_attachments = table('attachment')->local(false)->where('uniacid', $uniacid)
		->searchWithPage(1, 10)->getall('id');
	array_walk($wechat_attachments, function($item) {
		$path = $item['attchment'];
		file_delete($path);
	});
	// 都为0 说明已经删除完了
	if ($core_attchments == 0 && $wechat_attachments == 0) {
		table('attachment_group')->deleteByUniacid($uniacid);
	}

	// 从数据表中删除记录
	$core_ids = array_keys($core_attchments);
	$wechat_ids = array_keys($wechat_attachments);
	if (count($core_ids) > 0) {
		table('attchment')->deleteById($core_ids);
	}
	if (count($wechat_ids) > 0) {
		table('attchment')->local(false)->deleteById($core_ids);
	}

	return count($core_ids) + count($wechat_ids);


}