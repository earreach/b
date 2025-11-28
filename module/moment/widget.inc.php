<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
$typeid = isset($typeid) ? intval($typeid) : 0;
(isset($username) && check_name($username)) or $username = '';
$userid = 0;
if($username) {
	$member = userinfo($username);
	if($member) {
		$typeid = 0;
		$userid = $member['userid'];
	} else {
		$username = '';
	}
}
if($typeid == 1) {
	$tag_condition = 'status=3 AND level>0 AND open=1';
	$tag_order = $MOD['order'];
} else if($typeid == 2) {
	#login();
	$tag_condition = 'status=3 AND open IN(1,2) AND userid IN('.my_fuid(2).')';
	$tag_order = 'addtime DESC';
} else if($typeid == 3) {
	#login();
	$tag_condition = 'status=3 AND open IN(1,3) AND userid IN('.my_fuid(3).')';
	$tag_order = 'addtime DESC';
} else if($typeid == 4) {
	$tag_condition = 'status=3 AND open=1';
	$tag_order = 'likes DESC';
} else {
	$typeid = 0;
	$tag_condition = 'status=3 AND open=1';
	$tag_order = 'addtime DESC';
}
if($userid) $tag_condition .= ' AND userid='.$userid;
$DT['pages_mode'] = 1;
$items = $db->count($table, $tag_condition, $DT['cache_search']);
$pages = $DT_PC ? pages($items, $page, $pagesize) : mobile_pages($items, $page, $pagesize);
$tags = array();
$result = $db->query("SELECT * FROM {$table} WHERE {$tag_condition} ORDER BY {$tag_order} LIMIT {$offset},{$pagesize}", $DT['cache_search'] && $page <= $DT['cache_page'] ? 'CACHE' : '', $DT['cache_search']);
while($r = $db->fetch_array($result)) {
	$tags[] = $r;
}
$comment_url = $EXT['comment_url'];
$showpage = 1;
$JS[] = 'moment';
$JS[] = 'player';

include template('widget', $module);
?>