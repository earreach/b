<?php
defined('IN_DESTOON') or exit('Access Denied');
if(!$MOD['list_html'] || !$catid) return false;
$CAT or $CAT = get_cat($catid);
if(!$CAT) return false;
unset($CAT['moduleid']);
extract($CAT);
$FD = cache_read('fields-'.substr($table, strlen($DT_PRE)).'.php');
if($FD) require DT_ROOT.'/include/fields.func.php';
$CP = $MOD['cat_property'] && $catid && $CAT['property'];
if($CP) require DT_ROOT.'/include/property.func.php';
$PP = $CP ? property_search_arr($catid) : array();
$maincat = get_maincat($child ? $catid : $parentid, $moduleid);
$DT['pages_mode'] = 1;
$comment_url = $EXT['comment_url'];
$condition = 'status=3';
$condition .= ($CAT['child']) ? " AND catid IN (".$CAT['arrchildid'].")" : " AND catid=$catid";
if($page == 1) {
	$items = $db->count($table, $condition);
	if($items != $CAT['item']) {
		$CAT['item'] = $items;
		$db->query("UPDATE {$DT_PRE}category SET item=$items WHERE catid=$catid");
	}
} else {
	$items = $CAT['item'];
}
$pagesize = $MOD['pagesize'];
$showpage = 1;
$datetype = 5;
$introduce = 150;
$template = $CAT['template'] ? $CAT['template'] : ($MOD['template_list'] ? $MOD['template_list'] : 'list');
$total = max(ceil($items/$MOD['pagesize']), 1);
if(isset($fid) && isset($num)) {
	$page = $fid;
	$topage = $fid + $num - 1;
	$total = $topage < $total ? $topage : $total;
}
for(; $page <= $total; $page++) {
	$offset = ($page-1)*$pagesize;
	$pages = listpages($CAT, $items, $page, $pagesize);
	$tags = array();
	$result = $db->query("SELECT ".$MOD['fields']." FROM {$table} WHERE {$condition} ORDER BY ".$MOD['order']." LIMIT {$offset},{$pagesize}");
	while($r = $db->fetch_array($result)) {
		$r['linkurl'] = $MOD['linkurl'].$r['linkurl'];
		$tags[] = $r;
	}
	$seo_file = 'list';
	include DT_ROOT.'/include/seo.inc.php';
	$destoon_task = "moduleid=$moduleid&html=list&catid=$catid&page=$page";
	if($EXT['mobile_enable']) $head_mobile = $MOD['mobile'].($page > 1 ?listurl($CAT, $page) : $CAT['linkurl']);
	$filename = DT_ROOT.'/'.$MOD['moduledir'].'/'.listurl($CAT, $page);
	$_tags = $tags;
	$DT_PC = $GLOBALS['DT_PC'] = 1;
	ob_start();
	include template($template, $module);
	$data = ob_get_contents();
	ob_clean();
	if($DT['pcharset']) $filename = convert($filename, DT_CHARSET, $DT['pcharset']);
	file_put($filename, $data);
	if($page == 1) {
		$indexname = DT_ROOT.'/'.$MOD['moduledir'].'/'.listurl($CAT, 0);
		if($DT['pcharset']) $indexname = convert($indexname, DT_CHARSET, $DT['pcharset']);
		file_copy($filename, $indexname);
	}
	if($EXT['mobile_enable']) {
		include DT_ROOT.'/include/mobile.htm.php';
		$comment_url = $EXT['comment_mob'];	
		$head_pc = str_replace($MOD['mobile'], $MOD['linkurl'], $head_mobile);
		if($CAT['parentid']) $PCAT = get_cat($CAT['parentid']);
		$pages = mobile_pages($items, $page, $pagesize, $MOD['mobile'].listurl($CAT, '{destoon_page}'));
		$time = strpos($MOD['order'], 'add') !== false ? 'addtime' : 'edittime';
		$tags = array();
		foreach($_tags as $r) {
			$r['introduce'] = parse_mob($r['introduce']);
			$r['linkurl'] = str_replace($MOD['linkurl'], $MOD['mobile'], $r['linkurl']);
			$tags[] = $r;
		}
		if($items) $js_load = $MOD['mobile'].'search'.DT_EXT.'?job=ajax&catid='.$catid;
		$head_title = $head_name = $CAT['catname'];
		$filename = str_replace(DT_ROOT, DT_ROOT.'/mobile', $filename);
		ob_start();
		include template($template, $module);
		$data = ob_get_contents();
		ob_clean();
		file_put($filename, $data);
		if($page == 1) file_copy($filename, str_replace(DT_ROOT, DT_ROOT.'/mobile', $indexname));
	}
}
return true;
?>