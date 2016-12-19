<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 2016/12/2
 * Time: 16:23
 * 编辑应用套餐
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('display', 'delete', 'post', 'save');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';

load()->model('module');

if ($do == 'save') {
	$param = $_GPC['__input'];
	if (!empty($param['id'])) {
		$name_exist = pdo_get('uni_group', array('uniacid' => 0, 'id <>' => $param['id'], 'name' => $param['name']));
		if (!empty($name_exist)) {
			message(error(1, '套餐名已存在'), '', 'ajax');
		}
		$param['modules'] = iserializer(array_keys($param['modules']));
		foreach ($param['templates'] as $key => $template) {
			$param['templates'][] = $template['id'];
			unset($param['templates'][$key]);
		}
		$param['templates'] = iserializer($param['templates']);
		$groupid = $param['id'];
		unset($param['id']);
		pdo_update('uni_group', $param, array('id' => $groupid));
		message(error(0, url('system/module_group')), '', 'ajax');
	} else {
		$name_exist = pdo_get('uni_group', array('uniacid' => 0, 'name' => $param['name']));
		if (!empty($name_exist)) {
			message(error(1, '套餐名已存在'), '', 'ajax');
		}
		$param['modules'] = iserializer(array_keys($param['modules']));
		foreach ($param['templates'] as $key => $template) {
			$param['templates'][] = $template['id'];
			unset($param['templates'][$key]);
		}
		$param['templates'] = iserializer($param['templates']);
		pdo_insert('uni_group', $param);
		message(error(0, url('system/module_group')), '', 'ajax');
	}
}

if ($do == 'display') {
	$_W['page']['title'] = '应用套餐列表';

	$param = array('uniacid' => 0);
	if (!empty($_GPC['name'])) {
		$param['name like'] = "%". trim($_GPC['name']) ."%";
	}
	$modules_group_list = pdo_getall('uni_group', $param);
	if (!empty($modules_group_list)) {
		foreach ($modules_group_list as &$group) {
			if (!empty($group['modules'])) {
				$modules = iunserializer($group['modules']);
				if (is_array($modules)) {
					$group['modules'] = pdo_fetchall("SELECT name, title FROM ".tablename('modules')." WHERE `name` IN ('".implode("','", $modules)."')");
				}
			}
			if (!empty($group['templates'])) {
				$templates = iunserializer($group['templates']);
				if (is_array($templates)) {
					$group['templates'] = pdo_fetchall("SELECT name, title FROM ".tablename('site_templates')." WHERE id IN ('".implode("','", $templates)."')");
				}
			}
		}
	}
	if (empty($_GPC['name']) || strexists('基础服务', $_GPC['name'])) {
		array_unshift($modules_group_list, array('name' => '基础服务'));

	}
	if (empty($_GPC['name']) || strexists('所有服务', $_GPC['name'])) {
		array_unshift($modules_group_list, array('name' => '所有服务'));
	}
}

if ($do == 'delete') {
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		pdo_delete('uni_group', array('id' => $id));
		cache_build_account_modules();
	}
	message('删除成功！', referer(), 'success');
}

if ($do == 'post') {
	$id = intval($_GPC['id']);
	$_W['page']['title'] = $id ? '编辑应用套餐' : '添加应用套餐';

	$module_list = pdo_getall('modules', array('issystem' => 0), array(), 'name');
	$template_list = pdo_getall('site_templates',array(), array(), 'name');
	$group_have_module = array();
	$group_have_template = array();
	if (!empty($id)) {
		$module_group = pdo_get('uni_group', array('id' => $id));
		$module_group['modules'] = empty($module_group['modules']) ? array() : iunserializer($module_group['modules']);
		if (!empty($module_group['modules'])) {
			foreach ($module_group['modules'] as $module_name) {
				$module_info = pdo_get('modules', array('name' => $module_name));
				if (empty($module_info)) {
					continue;
				}
				$group_have_module[$module_info['name']] = $module_info;
				if (file_exists(IA_ROOT.'/addons/'.$module_name.'/icon-custom.jpg')) {
					$group_have_module[$module_info['name']]['logo'] = tomedia(IA_ROOT.'/addons/'.$module_name.'/icon-custom.jpg');
				} else {
					$group_have_module[$module_info['name']]['logo'] = tomedia(IA_ROOT.'/addons/'.$module_name.'/icon.jpg');
				}
			}
		}
		$module_group['templates'] = empty($module_group['templates']) ? array() : iunserializer($module_group['templates']);
		if (!empty($module_group['templates'])) {
			foreach ($module_group['templates'] as $templateid) {
				$template_info = pdo_get('site_templates', array('id' => $templateid));
				if (!empty($template_info)) {
					$group_have_template[$template_info['name']] = $template_info;
				}
			}
		}
	}
	$group_not_have_module = array();//套餐未拥有模块
	if (!empty($module_list)) {
		foreach ($module_list as $module_info) {
			if (!in_array($module_info['name'], array_keys($group_have_module))) {
				$group_not_have_module[$module_info['name']] = $module_info;
				if (file_exists(IA_ROOT.'/addons/'.$module_name.'/icon-custom.jpg')) {
					$group_not_have_module[$module_info['name']]['logo'] = tomedia(IA_ROOT.'/addons/'.$module_info['name'].'/icon-custom.jpg');
				} else {
					$group_not_have_module[$module_info['name']]['logo'] = tomedia(IA_ROOT.'/addons/'.$module_info['name'].'/icon.jpg');
				}
			}
		}
	}

	$group_not_have_template = array();//套餐未拥有模板
	if (!empty($template_list)) {
		foreach ($template_list as $template) {
			if (!in_array($template['name'], array_keys($group_have_template))) {
				$group_not_have_template[$template['name']] =  $template;
			}
		}
	}

	if (checksubmit('submit')) {
		if (empty($_GPC['name'])) {
			message('请输入公众号组名称！');
		}
		$data = array(
			'name' => $_GPC['name'],
			'modules' => iserializer($_GPC['module']),
			'templates' => iserializer($_GPC['template'])
		);
		if (empty($id)) {
			pdo_insert('uni_group', $data);
		} else {
			pdo_update('uni_group', $data, array('id' => $id));
			cache_build_account_modules();
		}
		module_build_privileges();
		message('公众号组更新成功！', url('system/module_group/display'), 'success');
	}
}

template('system/module_group');