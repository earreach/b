<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
if($itemid) {	
	$item = $db->get_one("SELECT * FROM {$table_topic} WHERE itemid=$itemid");
	($item && $item['status'] == 3) or dheader('topic'.DT_EXT);
	$typeid = isset($typeid) ? intval($typeid) : 0;
	if($typeid == 1) {
		$tag_condition = 'status=3 AND topicid='.$itemid.' AND level>0 AND open=1';
		$tag_order = $MOD['order'];
	} else if($typeid == 2) {
		#login();
		$tag_condition = 'status=3 AND topicid='.$itemid.' AND open IN(1,2) AND userid IN('.my_fuid(2).')';
		$tag_order = 'addtime DESC';
	} else if($typeid == 3) {
		#login();
		$tag_condition = 'status=3 AND topicid='.$itemid.' AND open IN(1,3) AND userid IN('.my_fuid(3).')';
		$tag_order = 'addtime DESC';
	} else if($typeid == 4) {
		$tag_condition = 'status=3 AND topicid='.$itemid.' AND open=1';
		$tag_order = 'likes DESC';
	} else {
		$typeid = 0;
		$tag_condition = 'status=3 AND topicid='.$itemid.' AND open=1';
		$tag_order = 'addtime DESC';
	}
	$items = $db->count($table, $tag_condition, $DT['cache_search']);
	$pages = $DT_PC ? pages($items, $page, $pagesize) : mobile_pages($items, $page, $pagesize);
	$tags = array();
	$result = $db->query("SELECT * FROM {$table} WHERE {$tag_condition} ORDER BY {$tag_order} LIMIT {$offset},{$pagesize}", $DT['cache_search'] && $page <= $DT['cache_page'] ? 'CACHE' : '', $DT['cache_search']);
	while($r = $db->fetch_array($result)) {
		$tags[] = $r;
	}	
	$update = 'hits=hits+1';
	if($typeid == 0 && $items != $item['item'] && substr_count($tag_condition, 'AND') == 2) $update .= ",item=$items";
	$db->query("UPDATE LOW_PRIORITY {$table_topic} SET $update WHERE itemid=$itemid", 'UNBUFFERED');
	$username = $item['username'];
	$comment_url = $EXT['comment_url'];
	$showpage = 1;
	$JS[] = 'moment';
	$JS[] = 'player';
	if($item['seo_title']) {
		$head_title = $item['seo_title'];
	} else {
		$head_title = $item['title'].$DT['seo_delimiter'].$L['topic_title'];
	}
	$head_title = $head_title.$DT['seo_delimiter'].$MOD['name'];
	$head_keywords = $item['seo_keywords'];
	$head_description = $item['seo_description'];
} else {
	$condition = "1";
	if($keyword) $condition .= match_kw('title', $keyword);
	if($catid) $condition .= $CAT['child'] ? " AND catid IN (".$CAT['arrchildid'].")" : " AND catid=$catid";
	$items = $db->count($table_topic, $condition, $DT['cache_search']);
	$pages = $DT_PC ? pages($items, $page, $pagesize) : mobile_pages($items, $page, $pagesize);
	$tags = array();
	$result = $db->query("SELECT * FROM {$table_topic} WHERE {$condition} ORDER BY addtime DESC LIMIT {$offset},{$pagesize}", $DT['cache_search'] && $page <= $DT['cache_page'] ? 'CACHE' : '', $DT['cache_search']);
	while($r = $db->fetch_array($result)) {
		$r['alt'] = $r['title'];
		$r['moburl'] = $MOD['mobile'].rewrite('topic'.DT_EXT.'?itemid='.$r['itemid']);
		$r['linkurl'] = $MOD['linkurl'].rewrite('topic'.DT_EXT.'?itemid='.$r['itemid']);
		$tags[] = $r;
	}
	$showpage = 1;
	$target = '';
	$head_title = $L['topic_title'].$DT['seo_delimiter'].$MOD['name'];
	if($catid) $head_title = $CAT['catname'].$DT['seo_delimiter'].$head_title;
	if($kw) $head_title = $kw.$DT['seo_delimiter'].$head_title;
	if($DT_PC) {
		if($EXT['mobile_enable']) $head_mobile = str_replace($MOD['linkurl'], $MOD['mobile'], $DT_URL);
	} else {
		if($job == 'ajax') {
			if($tags) include template('list-moment-topic', 'tag');
			exit;
		}
		$js_load = $MOD['mobile'].'topic'.DT_EXT.'?job=ajax';
		$head_name = $L['topic_title'];
		if($sns_app) $seo_title = $MOD['name'];
	}
}
include template($MOD['template_topic'] ? $MOD['template_topic'] : 'topic', $module);
?>