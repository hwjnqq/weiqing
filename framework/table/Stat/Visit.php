<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Stat;

class Visit extends \We7Table {
	protected $tableName = 'stat_visit';
	protected $primaryKey = 'id';
	protected $field = array(
		'uniacid',
		'type',
		'module',
		'count',
		'date',
	);
	protected $default = array(
		'uniacid' => '',
		'type' => '',
		'module' => '',
		'count' => '',
		'date' => '',
	);

	public function visitList($params, $type = 'more') {
		if (!empty($params['uniacid'])) {
			$this->query->where('uniacid', $params['uniacid']);
		}
		if (!empty($params['date'])) {
			$this->query->where('date', $params['date']);
		}
		if (!empty($params['date >='])) {
			$this->query->where('date >=', $params['date >=']);
		}
		if (!empty($params['date <='])) {
			$this->query->where('date <=', $params['date <=']);
		}
		if (!empty($params['module'])) {
			$this->query->where('module', $params['module']);
		}
		if (!empty($params['type'])) {
			$this->query->where('type', $params['type']);
		}

		if ($type == 'one') {
			return $this->query->get();
		} else {
			return $this->query->getall();
		}
	}
}