<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
require DT_ROOT.'/include/content.class.php';
$could_comment = in_array($moduleid, explode(',', $EXT['comment_module'])) ? 1 : 0;
if($DT_PC) {
    //如果获取不到$itemid 就到本模型页面。
	$itemid or dheader($MOD['linkurl']);
    //检车groupid是否符合 就到403。
	if(!check_group($_groupid, $MOD['group_show'])) include load('403.inc');
//    顺利就获取本条item。
	$item = $db->get_one("SELECT * FROM {$table} WHERE itemid=$itemid");
	if($item && $item['status'] == 3) {
//        如果item是外部链接，就跳转
		if($item['islink']) dheader($item['linkurl']);
//        内容页是否生成html，如果是并且存在就301静态页面。
		if($MOD['show_html'] && is_file(DT_ROOT.'/'.$MOD['moduledir'].'/'.$item['linkurl'])) d301($MOD['linkurl'].$item['linkurl']);
//        此时没有content
		extract($item);
	} else {
		include load('404.inc');
	}
//    获取整个分类数据
	$CAT = get_cat($catid);
//    检查分租权限
	if(!check_group($_groupid, $CAT['group_show'])) include load('403.inc');
//    获取content所在的表名
	$content_table = content_table($moduleid, $itemid, $MOD['split'], $table_data);
//    获取content
	$t = $db->get_one("SELECT content FROM {$content_table} WHERE itemid=$itemid");
	$content = $t['content'];

    //图片查看
//    $content=img_fancybox($content,$title);

//    后面是什么？
	$CP = $MOD['cat_property'] && $CAT['property'];
	if($CP) {
		require DT_ROOT.'/include/property.func.php';
		$options = property_option($catid);
		$values = property_value($moduleid, $itemid);
	}
	$adddate = timetodate($addtime, 3);
	$editdate = timetodate($edittime, 3);
	if($voteid) $voteid = explode(' ', $voteid);
	if($fromurl) $fromurl = fix_link($fromurl);
	$linkurl = $MOD['linkurl'].$linkurl;
	$titles = array();
	if($subtitle) {
		$titles = explode("\n", $subtitle);
		$titles = array_map('trim', $titles);
	}
	$subtitle = isset($titles[$page-1]) ? $titles[$page-1] : '';
	$keytags = $tag ? explode(' ', trim($tag)) : array();
	$update = '';
	$fee = DC::fee($item['fee'], $MOD['fee_view']);
	if($fee) {
		$user_status = 4;
		$destoon_task = "moduleid=$moduleid&html=show&itemid=$itemid&page=$page";
		$description = DC::description($content, $MOD['pre_view']);
	} else {
		$user_status = 3;
	}
	if($user_status != 3 && $_username && $item['username'] == $_username) {
		$user_status = 3;
		$destoon_task = '';
	}
	$pages = '';
	$subtitles = count($titles);
	$total = 1;
	if(strpos($content, 'pagebreak') !== false) {		
		$contents = DC::pagebreak($content);
		if($contents) {
			$total = count($contents);
			$pages = pages($total, $page, 1, $MOD['linkurl'].itemurl($item, '{destoon_page}'));
			if($pages) $pages = substr($pages, 0, strpos($pages, '<cite>'));
			$content = isset($contents[$page-1]) ? $contents[$page-1] : '';
			if($total < $subtitles) $subtitles = $total;
		}
	}
	if($page > $total) include load('404.inc');
	if($content) {
		$content = DC::format($content, $DT_PC);
		if($MOD['keylink']) $content = DC::keylink($content, $moduleid, $DT_PC);
		if($lazy) $content = DC::lazy($content);
	}
	if($EXT['mobile_enable']) $head_mobile = $MOD['mobile'].($page > 1 ? itemurl($item, $page) : $item['linkurl']);
} else {
	$itemid or dheader($MOD['mobile']);
	$item = $db->get_one("SELECT * FROM {$table} WHERE itemid=$itemid");
	($item && $item['status'] == 3) or message($L['msg_not_exist']);
	extract($item);
	$CAT = get_cat($catid);
	if(!check_group($_groupid, $MOD['group_show']) || !check_group($_groupid, $CAT['group_show'])) message($L['msg_no_right']);
	$description = '';
	$user_status = 3;
	$fee = DC::fee($item['fee'], $MOD['fee_view']);
	include DT_ROOT.'/mobile/api/content.inc.php';
	$content_table = content_table($moduleid, $itemid, $MOD['split'], $table_data);
	$t = $db->get_one("SELECT content FROM {$content_table} WHERE itemid=$itemid");
	$content = $t['content'];
	$titles = array();
	if($subtitle) {
		$titles = explode("\n", $subtitle);
		$titles = array_map('trim', $titles);
	}
	$subtitle = isset($titles[$page-1]) ? $titles[$page-1] : '';
	$keytags = $tag ? explode(' ', trim($tag)) : array();
	$pages = '';
	$subtitles = count($titles);
	$total = 1;
	if(strpos($content, 'pagebreak') !== false) {
		$contents = DC::pagebreak($content);
		if($contents) {
			$total = count($contents);
			$pages = mobile_pages($total, $page, 1, $MOD['mobile'].itemurl($item, '{destoon_page}'));
			$content = isset($contents[$page-1]) ? $contents[$page-1] : '';
			if($total < $subtitles) $subtitles = $total;
		}
	}
	if($content) {
		if($MOD['keylink']) $content = DC::keylink($content, $moduleid, $DT_PC);
		if($share_icon) $share_icon = DC::icon($thumb, $content);
		if($user_status == 2) $description = DC::description($content, $MOD['pre_view']);
		$content = DC::format($content, $DT_PC);
	}
	$CP = $MOD['cat_property'] && $CAT['property'];
	if($CP) {
		require DT_ROOT.'/include/property.func.php';
		$options = property_option($catid);
		$values = property_value($moduleid, $itemid);
	}
	$editdate = timetodate($addtime, 5);
	if($voteid) $voteid = explode(' ', $voteid);
	if($fromurl) $fromurl = fix_link($fromurl);
	$update = '';
	$head_title = $head_name = $CAT['catname'];
	$js_item = 1;
	$foot = '';
}
if(!$DT_BOT) include DT_ROOT.'/include/update.inc.php';
$seo_file = 'show';
include DT_ROOT.'/include/seo.inc.php';
if($subtitle) $seo_title = $subtitle.$seo_delimiter.$seo_title;
$template = $item['template'] ? $item['template'] : ($CAT['show_template'] ? $CAT['show_template'] : ($MOD['template_show'] ? $MOD['template_show'] : 'show'));
include template($template, $module);
?>