<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');


class JobTable extends We7Table {

	protected $field = array('type', 'payload', 'status', 'doing', 'uid', 'title', 'progress', 'create_time', 'end_time', 'update_time');

	protected $default = array('status'=>0, 'doing'=>0, 'progress'=>0, 'create_time'=>'custom', 'update_time'=>'custom');
	const DELETE_ACCOUNT = 10;

	/**
	 *  使用默认创建时机
	 * @return int
	 */
	protected function defaultCreateTime() {
		return TIMESTAMP;
	}

	/** 默认更新时间
	 * @return int
	 */
	protected function defaultUpdateTime() {
		return TIMESTAMP;
	}
	/**
	 * 获取所有任务
	 * @return mixed
	 */
	public 	function jobs() {
		return $this->where('status', 0)->getall();
	}

	/**
	 *  所有任务
	 */
	public function alljobs() {
		return $this->getall();
	}

	/*
	 *  设置正在处理中
	 */
	public function setDoing($id, $doing)
	{
		return table('job')->fill('doing', intval($doing) == 1 ? 1 : 0)->where('id', $id)->save();
	}

	/**
	 *  创建一个删除公众号素材的任务
	 * @param $uniacid
	 */
	public function createDeleteAccountJob($uniacid, $uid = 0)
	{
		$data = array(
			'type' => self::DELETE_ACCOUNT,
			'title'=> "删除 uniacid $uniacid 的公众号数据",
		);
		return $this->createJob($data);
	}

	private function createJob($data)
	{
		$this->fill($data);
		$this->save();
	}
}