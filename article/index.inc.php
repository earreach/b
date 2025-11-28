<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
if($DT_PC) {
	if($MOD['index_html']) {	
		$html_file = DT_ROOT.'/'.$MOD['moduledir'].'/'.$DT['index'].'.'.$DT['file_ext'];
		if(!is_file($html_file)) tohtml('index', $module);
		if(is_file($html_file)) exit(include($html_file));
	}
	if(!check_group($_groupid, $MOD['group_index'])) include load('403.inc');
	$maincat = $childcat = get_maincat(0, $moduleid, 1);
    $maincat1=get_maincat_1(0, $moduleid, 1);
//    var_dump($maincat1);die();
	$destoon_task = "moduleid=$moduleid&html=index";
	if($EXT['mobile_enable']) $head_mobile = $MOD['mobile'].($page > 1 ? 'index'.DT_EXT.'?page='.$page : '');
} else {
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
			$r['title'] = set_style($r['title'], $r['style']);
			if(!$r['islink']) $r['linkurl'] = $MOD['mobile'].$r['linkurl'];
			$tags[] = $r;
		}
		$db->free_result($result);
		$js_load = $MOD['mobile'].'search'.DT_EXT.'?job=ajax';
	}
	$head_title = $head_name = $MOD['name'];
    $mid or $mid = 4;
    $CATEGORY = cache_read('category-'.$mid.'.php');

    $MOD = cache_read('module-'.$mid.'.php');
//    var_dump($MOD);
//    die();
}
$seo_file = 'index';
include DT_ROOT.'/include/seo.inc.php';
include template($MOD['template_index'] ? $MOD['template_index'] : 'index', $module);
?>