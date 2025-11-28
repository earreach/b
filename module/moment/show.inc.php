<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
$could_comment = $MOD['show_like'] = 0;
if($DT_PC) {
	$itemid or dheader($MOD['linkurl']);
	if(!check_group($_groupid, $MOD['group_show'])) include load('403.inc');
	$item = $db->get_one("SELECT * FROM {$table} WHERE itemid=$itemid");
	if($item && $item['status'] > 2) {
		if($MOD['show_html'] && is_file(DT_ROOT.'/'.$MOD['moduledir'].'/'.$item['linkurl'])) d301($MOD['linkurl'].$item['linkurl']);
		require DT_ROOT.'/include/content.class.php';
		extract($item);
	} else {
		include load('404.inc');
	}
	if($item['open'] < 1) include load('404.inc');
	$CAT = get_cat($catid);
	if(!check_group($_groupid, $CAT['group_show'])) include load('403.inc');
	$content_table = content_table($moduleid, $itemid, $MOD['split'], $table_data);
	$t = $db->get_one("SELECT content FROM {$content_table} WHERE itemid=$itemid");
	$content = $t['content'];
	if($content) {
		if($MOD['keylink']) $content = DC::keylink($content, $moduleid, $DT_PC);
		if($lazy) $content = DC::lazy($content);
		$content = DC::format($content, $DT_PC);
	}
	$CP = $MOD['cat_property'] && $CAT['property'];
	if($CP) {
		require DT_ROOT.'/include/property.func.php';
		$options = property_option($catid);
		$values = property_value($moduleid, $itemid);
	}

	$update = '';
	$fee = DC::fee($item['fee'], $MOD['fee_view']);
	if(check_group($_groupid, $MOD['group_contact'])) {
		if($fee) {
			$user_status = 4;
			$destoon_task = "moduleid=$moduleid&html=show&itemid=$itemid";
			$description = '';
		} else {
			$user_status = 3;
		}
	} else {
		$user_status = $_userid ? 1 : 0;
	}
	if($user_status != 3 && $_username && $item['username'] == $_username) {
		$user_status = 3;
		$destoon_task = '';
	}
	$JS[] = 'moment';
	$JS[] = 'player';
	$comment_url = $EXT['comment_url'];
	$item['more'] = 0;
	if($item['open'] == 2 && !followed($item['userid']) && $item['userid'] != $_userid) $content = $L['fans_only'];
	if($item['open'] == 3 && !friended($item['userid']) && $item['userid'] != $_userid) $content = $L['friend_only'];
	$introduce = strip_tags($item['introduce']);
	$item['introduce'] = nl2br($content);
	$showpage = 0;
	$tags = array($item);
	if($EXT['mobile_enable']) $head_mobile = str_replace($MOD['linkurl'], $MOD['mobile'], $linkurl);
} else {
	$itemid or dheader($MOD['mobile']);
	$item = $db->get_one("SELECT * FROM {$table} WHERE itemid=$itemid");
	($item && $item['status'] > 2) or message($L['msg_not_exist']);
	require DT_ROOT.'/include/content.class.php';
	extract($item);
	$CAT = get_cat($catid);
	if(!check_group($_groupid, $MOD['group_show']) || !check_group($_groupid, $CAT['group_show'])) message($L['msg_no_right']);
	$member = array();
	$fee = DC::fee($item['fee'], $MOD['fee_view']);
	include DT_ROOT.'/mobile/api/contact.inc.php';
	$content_table = content_table($moduleid, $itemid, $MOD['split'], $table_data);
	$t = $db->get_one("SELECT content FROM {$content_table} WHERE itemid=$itemid");
	$content = $t['content'];
	if($content) {
		if($MOD['keylink']) $content = DC::keylink($content, $moduleid, $DT_PC);
		if($share_icon) $share_icon = DC::icon($thumb, $content);
		$content = DC::format($content, 0);
	}
	$CP = $MOD['cat_property'] && $CAT['property'];
	if($CP) {
		require DT_ROOT.'/include/property.func.php';
		$options = property_option($catid);
		$values = property_value($moduleid, $itemid);
	}
	$comment_url = $EXT['comment_mob'];
	$item['more'] = 0;
	if($item['open'] == 2 && !followed($item['userid']) && $item['userid'] != $_userid) $content = $L['fans_only'];
	if($item['open'] == 3 && !friended($item['userid']) && $item['userid'] != $_userid) $content = $L['friend_only'];
	$introduce = strip_tags($item['introduce']);
	$item['introduce'] = nl2br($content);
	$showpage = 0;
	$tags = array($item);
	$editdate = timetodate($edittime, 5);
	$update = '';
	$head_title = $head_name = $CAT['catname'];
	$js_item = 1;
	$foot = '';
}
if(!$DT_BOT) include DT_ROOT.'/include/update.inc.php';
$seo_file = 'show';
include DT_ROOT.'/include/seo.inc.php';
$template = $item['template'] ? $item['template'] : ($CAT['show_template'] ? $CAT['show_template'] : ($MOD['template_show'] ? $MOD['template_show'] : 'show'));
include template($template, $module);
?>