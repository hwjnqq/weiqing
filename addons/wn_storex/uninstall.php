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
	'storex_clerk',
	'storex_notices',
	'storex_notices_unread',
	'storex_sign_record',
	'storex_sign_set',
	'storex_mc_card',
	'storex_mc_card_members',
	'storex_mc_card_record',
	'storex_mc_member_property',
	'storex_activity_exchange',
	'storex_coupon',
	'storex_coupon_activity',
	'storex_coupon_record',
	'storex_coupon_store',
	'storex_activity_stores',
	'storex_activity_clerks',
	'storex_paycenter_order',
);
foreach ($wn_storex_table as $table){
	if(pdo_tableexists($table)){
		pdo_query("DROP TABLE " .tablename($table));
	}
}
?>