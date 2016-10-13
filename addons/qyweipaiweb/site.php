<?php
/**
 * 微拍模块微站定义
 *
 * @author 清逸
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class QyweipaiwebModuleSite extends WeModuleSite {
	public $tablename = 'qywpweb_reply';
	public $tablenamelog = 'qywpweb_log';
	public $tablenamexfm = 'qywpweb_xfm';

	//这个操作被定义用来呈现 规则列表
	public function doWebAwardlist() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			$sql = "SELECT id,pic FROM ".tablename('qywpweb_reply')." WHERE id  IN  ('".implode("','", $_GPC['select'])."')";
			$replies = pdo_fetchall($sql);
			if (!empty($replies)) {
				load()->func('file');
				foreach ($replies as $index => $row) {
					file_delete($row['pic']);
				}
			}
			pdo_delete($this->tablename, " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', $this->createWebUrl('awardlist', array('id' => $id, 'page' => $_GPC['page'])));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$sql = "SELECT a.*, b.nickname, b.realname FROM " . tablename('qywpweb_reply') . " AS a INNER JOIN " . tablename('mc_members') . "
				AS b ON a.fid = b.uid WHERE a.rid = '{$id}' ORDER BY a.create_time DESC LIMIT ".($pindex - 1) * $psize.",{$psize}";
		$list = pdo_fetchall($sql);
		if (!empty($list)) {
			$sql = 'SELECT COUNT(*) FROM ' . tablename('qywpweb_reply') . " WHERE rid = '{$id}'";
			$total = pdo_fetchcolumn($sql);
			$sql = 'SELECT COUNT(*) FROM ' . tablename('qywpweb_reply') . " WHERE rid = '{$id}' and create_time > '".strtotime(date('Y-m-d'))."'";
			$total1 = pdo_fetchcolumn($sql);
			$pager = pagination($total, $pindex, $psize);
		}
		include $this->template('manage');
	}

	//这个操作被定义用来呈现 规则列表
	public function doWebloglist() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete($this->tablenamelog, " id  IN  ('" . implode("','", $_GPC['select']) . "')");
			message('删除成功！', $this->createWebUrl('loglist', array('id' => $id, 'page' => $_GPC['page'])));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$sql = "SELECT a.*, b.nickname, b.realname FROM " . tablename('qywpweb_log') . " AS a INNER JOIN " . tablename('mc_members') . " AS b
				ON a.fid = b.uid WHERE a.rid = '{$id}' ORDER BY a.create_time DESC LIMIT " . ($pindex - 1) * $psize . ",{$psize}";
		$list = pdo_fetchall($sql);
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('qywpweb_log') . " WHERE rid = '{$id}'");
			$pager = pagination($total, $pindex, $psize);
		}
		include $this->template('loglist');
	}

	//这个操作被定义用来呈现 规则列表
	public function doWebxfmlist() {
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete($this->tablenamexfm, " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', $this->createWebUrl('xfmlist', array('id' => $id, 'page' => $_GPC['page'])));
		}
        $where = 'where rid=:rid and weid=:weid';
        $params = array(':rid' => $id, ':weid' => $_W['uniacid']);
		if (!empty($_GPC['status'])) {
			if ($_GPC['status'] < 3) {
				$where .= ' and status = :status';
				$params[':status'] = $_GPC['status'] - 1;
			} else {
				$where .= ' and stype = :status';
				$params[':status'] = $_GPC['status'] - 3;
			}
		}
		if (!empty($_GPC['keywords'])) {
			$where .= ' and xfm like :keywords';
			$params[':keywords'] = "%{$_GPC['keywords']}%";
		}

		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$sql = "SELECT a.id,a.rid,a.xfm,a.stype,a.status,a.create_time,a.use_time FROM ".tablename('qywpweb_xfm')." AS a " . $where .
				" ORDER BY a.id DESC LIMIT ".($pindex - 1) * $psize.",{$psize}";
		$list = pdo_fetchall($sql, $params);
		$total = $total0 = $total1 = $num = $num2 = $num3 = 0;
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('qywpweb_xfm') . $where . "", $params);
			$total1 = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('qywpweb_xfm') . $where . " and status=1", $params);
			$total0 = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('qywpweb_xfm') . $where . " and status=0", $params);
			$pager = pagination($total, $pindex, $psize);
		}
        $num1 = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('qywpweb_xfm'). " WHERE rid = '{$id}'");
        $num2 = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('qywpweb_xfm').  " WHERE rid = '{$id}' and status=1");
        $num3 = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('qywpweb_xfm'). " WHERE rid = '{$id}' and status=0");
		include $this->template('xfmlist');
	}

	//这个操作被定义用来呈现 规则列表
	public function doWebaddxfm() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$stype = $_GPC['stype'];
		if ((!empty($id)) && ($stype==0 || $stype==1)) {
          	for ($i = 0; $i <30; $i++) {
				$insert = array(
					'rid' => $id,
					'weid' => $_W['weid'],
					'xfm' => random(5, true),
					'status' => 0,
					'stype' => $stype,
					'create_time' => time()
				);
				$ids = pdo_insert($this->tablenamexfm, $insert);
			}
			message('消费码生成成功！', $this->createWebUrl('xfmlist', array('id' => $id, 'page' => $_GPC['page'])));
		}
	}

    public function dowebdownload() {
        require_once 'download.php';
    }
}