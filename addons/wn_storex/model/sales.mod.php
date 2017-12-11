<?php
/**
 * 营业额记录
 * @param array $sales_info,(storeid=>店铺id, sum_price=>金额)
 * @return boolean 
 */
function sales_update($sales_info) {
	global $_W;
	if (empty($sales_info['storeid']) || empty($sales_info['sum_price'])) {
		return error(-1, '参数错误');
	}
	$today_sales = pdo_get('storex_sales', array('uniacid' => $_W['uniacid'], 'storeid' => $sales_info['storeid'], 'date' => date('Ymd')));
	if (empty($today_sales)) {
		$sales_update = array(
			'uniacid' => $_W['uniacid'],
			'storeid' => $sales_info['storeid'],
			'cumulate' => $sales_info['sum_price'],
			'date' => date('Ymd')
		);
		pdo_insert('storex_sales', $sales_update);
	} else {
		$sales_update = array(
			'cumulate' => $today_sales['cumulate'] + $sales_info['sum_price'],
		);
		pdo_update('storex_sales', $sales_update, array('id' => $today_sales));
	}
	return true;
}