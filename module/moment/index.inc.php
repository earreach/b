<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
$typeid = isset($typeid) ? intval($typeid) : 0;
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
	$tag_condition = 'status=3 AND open=1 AND addtime>'.($DT_TIME - 86400*30);
	$tag_order = 'likes DESC';
} else {
	$typeid = 0;
	$tag_condition = 'status=3 AND open=1';
	$tag_order = 'addtime DESC';
}
if($catid) $tag_condition .= ($CAT['child']) ? " AND catid IN (".$CAT['arrchildid'].")" : " AND catid=$catid";
if($areaid) $tag_condition .= ($ARE['child']) ? " AND areaid IN (".$ARE['arrchildid'].")" : " AND areaid=$areaid";

if($DT_PC) {
	$showpage = 1;
	$pagesize = $MOD['pagesize'];
	$DT['pages_mode'] = 1;
	$comment_url = $EXT['comment_url'];
	if(!check_group($_groupid, $MOD['group_index'])) include load('403.inc');
	$maincat = get_maincat(0, $moduleid, 1);
	$destoon_task = "moduleid=$moduleid&html=index";
	if($EXT['mobile_enable']) $head_mobile = $MOD['mobile'].($page > 1 ? 'index'.DT_EXT.'?page='.$page : '');
} else {
	$comment_url = $EXT['comment_mob'];
	$condition = "status=3";
	if($cityid) {
		$areaid = $cityid;
		$ARE = get_area($areaid);
		$condition .= $ARE['child'] ? " AND areaid IN (".$ARE['arrchildid'].")" : " AND areaid=$areaid";
	}
	$r = $db->get_one("SELECT COUNT(*) AS num FROM {$table} WHERE {$condition}", 'CACHE');
	$items = $r['num'];
	$pages = mobile_pages($items, $page, $pagesize);
	$tags = array();
	if($items) {
		$result = $db->query("SELECT ".$MOD['fields']." FROM {$table} WHERE {$condition} ORDER BY ".$MOD['order']." LIMIT {$offset},{$pagesize}", ($CFG['db_expires'] && $page <= $DT['cache_page']) ? 'CACHE' : '', $CFG['db_expires']);
		while($r = $db->fetch_array($result)) {
			$r['introduce'] = parse_mob($r['introduce']);
			$r['linkurl'] = $MOD['mobile'].$r['linkurl'];
			$tags[] = $r;
		}
		$db->free_result($result);
		$js_load = $MOD['mobile'].'search'.DT_EXT.'?job=ajax';
	}
	$head_title = $head_name = $MOD['name'];
}
$seo_file = 'index';
include DT_ROOT.'/include/seo.inc.php';
include template($MOD['template_index'] ? $MOD['template_index'] : 'index', $module);
?>