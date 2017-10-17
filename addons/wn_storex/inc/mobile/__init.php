<?php
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
if (!empty($_GPC['agentid'])) {
    $member_agent = pdo_get('storex_member', array('from_user' => $_W['openid']), array('id', 'from_user', 'agentid'));
    if (empty($_GPC['agentid'])) {
        pdo_update('storex_member', array('agentid' => $_GPC['agentid']), array('id' => $member_agent['id']));
    }
}