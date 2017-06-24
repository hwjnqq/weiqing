<?php 
/**
 * 数据库相关操作
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
//防止30秒运行超时的错误（Maximum execution time of 30 seconds exceeded).
set_time_limit(0);

load()->func('file');
load()->model('cloud');
load()->func('db');
load()->model('system');
$dos = array('backup', 'restore', 'trim', 'optimize', 'run');
$do = in_array($do, $dos) ? $do : 'backup';

//备份
if ($do == 'backup') {
	$_W['page']['title'] = '备份 - 数据库 - 常用系统工具 - 系统管理';
	if ($_GPC['status']) {
		if (empty($_W['setting']['copyright']['status'])) {
			itoast('为了保证备份数据完整请关闭站点后再进行此操作', url('system/site'), 'error');
		}
		
		//获取系统数据库中所有表
		$sql = "SHOW TABLE STATUS LIKE '{$_W['config']['db']['tablepre']}%'";
		$tables = pdo_fetchall($sql);
		if (empty($tables)) {
			itoast('数据已经备份完成', url('system/database/'), 'success');
		}
		
		//设置备份文件的卷数。
		 $series = max(1, intval($_GPC['series']));
		//设置备份文件名中随机数。
		if (!empty($_GPC['volume_suffix'])) {
			$volume_suffix =  $_GPC['volume_suffix'];
		} else {
			$volume_suffix = random(10);
		}	
		//设置备份目录中随机数
		if (!empty($_GPC['folder_suffix'])) {
			$folder_suffix = $_GPC['folder_suffix'];
		} else {
			$folder_suffix = TIMESTAMP . '_' . random(8);
		}
		//设置备份目录
		$bakdir = IA_ROOT . '/data/backup/' . $folder_suffix;
		if (trim($_GPC['start'])) {
			$result = mkdirs($bakdir);		
		}
		//一次让查询300条记录
		$size = 300;
		//设置备份文件的大小
		$volumn = 1024 * 1024 * 2;
		$dump = '';
		//设置上卷结束时的表名
		if (empty($_GPC['last_table'])) {
			$last_table ='';
			$catch = true;
		} else {
			$last_table = $_GPC['last_table'];
			$catch = false;
		}
		foreach ($tables as $table) {
			//抛出表名
			$table = array_shift($table);
			//如果开始有表，且表名相同	
			if (!empty($last_table) && $table == $last_table) {
				$catch = true;
			}
			//查询上卷结束时的表名，没找到则跳出本次循环，进入下次循环
			if (!$catch) { 
				continue;
			}
			if (!empty($dump)) {
				$dump .= "\n\n";
			}
			// 枚举所有表的创建语句
			if ($table != $last_table) {
				$row = db_table_schemas($table);
				$dump .= $row;
			}
			//设置查询表中记录的开始标识
			$index = 0;
			if (!empty($_GPC['index'])) {
				$index = $_GPC['index'];
				$_GPC['index'] = 0;
			}
			//枚举所有表的INSERT语句
			while (true) {
				$start = $index * $size;
				$result = db_table_insert_sql($table, $start, $size);
				if (!empty($result)) {
					$dump .= $result['data'];
					if (strlen($dump) > $volumn) {
						$bakfile = $bakdir . "/volume-{$volume_suffix}-{$series}.sql";
						$dump .= "\n\n";
						file_put_contents($bakfile, $dump);
						$series++;
						$index++;
						$current = array(
							'last_table' => $table,
							'index' => $index,
							'series' => $series,
							'volume_suffix'=>$volume_suffix,
							'folder_suffix'=>$folder_suffix,
							'status'=>1
						);
						$current_series = $series-1;
						message('正在导出数据, 请不要关闭浏览器, 当前第 ' . $current_series . ' 卷.', url('system/database/backup/',$current), 'info');
					}
					
				}
				
				if (empty($result) || count($result['result']) < $size) {
					break;
				}
				$index++;
			}
		}
	
		//结束标识	
		$bakfile = $bakdir . "/volume-{$volume_suffix}-{$series}.sql";
		$dump .= "\n\n----WeEngine MySQL Dump End";
		file_put_contents($bakfile, $dump);
		itoast('数据已经备份完成', url('system/database/'), 'success');	
	}
}

//还原
if($do == 'restore') {
	$_W['page']['title'] = '还原 - 数据库 - 常用系统工具 - 系统管理';
	$reduction = array();
	//获取备份目录下数据库备份数组
	$reduction = system_database_backup();
	//备份还原
	if (!empty($_GPC['restore_dirname'])) {
		$restore = array (
				'restore_dirname' => $_GPC['restore_dirname'],
				'restore_volume_prefix' => $_GPC['restore_volume_prefix'],
				'restore_volume_sizes' => $_GPC['restore_volume_sizes'],
		);
		system_database_restore($reduction, $restore);
	}
	//删除备份	
	if ($_GPC['delete_dirname']) {
		$delete_dirname = $_GPC['delete_dirname'];
		system_database_delete($reduction, $delete_dirname);
	}
}

//数据库结构整理
if ($do == 'trim') {
	if ($_W['ispost']) {
		$type = $_GPC['type'];
		$data = $_GPC['data'];
		$table = $_GPC['table'];
		if ($type == 'field') {
			$sql = "ALTER TABLE `$table` DROP `$data`";
			if (false !== pdo_query($sql, $params)) {
				exit('success');
			}
		} elseif ($type == 'index') {
			$sql = "ALTER TABLE `$table` DROP INDEX `$data`";
			if (false !== pdo_query($sql, $params)) {
				exit('success');
			}
		}
		exit();
	}
	
	$r = cloud_prepare();
	if(is_error($r)) {
		itoast($r['message'], url('cloud/profile'), 'error');
	}
	
	$upgrade = cloud_schema();
	$schemas = $upgrade['schemas'];
	/*
	 * $schemas 是存在差异的数据库表
	 * 遍历$schemas, 读取本地数据库. 然后使用compare
	*/
	
	if (!empty($schemas)) {
		foreach ($schemas as $key=>$value) {
			$tablename =  substr($value['tablename'], 4);
			$struct = db_table_schema(pdo(), $tablename);
			if (!empty($struct)) {
				$temp = db_schema_compare($schemas[$key],$struct);
				if (!empty($temp['fields']['less'])) {
					$diff[$tablename]['name'] = $value['tablename'];
					foreach ($temp['fields']['less'] as $key=>$value) {
						$diff[$tablename]['fields'][] = $value;
					}
				}
				if (!empty($temp['indexes']['less'])) {
					$diff[$tablename]['name'] = $value['tablename'];
					foreach ($temp['indexes']['less'] as $key=>$value) {
						$diff[$tablename]['indexes'][] = $value;
					}
				}
			}
		}
	}
}
//优化
if ($do == 'optimize') {
	$_W['page']['title'] = '优化 - 数据库 - 常用系统工具 - 系统管理';
	$optimize_table = array();
	$sql = "SHOW TABLE STATUS LIKE '{$_W['config']['db']['tablepre']}%'";
	$tables = pdo_fetchall($sql);
	foreach ($tables as $tableinfo) {
		if ($tableinfo['Engine'] == 'InnoDB') {
			continue;
		}
		if (!empty($tableinfo) && !empty($tableinfo['Data_free'])) {
			$row = array(
				'title' => $tableinfo['Name'],
				'type' => $tableinfo['Engine'],
				'rows' => $tableinfo['Rows'],
				'data' => sizecount($tableinfo['Data_length']),
				'index' => sizecount($tableinfo['Index_length']),
				'free' => sizecount($tableinfo['Data_free'])
			);
			$optimize_table[$row['title']] = $row;
		}
	}

	if (checksubmit()) {
		foreach ($_GPC['select'] as $tablename) {
			if (!empty($optimize_table[$tablename])) {
				$sql = "OPTIMIZE TABLE {$tablename}";
				pdo_fetch($sql);
			}
		}
		itoast('数据表优化成功.', 'refresh', 'success');
	}
}
//运行SQL
if ($do == 'run') {
	$_W['page']['title'] = '运行SQL - 数据库 - 常用系统工具 - 系统管理';
	if (!DEVELOPMENT) {
		itoast('请先开启开发模式后再使用此功能', referer(), 'info');
	}
	if (checksubmit()) {
		$sql = $_POST['sql'];
		pdo_run($sql);
		itoast('查询执行成功.', 'refresh', 'success');
	}
}

template('system/database');

