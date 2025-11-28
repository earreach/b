<?php 
defined('IN_DESTOON') or exit('Access Denied');
$filename =  DT_ROOT.'/'.$MOD['moduledir'].'/'.$DT['index'].'.'.$DT['file_ext'];
if(!$MOD['index_html']) return html_del($filename);
$maincat = $childcat = get_maincat(0, $moduleid, 1);
$seo_file = 'index';
include DT_ROOT.'/include/seo.inc.php';
$destoon_task = "moduleid=$moduleid&html=index";
$template = $MOD['template_index'] ? $MOD['template_index'] : 'index';
if($EXT['mobile_enable']) $head_mobile = $MOD['mobile'];
$DT_PC = $GLOBALS['DT_PC'] = 1;
ob_start();
include template($template, $module);




$data = ob_get_contents();
ob_clean();
file_put($filename, $data);
if($EXT['mobile_enable']) {
	include DT_ROOT.'/include/mobile.htm.php';
	$head_pc = $MOD['linkurl'];
	$condition = "status=3";
	$r = $db->get_one("SELECT COUNT(*) AS num FROM {$table} WHERE {$condition}", 'CACHE');
	$items = $r['num'];
	$pages = mobile_pages($items, $page, $pagesize, $MOD['mobile'].'index'.DT_EXT.'?page={destoon_page}');
	$tags = array();
	if($items) {
		$result = $db->query("SELECT ".$MOD['fields']." FROM {$table} WHERE {$condition} ORDER BY ".$MOD['order']." LIMIT {$offset},{$pagesize}");
		while($r = $db->fetch_array($result)) {
			$r['title'] = set_style($r['title'], $r['style']);
			if(!$r['islink']) $r['linkurl'] = $MOD['mobile'].$r['linkurl'];
			$tags[] = $r;
		}
		$db->free_result($result);
		$js_load = $MOD['mobile'].'search'.DT_EXT.'?job=ajax';
	}
	$head_title = $head_name = $MOD['name'];
	ob_start();
	include template($template, $module);
	$data = ob_get_contents();
	ob_clean();
	file_put(str_replace(DT_ROOT, DT_ROOT.'/mobile', $filename), $data);
}
return true;
?>