<?php

$we7_coupon = pdo_get('modules', array('name' => 'we7_coupon'));
if (!empty($we7_coupon)) {
	pdo_update('modules', array('issystem' => '1', 'settings' => '2'), array('mid' => $we7_coupon['mid']));
}

