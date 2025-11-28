<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
if($DT['max_list'] > 0 && $page > $DT['max_list']) $page = 1;
if($DT_PC) {
	if(!$CAT || $CAT['moduleid'] != $moduleid) include load('404.inc');
	if($MOD['list_html']) {
		$html_file = listurl($CAT, $page);
		if(is_file(DT_ROOT.'/'.$MOD['moduledir'].'/'.$html_file)) d301($MOD['linkurl'].$html_file);
	}
	if(!check_group($_groupid, $MOD['group_list']) || !check_group($_groupid, $CAT['group_list'])) include load('403.inc');

	$FD = cache_read('fields-'.substr($table, strlen($DT_PRE)).'.php');
	if($FD) require DT_ROOT.'/include/fields.func.php';
	$CP = $MOD['cat_property'] && $catid && $CAT['property'];
	if($CP) require DT_ROOT.'/include/property.func.php';
	$PP = $CP ? property_search_arr($catid) : array();

	unset($CAT['moduleid']);
	extract($CAT);

	$DT['pages_mode'] = 1;
	$comment_url = $EXT['comment_url'];

	$maincat = get_maincat($child ? $catid : $parentid, $moduleid);
	$condition = 'status=3';
	$condition .= ($CAT['child']) ? " AND catid IN (".$CAT['arrchildid'].")" : " AND catid=$catid";
	if($cityid) {
		$areaid = $cityid;
		$ARE = get_area($areaid);
		$condition .= $ARE['child'] ? " AND areaid IN (".$ARE['arrchildid'].")" : " AND areaid=$areaid";
		$items = $db->count($table, $condition, $CFG['db_expires']*split_id($CAT['item']));
	} else {
		if($page == 1) {
			$items = $db->count($table, $condition, $CFG['db_expires']*split_id($CAT['item']));
			if($items != $CAT['item']) {
				$CAT['item'] = $items;
				$db->query("UPDATE {$DT_PRE}category SET item=$items WHERE catid=$catid");
			}
		} else {
			$items = $CAT['item'];
		}
	}
	$pagesize = $MOD['pagesize'];
	$offset = ($page-1)*$pagesize;
	$pages = listpages($CAT, $items, $page, $pagesize);
	$tags = array();
	if($items) {
		$result = $db->query("SELECT ".$MOD['fields']." FROM {$table} WHERE {$condition} ORDER BY ".$MOD['order']." LIMIT {$offset},{$pagesize}", ($CFG['db_expires'] && $page <= $DT['cache_page']) ? 'CACHE' : '', $CFG['db_expires']);
		while($r = $db->fetch_array($result)) {
			$r['linkurl'] = $MOD['linkurl'].$r['linkurl'];
			$tags[] = $r;
		}
		$db->free_result($result);
	}
	$showpage = 1;
	$datetype = 5;
	$introduce = 150;
	if($EXT['mobile_enable']) $head_mobile = $MOD['mobile'].listurl($CAT, $page > 1 ? $page : 0);
} else {
	if(!$CAT || $CAT['moduleid'] != $moduleid) message($L['msg_not_cate']);
	if(!check_group($_groupid, $MOD['group_list']) || !check_group($_groupid, $CAT['group_list'])) message($L['msg_no_right']);
	$comment_url = $EXT['comment_mobile'];
	$condition = "status=3";
	$condition .= $CAT['child'] ? " AND catid IN (".$CAT['arrchildid'].")" : " AND catid=$catid";
	if($cityid) {
		$areaid = $cityid;
		$ARE = get_area($areaid);
		$condition .= $ARE['child'] ? " AND areaid IN (".$ARE['arrchildid'].")" : " AND areaid=$areaid";
		$items = $db->count($table, $condition, $CFG['db_expires']*split_id($CAT['item']));
	} else {
		$items = $CAT['item'];
	}
	$pages = mobile_pages($items, $page, $pagesize, $MOD['mobile'].listurl($CAT, '{destoon_page}'));
	$tags = array();
	if($items) {
		$result = $db->query("SELECT ".$MOD['fields']." FROM {$table} WHERE {$condition} ORDER BY ".$MOD['order']." LIMIT {$offset},{$pagesize}", ($CFG['db_expires'] && $page <= $DT['cache_page']) ? 'CACHE' : '', $CFG['db_expires']);
		while($r = $db->fetch_array($result)) {
			$r['introduce'] = parse_mob($r['introduce']);
			$r['linkurl'] = $MOD['mobile'].$r['linkurl'];
			$tags[] = $r;
		}
		$db->free_result($result);
		$js_load = $MOD['mobile'].'search'.DT_EXT.'?job=ajax&catid='.$catid;
	}
	if($CAT['parentid']) $PCAT = get_cat($CAT['parentid']);
	$head_title = $head_name = $CAT['catname'];
}
$seo_file = 'list';
include DT_ROOT.'/include/seo.inc.php';
$template = $CAT['template'] ? $CAT['template'] : ($MOD['template_list'] ? $MOD['template_list'] : 'list');
include template($template, $module);
?>