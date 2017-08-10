<?php
if (!pdo_fieldexists('storex_activity_exchange_trades', 'num')) {
	pdo_query("ALTER TABLE " . tablename('storex_activity_exchange_trades') . " ADD `num` INT(11) NOT NULL COMMENT '数量';");
}
if (!pdo_fieldexists('storex_activity_exchange_trades_shipping', 'num')) {
	pdo_query("ALTER TABLE " . tablename('storex_activity_exchange_trades_shipping') . " ADD `num` INT(11) NOT NULL COMMENT '数量';");
}

$exchange_trades = pdo_getall('storex_activity_exchange_trades', array('num' => 0), array('tid', 'num'));
if (!empty($exchange_trades) && is_array($exchange_trades)) {
	foreach ($exchange_trades as $val) {
		if (empty($val['num'])) {
			pdo_update('storex_activity_exchange_trades', array('num' => 1), array('tid' => $val['tid']));
		}
	}
}
$trades_shipping = pdo_getall('storex_activity_exchange_trades_shipping', array('num' => 0), array('tid', 'num'));
if (!empty($trades_shipping) && is_array($trades_shipping)) {
	foreach ($trades_shipping as $val) {
		if (empty($val['num'])) {
			pdo_update('storex_activity_exchange_trades_shipping', array('num' => 1), array('tid' => $val['tid']));
		}
	}
}