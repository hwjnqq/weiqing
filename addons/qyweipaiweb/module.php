<?php
/**
 * 微拍模块定义
 *
 * @author 清逸
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class QyweipaiwebModule extends WeModule {
	public $tablename = 'qywpweb';

	//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
	public function fieldsFormDisplay($rid = 0) {
		global $_W;
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));		
 		}
		load()->func('tpl');
		include $this->template('form');
	}

	//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
	public function fieldsFormValidate($rid = 0) {
		return true;
	}

	public function fieldsFormSubmit($rid) {
		global $_GPC, $_W;
		$id = intval($_GPC['reply_id']);
		empty($_GPC['msg']) ? $_GPC['msg'] = '欢迎进入微拍活动！' : '';
		empty($_GPC['msg_succ']) ? $_GPC['msg_succ'] = '参与活动成功。' : '';
		empty($_GPC['msg_fail']) ? $_GPC['msg_fail'] = '提交失败，请重试。' : '';
		if (($_GPC['adtime1'] > 1000) || ($_GPC['adtime1'] < 10)) {
			$adtime1 = 10;
		} else {
			$adtime1 = $_GPC['adtime1'];
		}

		$insert = array(
			'rid' => $rid,
			'weid' => $_W['weid'],
			'maxnum' => $_GPC['maxnum'],
			'dcmaxnum' => $_GPC['dcmaxnum'],
			'pwd' => '111111',
			'mpwd' => $_GPC['mpwd'],
			'picture1' => $_GPC['picture1'],
			'picture2' => $_GPC['picture2'],
			'picture3' => $_GPC['picture3'],
			'msg' => $_GPC['msg'],
			'msg_succ' => $_GPC['msg_succ'],
			'msg_fail' => $_GPC['msg_fail'],
			'status' => intval($_GPC['wpstatus']),
			'lyok' => intval($_GPC['wplyok']),
			'jcok' => intval($_GPC['jcok']),
			'isck' => $_GPC['isck'],
			'isxf' => $_GPC['isxf'],
			'ispwd' => intval($_GPC['ispwd']),
			'adstatus' => $_GPC['adstatus'],
			'ad1type' => $_GPC['ad1type'],
			'acode' => $_GPC['acode'],
			'apage' => $_GPC['apage'],
			'wifis' => $_GPC['wifis'],
			'wifif' => $_GPC['wifif'],
			'compass' => $_GPC['compass'],
			'adtime1' => $adtime1,
			'ad1url1' => $_GPC['ad1url1'],
			'ad2url1' => $_GPC['ad2url1'],
			'ad2url2' => $_GPC['ad2url2'],
			'ad2url3' => $_GPC['ad2url3'],
			'ad3url1' => $_GPC['ad3url1'],
			'ad3url2' => $_GPC['ad3url2'],
			'ad3url3' => $_GPC['ad3url3'],
			'ad4url1' => $_GPC['ad4url1'],
			'ad4url2' => $_GPC['ad4url2'],
			'ad4url3' => $_GPC['ad4url3']
		);

		if (empty($id)) {
			$id = pdo_insert($this->tablename, $insert);
		} else {
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
		load()->func('file');

		// 动态参与码
		$filenamep = 'qywp/' . $rid . '/pwd.txt';
		$pwd1 = 'lyqywp111111';
		if (($_GPC['isxf'] == 1) && ($_GPC['ispwd'] == 0)) {
			$pwd1 = 'lyqywp需要消费码参与';
		}
		file_write($filenamep, $pwd1);

		// 照片模板
		$filename = 'qywp/' . $rid . '/moban.txt';
		$s = 'lyqywp<s>'  . tomedia($_GPC['picture1']) . '</s>';
		$h = $s . '<h>' . tomedia($_GPC['picture2']) . '</h><l>' . tomedia($_GPC['picture3']) . '</l>';
		file_write($filename, $h);

		// 广告信息
		$filenamead = 'qywp/' . $rid . '/ad.txt';
		if ($_GPC['adstatus'] == 1) {
			$ad = 'lyqywp<cfg>1</cfg>';
			$ad = $ad . '<ad1><type>' . $_GPC['ad1type'] . '</type><path>' . $_GPC['ad1url1'] . '</path></ad1>';
			$ad = $ad . '<ad2><count>3</count><timer>' . $adtime1 . '</timer><path1>' . $_GPC['ad2url1'] . '</path1><path2>' . $_GPC['ad2url2'] . '</path2><path3>' . $_GPC['ad2url3'] . '</path3></ad2>';
			$ad = $ad . '<ad3><count>3</count><timer>' . $adtime1 . '</timer><path1>' . $_GPC['ad3url1'] . '</path1><path2>' . $_GPC['ad3url2'] . '</path2><path3>' . $_GPC['ad3url3'] . '</path3></ad3>';
			$ad = $ad . '<ad4><count>3</count><timer>' . $adtime1 . '</timer><path1>' . $_GPC['ad4url1'] . '</path1><path2>' . $_GPC['ad4url2'] . '</path2><path3>' . $_GPC['ad4url3'] . '</path3></ad4>';
			$ad = $ad . '<other><page>' . $_GPC['apage'] . '</page><wifis>' . $_GPC['wifis'] . '</wifis><wifif>' . $_GPC['wifif'] . '</wifif><compass>' . $_GPC['compass'] . '</compass><code>' . $_GPC['acode'] . '</code></other>';
		} else {
			$ad = 'lyqywp<cfg>0</cfg>';
		}
		file_write($filenamead, $ad);

      	return true;
	}

	//删除规则时调用，这里 $rid 为对应的规则编号
	public function ruleDeleted($rid) {
		global $_W;
		$replies = pdo_fetchall("SELECT id,pic FROM " . tablename('qywpweb_reply') . " WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
			foreach ($replies as $index => $row) {
				file_delete($row['pic']);
				$deleteid[] = $row['id'];
			}
		}
		pdo_delete('qywpweb_reply', " id IN ('" . implode("','", $deleteid) . "')");
		pdo_delete($this->tablename, "rid =" . $rid . "");
		pdo_delete('qywpweb_count', array('rid' => $rid));
		pdo_delete('qywpweb_log', array('rid' => $rid));
		load()->func('file');
		rmdirs(IA_ROOT . '/' . $_W['config']['upload']['attachdir'] . '/qywp/' . $rid);
		return true;
	}


}