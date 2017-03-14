<?php 
$wn_storex_table = array(
	'storex_hotel',
	'storex_brand',
	'storex_business',
	'storex_comment',
	'storex_comment_clerk',
	'storex_member',
	'storex_order',
	'storex_reply',
	'storex_room',
	'storex_room_price',
	'storex_set',
	'storex_code',
	'storex_bases',
	'storex_categorys',
	'storex_goods',
);
foreach ($wn_storex_table as $table){
	if(pdo_tableexists($table)){
		pdo_query("DROP TABLE " .tablename($table));
	}
}
?>