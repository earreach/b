<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
require DT_ROOT.'/include/post.func.php';
if($DT['rewrite'] && $DT['search_rewrite'] && $_SERVER["REQUEST_URI"] && $_SERVER['QUERY_STRING'] && $job != 'ajax') {
	$_URL = rewrite($_SERVER["REQUEST_URI"]);
	if($_URL != $_SERVER["REQUEST_URI"]) dheader($_URL);
}
if(bansearch()) exit(include template('search', 'message'));
if($DT['max_search'] > 0 && $page > $DT['max_search']) $page = 1;
if($DT_PC) {
	if(!check_group($_groupid, $MOD['group_search'])) include load('403.inc');
	include load('search.lang');
	if(!$areaid && $cityid && strpos($DT_URL, 'areaid') === false) {
		$areaid = $cityid;
		$ARE = get_area($areaid);
	}
	$FD = cache_read('fields-'.substr($table, strlen($DT_PRE)).'.php');
	if($FD) require DT_ROOT.'/include/fields.func.php';
	$CP = $MOD['cat_property'] && $catid && $CAT['property'];
	if($CP) require DT_ROOT.'/include/property.func.php';
	$PP = $CP ? property_search_arr($catid) : array();
	$maincat = get_maincat(($CAT ? ($CAT['child'] ? $catid : $CAT['parentid']) : 0), $moduleid);

	$sfields = array($L['by_auto'], $L['by_title'], $L['by_content'], $L['by_introduce'], $L['by_author']);
	$dfields = array('keyword', 'title', 'content', 'introduce', 'author');
	$sorder  = array($L['order'], $L['order_auto'], $L['order_addtime'], $L['order_hits']);
	$dorder  = array($MOD['order'], '', 'addtime DESC', 'hits DESC');
	if(!$MOD['fulltext']) unset($sfields[2], $dfields[2]);
	isset($fields) && isset($dfields[$fields]) or $fields = 0;
	isset($order) && isset($dorder[$order]) or $order = 0;
	$category_select = category_select('catid', $L['all_category'], $catid, $moduleid);
	$area_select = $DT['city'] ? ajax_area_select('areaid', $L['all_area'], $areaid) : '';
	$order_select  = dselect($sorder, 'order', '', $order);
	$tags = array();
	if($DT_QST) {
		if($kw) {
			if(strlen($kw) < $DT['min_kw'] || strlen($kw) > $DT['max_kw']) message(lang($L['word_limit'], array($DT['min_kw'], $DT['max_kw'])), $MOD['linkurl'].'search.php');
			if($DT['search_limit'] && $page == 1) {
				if(($DT_TIME - $DT['search_limit']) < get_cookie('last_search')) message(lang($L['time_limit'], array($DT['search_limit'])), $MOD['linkurl'].'search.php');
				set_cookie('last_search', $DT_TIME);
			}
		}

		$fds = $MOD['fields'];
		$condition = '';
		if($catid) $condition .= ($CAT['child']) ? " AND catid IN (".$CAT['arrchildid'].")" : " AND catid=$catid";
		if($areaid) $condition .= ($ARE['child']) ? " AND areaid IN (".$ARE['arrchildid'].")" : " AND areaid=$areaid";
		if($FD) $condition .= fields_search_sql();
		if($CP) $condition .= property_search_sql();

		if($dfields[$fields] == 'content') {
			if($keyword && $MOD['fulltext'] == 1) $condition .= match_kw($dfields[$fields], $keyword);
			$condition = str_replace('AND ', 'AND i.', $condition);
			$condition = str_replace('i.content', 'd.content', $condition);
			$condition = "i.status=3 AND i.itemid=d.itemid".$condition;
			if($keyword && $MOD['fulltext'] == 2) $condition .= " AND MATCH(`content`) AGAINST('$kw'".(preg_match("/[+-<>()~*]/", $kw) ? ' IN BOOLEAN MODE' : '').")";
			$table = $table.' i,'.$table_data.' d';
			$fds = 'i.'.str_replace(',', ',i.', $fds);
		} else {
			if($keyword) $condition .= match_kw($dfields[$fields], $keyword);
			$condition = "status=3".$condition;
		}
		$pagesize = $MOD['pagesize'];
		$offset = ($page-1)*$pagesize;
		$items = $db->count($table, $condition, $DT['cache_search']);
		if($DT['max_search']) {
			//
		}
		$pages = pages($items, $page, $pagesize);
		if($items) {
			$order = $dorder[$order] ? " ORDER BY {$dorder[$order]}" : '';
			$result = $db->query("SELECT {$fds} FROM {$table} WHERE {$condition}{$order} LIMIT {$offset},{$pagesize}", $DT['cache_search'] && $page <= $DT['cache_page'] ? 'CACHE' : '', $DT['cache_search']);
			if($kw) {
				$replacef = explode(' ', $kw);
				$replacet = array_map('highlight', $replacef);
			}
			while($r = $db->fetch_array($result)) {
				$r['adddate'] = timetodate($r['addtime'], 5);
				$r['editdate'] = timetodate($r['edittime'], 5);
				if($lazy && isset($r['thumb']) && $r['thumb']) $r['thumb'] = DT_STATIC.'image/lazy.gif" original="'.$r['thumb'];
				$r['alt'] = $r['title'];
				$r['title'] = set_style($r['title'], $r['style']);
				if($kw) $r['title'] = str_replace($replacef, $replacet, $r['title']);
				if(!$r['islink']) $r['linkurl'] = $MOD['linkurl'].$r['linkurl'];
				$tags[] = $r;
			}
			$db->free_result($result);
		}
	}
	if($page == 1 && $kw && $DT['search_kw']) keyword($DT['search_kw'], $_username, $kw, $items, $moduleid);
	$showpage = 1;
	$datetype = 5;
	$target = '_blank';
	$cols = 3;
	$class = '';
	if($EXT['mobile_enable']) $head_mobile = str_replace($MOD['linkurl'], $MOD['mobile'], $DT_URL);
} else {
	if($kw) {
		check_group($_groupid, $MOD['group_search']) or message($L['msg_no_search']);
	} else if($catid) {
		$CAT or message($L['msg_not_cate']);
		if(!check_group($_groupid, $MOD['group_list']) || !check_group($_groupid, $CAT['group_list'])) message($L['msg_no_right']);
	} else {
		check_group($_groupid, $MOD['group_index']) or message($L['msg_no_right']);
	}
	$head_title = $MOD['name'].$DT['seo_delimiter'].$head_title;
	if($kw) $head_title = $kw.$DT['seo_delimiter'].$head_title;
	if(!$areaid && $cityid && strpos($DT_URL, 'areaid') === false) {
		$areaid = $cityid;
		$ARE = get_area($areaid);
	}
	$elite = isset($elite) ? intval($elite) : 0;
	(isset($orderby) && in_array($orderby, array('dlikes', 'dhits', 'dcomments'))) or $orderby = '';
	$tags = array();
	if($DT_QST) {
		$condition = "status=3";
		if($keyword) $condition .= match_kw('keyword', $keyword);
		if($catid) $condition .= $CAT['child'] ? " AND catid IN (".$CAT['arrchildid'].")" : " AND catid=$catid";
		if($areaid) $condition .= $ARE['child'] ? " AND areaid IN (".$ARE['arrchildid'].")" : " AND areaid=$areaid";
		if($elite) $condition .= " AND level>0";
		$items = $db->count($table, $condition, $DT['cache_search']);
		$pages = mobile_pages($items, $page, $pagesize);
		if($items) {
			$order = $MOD['order'];
			if($orderby) $order = substr($orderby, 0, 1) == 'd' ? substr($orderby, 1).' DESC' : substr($orderby, 1).' ASC';
			$time = strpos($MOD['order'], 'add') !== false ? 'addtime' : 'edittime';
			$result = $db->query("SELECT ".$MOD['fields']." FROM {$table} WHERE {$condition} ORDER BY {$order} LIMIT {$offset},{$pagesize}", $DT['cache_search'] && $page <= $DT['cache_page'] ? 'CACHE' : '', $DT['cache_search']);
			while($r = $db->fetch_array($result)) {
				if($kw) $r['title'] = str_replace($kw, '<span class="f_red">'.$kw.'</span>', $r['title']);
				if(!$r['islink']) $r['linkurl'] = $MOD['mobile'].$r['linkurl'];
				$tags[] = $r;
			}
			$db->free_result($result);
			$js_load = preg_replace("/(.*)([&?]page=[0-9]*)(.*)/i", "\\1\\3", rewrite($DT_URL, 1)).'&job=ajax';
		}
		if($page == 1 && $kw && $DT['search_kw']) keyword($DT['search_kw'], $_username, $kw, $items, $moduleid);
	}
	if($job == 'ajax') {
		if($tags) include template('list-'.$module, 'tag');
		exit;
	}
	$head_title = $MOD['name'].$L['search'];
}
$seo_file = 'search';
include DT_ROOT.'/include/seo.inc.php';
include template($MOD['template_search'] ? $MOD['template_search'] : 'search', $module);
?>