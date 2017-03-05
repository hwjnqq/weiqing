<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;
$ops = array('display', 'recharge_pay');
$op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'display';

check_params();
$uid = mc_openid2uid($_W['openid']);

if ($op == 'recharge_add') {
    $type = trim($_GPC['__input']['type']) ? trim($_GPC['__input']['type']) : 'credit';
    if ($type == 'credit') {
        $fee = floatval($_GPC['__input']['fee']);
        if (empty($fee) || $fee <= 0) {
            message('请选择充值金额', referer(), 'error');
        }
        $backtype = trim($_GPC['__input']['backtype']);
        $back= floatval($_GPC['__input']['back']);
        $charge_record = array(
            'uid' => $uid,
            'openid' => $_W['openid'],
            'uniacid' => $_W['uniacid'],
            'tid' => date('YmdHi').random(8, 1),
            'fee' => $fee,
            'type' => 'credit',
            'tag' => $back,
            'backtype' => $backtype,
            'status' => 0,
            'createtime' => TIMESTAMP,
        );
        if (!pdo_insert('mc_credits_recharge', $charge_record)) {
            message('创建充值订单失败，请重试！', url('entry', array('m' => 'we7_storex', 'do' => 'recharge')), 'error');
        }
        $recharge_id = pdo_insertid();
        message(error(0, $recharge_id), '', 'ajax');
    }
}
if ($op == 'recharge_pay') {
    $charge_record = pdo_get('mc_credits_recharge', array('id' => intval($_GPC['__input']['id'])));
    $params = array(
        'tid' => $charge_record['tid'],
        'title' => '万能小店余额充值',
        'fee' => $charge_record['fee'],
        'user' => $uid
    );
    $pay_info = $this->pay($params, $mine);
    message(error(0, $pay_info), '', 'ajax');
}