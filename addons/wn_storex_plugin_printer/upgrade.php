<?php
$printer_set = pdo_getall('storex_plugin_printer_set');
if (!empty($printer_set) && is_array($printer_set)) {
	foreach ($printer_set as $key => $value) {
		if (strlen($value['printerids']) > 4) {
			$printerids = iunserializer($value['printerids']);
			if (!empty($printerids) && is_array($printerids)) {
				foreach ($printerids as $k => $val) {
					if ($val == 1) {
						$insert_data = array(
							'uniacid' => $value['uniacid'],
							'storeid' => $value['storeid'],
							'printerids' => $k
						);
						pdo_insert('storex_plugin_printer_set', $insert_data);
					}	
				}
			}
			pdo_delete('storex_plugin_printer_set', array('id' => $value['id']));
		}
	}
}
if (!pdo_fieldexists('storex_plugin_printer_set', 'printerids')) {
	pdo_query("ALTER TABLE " . tablename('storex_plugin_printer_set') . " CHANGE `printerids` `printerids` INT UNSIGNED NOT NULL COMMENT '打印机ID';");
}