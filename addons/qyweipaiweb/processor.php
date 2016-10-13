<?php
/**
 * 微拍模块处理程序
 *
 * @author 清逸
 * @url
 */
defined('IN_IA') or exit('Access Denied');

class QyweipaiwebModuleProcessor extends WeModuleProcessor {

    public $tablename = 'qywpweb';

    //这里定义此模块进行消息处理时的具体过程, 请查看微擎文档来编写你的代码
    public function respond() {
        $content = $this->message['content'];
        global $_W;
        load()->func('file');
        load()->model('mc');
        $rid = $this->rule;
        $message = $this->message;
        $content = $message['content'];
        $from_user = $message['from'];
        $member = mc_fetch($from_user, array('uid'));

        $sql = "SELECT `maxnum`, `dcmaxnum`, `pwd`, `msg`, `msg_succ`, `msg_fail`, `status`, `lyok`, `ispwd`, `isck`, `isxf`, `jcok` FROM " .
            tablename($this->tablename) . " WHERE rid = :rid ORDER BY `id` DESC";
        $reply = pdo_fetch($sql, array(':rid' => $rid));

        if (!$this->inContext) {

            $this->beginContext(300);

            if (empty($reply['isck']) && $reply['isck'] <> 1) {
                $sql = "SELECT COUNT(*) FROM " . tablename('qywpweb_reply') . " WHERE  rid = '" . $rid . "' and create_time > '" . strtotime(date('Y-m-d')) .
                    "' AND  fid = '" . $fans['id'] . "'";
                $total = pdo_fetchcolumn($sql);
            } elseif ($reply['isck'] == 2) {
                $date = date('Y-m-d');
                $w = date('w', TIMESTAMP);
                $start = strtotime("$date -" . ($w ? $w - 1 : 6) . ' days');
                $end = strtotime(date('Y-m-d', $start) . " +7 days");
                $sql = "SELECT COUNT(*) FROM " . tablename('qywpweb_reply') . " WHERE  rid = '" . $rid . "' and create_time >= '{$start}' AND create_time <
                        '{$end}' AND  fid = '" . $fans['id'] . "'";
                $total = pdo_fetchcolumn($sql);
            } else {
                $sql = "SELECT COUNT(*) FROM " . tablename('qywpweb_reply') . " WHERE  rid = '" . $rid . "' AND  fid = '" . $fans['id'] . "'";
                $total = pdo_fetchcolumn($sql);
            }


            if (empty($reply['status']) && $reply['status'] <> 1) {
                $this->endContext();
                session_destroy();
                return $this->respText('活动还没启动呢！');
            }


            if (!empty($reply['maxnum']) && $total >= $reply['maxnum']) {
                $sql = "SELECT * FROM " . tablename('qywpweb_count') . " WHERE rid = :rid AND fid = :fid order by id desc";
                $fuser = pdo_fetch($sql, array(':rid' => $rid, ':fid' => $fans['id']));
                if (empty($fuser) || $fuser['count'] <= 0) {
                    if ($reply['isxf'] == 1) {
                        $_SESSION['img'] = '0';
                        $_SESSION['xfmm'] = '1';
                        return $this->respText($reply['msg'] . '必须通过消费码来参与了，请输入消费码：');
                    } else {
                        $this->endContext();
                        session_destroy();
                        return $this->respText('你本次活动的参与次数已用完！');
                    }
                } else {
                    $_SESSION['ucount'] = $fuser['count'];
                    $_SESSION['uid'] = $fuser['id'];
                }
            }


            if (!empty($reply['pwd']) && (($reply['ispwd'] == 1) || ($reply['isxf'] == 1))) {
                $_SESSION['img'] = '0';
                if ($reply['ispwd'] == 1) {
                    return $this->respText($reply['msg'] . '请输入屏幕上的活动验证码：');
                } else {
                    return $this->respText($reply['msg'] . '必须通过消费码来参与了，请输入你的消费码：');
                }
            } else {
                $_SESSION['img'] = '1';
                return $this->respText($reply['msg'] . '请选择一张照片上传(点对话框后面 + 号，选择图片)：');
            }
            $_SESSION['imgnum'] = 1;

        } else {

            if ($content == '退出') {
                $this->endContext();
                session_destroy();
                return $this->respText('您已回到普通模式！');
            }

            if (($reply['ispwd'] == 1) && empty($_SESSION['img']) && ($_SESSION['xfmm'] != '1')) {
                if (($content == $reply['pwd']) && ($this->message['type'] == 'text')) {
                    $_SESSION['yzps'] = '0';
                } else {
                    $_SESSION['yzps'] = '1';
                }
            }

            if (($reply['isxf'] == 1) && ($_SESSION['yzps'] != '0') && empty($_SESSION['img'])) {
                $reply1 = pdo_fetch("SELECT  xfm FROM " . tablename('qywpweb_xfm') . " WHERE rid = :rid AND xfm = :xfm and status=0 LIMIT 1", array(':rid' => $rid, ':xfm' => $content));
                if (($content == $reply1['xfm']) && ($this->message['type'] == 'text')) {
                    $_SESSION['yzps'] = '0';
                    $_SESSION['xfmps'] = $reply1['xfm'];
                } else {
                    $_SESSION['yzps'] = '1';
                }
            }

            if (($_SESSION['yzps'] == '1') && empty($_SESSION['img'])) {
                if ($_SESSION['xfmm'] == '1') {
                    return $this->respText('你只有输入正确的消费码才能参与，请输入：');
                } else {
                    return $this->respText('输入的不对哦，请输入正确的验证码或消费码：');
                }
            } else {

                // 更新参与码
                if ($_SESSION['img'] == '0') {
                    if (empty($_SESSION['xfmps'])) {
                        $filenamep = 'qywp/' . $rid . '/pwd.txt';
                        $pwd1 = random(6, true);
                        file_write($filenamep, 'lyqywp' . $pwd1);
                        pdo_update($this->tablename, array('pwd' => $pwd1), array('rid' => $rid));
                    }
                    $_SESSION['img'] = '1';
                    return $this->respText('请选择一张照片上传(点对话框后面 + 号，选择图片)：');
                }


                if ($_SESSION['img'] == '1') {
                    if (($this->message['type'] == 'image') && empty($_SESSION['piccontent'])) {

                        // 上传图片保存
                        load()->func('communication');
                        $image = ihttp_request($this->message['picurl']);
                        $time = random(13);
                        $filename = 'qywp/' . $rid . '/' . $time . '.jpg';
                        file_write($filename, $image['content']);

                        $_SESSION['piccontent'] = $filename;

                        // 保存上传图片地址
                        $_SESSION['upImage'] = $this->message['picurl'];

                        // 图片自助裁剪
                        if ($reply['jcok'] == 1) {
                            if ($reply['lyok'] == 1) {
                                $_SESSION['img'] = '2';
                                return $this->respText('上传照片成功！如需裁剪 <a href="' . $_W['siteroot'] . '/lomo/cutimage.php?pic=/' . $GLOBALS['_W']['config']['upload']['attachdir'] . $filename . '">请点这里</a>。最后一步，请输入你想留在照片上的话（10个字以内），输入 # 则放弃留言：');
                            } else {
                                $_SESSION['img'] = '3';
                                return $this->respText('上传照片成功！如需裁剪 <a href="' . $_W['siteroot'] . '/lomo/cutimage.php?pic=/' . $GLOBALS['_W']['config']['upload']['attachdir'] . $filename . '">请点这里</a>。最后一步，直接回复 # 开始打印照片。');
                            }
                        } else {
                            if ($reply['lyok'] == 1) {
                                $_SESSION['img'] = '2';
                                return $this->respText('上传照片成功！最后一步，请输入你想留在照片上的话（10个字以内），输入 # 则放弃留言：');
                            } else {
                                $_SESSION['img'] = '3';
                            }
                        }
                    } else {
                        return $this->respText('只能传照片哦！');
                    }
                }

                // 图片文字
                if ($_SESSION['img'] == '2') {
                    if (($this->message['type'] != 'text') || empty($content)) {
                        return $this->respText('只能输入文字：');
                    } elseif (mb_strlen($content) >= 33) {
                        return $this->respText('你输入的文字超长了吧，重新输入：');
                    } else {
                        if (($content == '#') || ($content == '＃')) {
                            $_SESSION['msg'] = '';
                        } else {
                            $_SESSION['msg'] = $content;
                        }
                        $_SESSION['img'] = '3';
                    }
                }

                if ($_SESSION['img'] == '3') {
                    // 更新图片编号信息
                    $filenamec = 'qywp/' . $rid . '/count.txt';
                    $buffer = '10000';
                    $file_name = ATTACHMENT_ROOT . '/' .  $filenamec;
                    if (file_exists($file_name)) {
                        $fp = fopen($file_name, 'r');
                        while (!feof($fp)) {
                            $buffer = fgets($fp);
                        }
                        fclose($fp);
                    } else {
                        file_write($filenamec, $buffer);
                    }
                    $buffer++;
                    file_write($filenamec, $buffer);

                    // 更新打印规则文件
                    $filenamem = 'qywp/' . $rid . '/msg.html';
                    $msghead = 'lyqywp<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
                    $msgwrite = '<wp><id>' . $buffer . '</id><purl>' . substr($_SESSION['piccontent'], -17) . '</purl><msg>' . $_SESSION['msg'] . '</msg></wp>';
                    $file_name = ATTACHMENT_ROOT . '/' . $filenamem;
                    if (file_exists($file_name)) {
                        file_put_contents($file_name, $msgwrite, FILE_APPEND);
                    } else {
                        $msgwrite = $msghead . $msgwrite;
                        file_write($filenamem, $msgwrite);
                    }

                    // 图片记录
                    $insert = array(
                        'rid' => $rid,
                        'fid' => $member['uid'],
                        'weid' => $_W['uniacid'],
                        'msg' => $_SESSION['msg'],
                        'pic' => $_SESSION['piccontent'],
                        'bianhao' => $buffer,
                        'create_time' => TIMESTAMP
                    );


                    $sql = 'SELECT `fid`, `bianhao`, `create_time` FROM ' . tablename('qywpweb_reply') . " WHERE `rid` = :rid ORDER BY `id` DESC";
                    $reply2 = pdo_fetch($sql, array(':rid' => $rid));

                    if ((($fans['id'] == $reply2['fid']) && ((time() - $reply2['create_time']) <= 5)) || ($reply2['bianhao'] == $buffer)) {
                        $cfps = '1';
                    } else {
                        $cfps = '0';
                    }

                    if ($cfps == '0') {
                        if ($id = pdo_insert('qywpweb_reply', $insert)) {
                            // 更新该规则粉丝使用次数
                            if ((!empty($_SESSION['uid'])) && (empty($_SESSION['xfmps']))) {
                                $data = array(
                                    'count' => $_SESSION['ucount'] - 1,
                                );
                                pdo_update('qywpweb_count', $data, array('id' => $_SESSION['uid']));
                            }

                            // 更新消费码状态
                            if (!empty($_SESSION['xfmps'])) {
                                $data = array(
                                    'status' => 1,
                                    'use_time' => time()
                                );
                                pdo_update('qywpweb_xfm', $data, array('rid' => $rid, 'xfm' => $_SESSION['xfmps']));
                            }

                            $_SESSION['imgnum']++;

                            if (($_SESSION['imgnum'] < $reply['dcmaxnum']) && (!empty($_SESSION['xfmps']))) {
                                $_SESSION['img'] = '1';
                                $_SESSION['piccontent'] = 0;
                                $_SESSION['msg'] = '';
                                return $this->respText($reply['msg_succ'] . ' 你的照片编号为' . $buffer . "。还可继续传一张照片（退出请输入 退出）");
                            } else {
                                $this->endContext();
                                session_destroy();
                                return $this->respText($reply['msg_succ'] . ' 你的照片编号为' . $buffer . "。谢谢使用！");
                            }
                        } else {
                            $this->endContext();
                            session_destroy();
                            return $this->respText($reply['msg_fail']);
                        }
                    } else {
                        $this->endContext();
                        session_destroy();
                    }
                }
                // 结束会话
                $this->endContext();
                session_destroy();
            }
        }
    }

}