<?php
defined('DT_ADMIN') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/'.$module.'.class.php';
$do = new $module($moduleid);
$menus = array (
    array('添加'.$MOD['name'], '?moduleid='.$moduleid.'&action=add'),
    array($MOD['name'].'列表', '?moduleid='.$moduleid),
    array('审核'.$MOD['name'], '?moduleid='.$moduleid.'&action=check'),
    array('待发布', '?moduleid='.$moduleid.'&action=expire'),
    array('未通过', '?moduleid='.$moduleid.'&action=reject'),
    array('回收站', '?moduleid='.$moduleid.'&action=recycle'),
    array('移动分类', '?moduleid='.$moduleid.'&action=move'),
);

if(in_array($action, array('add', 'edit'))) {
	$FD = cache_read('fields-'.substr($table, strlen($DT_PRE)).'.php');
	if($FD) require DT_ROOT.'/include/fields.func.php';
	isset($post_fields) or $post_fields = array();
	$CP = $MOD['cat_property'];
	if($CP) require DT_ROOT.'/include/property.func.php';
	isset($post_ppt) or $post_ppt = array();
}

if($_catids || $_areaids) require DT_ROOT.'/module/destoon/admin/check.inc.php';

if(in_array($action, array('', 'check', 'expire', 'reject', 'recycle'))) {
	$sfields = array('模糊', '标题', '话题', '图片地址', '视频地址', '链接地址', '会员名', '昵称', '编辑', 'IP', '文件路径', '内容模板', '链接地址');
	$dfields = array('keyword', 'title', 'topic', 'thumbs', 'video', 'linkto', 'username', 'passport', 'editor', 'ip', 'filepath', 'template', 'linkurl');
	isset($fields) && isset($dfields[$fields]) or $fields = 0;
	$sorder  = array('结果排序方式', '添加时间降序', '添加时间升序', '更新时间降序', '更新时间升序', '推荐级别降序', '推荐级别升序', '浏览次数降序', '浏览次数升序', '点赞次数降序', '点赞次数升序', '反对次数降序', '反对次数升序', '收藏次数降序', '收藏次数升序', '打赏次数降序', '打赏次数升序', '打赏金额降序', '打赏金额升序', '分享次数降序', '分享次数升序', '举报次数降序', '举报次数升序', '评论数量降序', '评论数量升序', '转发数量降序', '转发数量升序',  '信息ID降序', '信息ID升序');
	$dorder  = array($MOD['order'], 'addtime DESC', 'addtime ASC', 'edittime DESC', 'edittime DESC', 'level DESC', 'level ASC', 'hits DESC', 'hits ASC', 'likes DESC', 'likes ASC', 'hates DESC', 'hates ASC', 'favorites DESC', 'favorites ASC', 'awards DESC', 'awards ASC', 'award DESC', 'award ASC', 'shares DESC', 'shares ASC', 'reports DESC', 'reports ASC', 'comments DESC', 'comments ASC', 'quotes DESC', 'quotes ASC', 'itemid DESC', 'itemid ASC');
	isset($order) && isset($dorder[$order]) or $order = 0;

	isset($datetype) && in_array($datetype, array('edittime', 'addtime')) or $datetype = 'addtime';
	(isset($fromdate) && is_time($fromdate)) or $fromdate = '';
	$fromtime = $fromdate ? datetotime($fromdate) : 0;
	(isset($todate) && is_time($todate)) or $todate = '';
	$totime = $todate ? datetotime($todate) : 0;
	$level = isset($level) ? intval($level) : 0;
	$areaid = isset($areaid) ? intval($areaid) : 0;
	$thumb = isset($thumb) ? intval($thumb) : 0;
	$video = isset($video) ? intval($video) : 0;
	$link = isset($link) ? intval($link) : 0;
	$topic = isset($topic) ? intval($topic) : 0;
	$quote = isset($quote) ? intval($quote) : 0;
	$tid = isset($tid) ? intval($tid) : 0;
	$topicid = isset($topicid) ? intval($topicid) : 0;
	$quoteid = isset($quoteid) ? intval($quoteid) : 0;
	$open = isset($open) ? intval($open) : -1;
	$comment = isset($comment) ? intval($comment) : -1;

	$itemid or $itemid = '';
	$tid or $tid = '';
	$topicid or $topicid = '';
	$quoteid or $quoteid = '';
	(isset($ip) && is_ip($ip)) or $ip= '';
	(isset($username) && check_name($username)) or $username = '';
	(isset($passport) && check_name($passport)) or $passport = '';

	$fields_select = dselect($sfields, 'fields', '', $fields);
	$level_select = level_select('level', '级别', $level, 'all');
	$order_select  = dselect($sorder, 'order', '', $order);
	$module_select = module_select('mid', '模块', $mid, '', '1,2,3,4');

	$condition = '';
	if($_childs) $condition .= " AND catid IN (".$_childs.")";//CATE
	if($_areaids) $condition .= " AND areaid IN (".$_areaids.")";//CITY
	if($_self) $condition .= " AND username='$_username'";//SELF
	if($keyword) $condition .= match_kw($dfields[$fields], $keyword);
	if($catid) $condition .= ($CAT['child']) ? " AND catid IN (".$CAT['arrchildid'].")" : " AND catid=$catid";
	if($areaid) $condition .= ($AREA[$areaid]['child']) ? " AND areaid IN (".$AREA[$areaid]['arrchildid'].")" : " AND areaid=$areaid";
	if($level) $condition .= $level > 9 ? " AND level>0" : " AND level=$level";
	if($fromtime) $condition .= " AND `$datetype`>=$fromtime";
	if($totime) $condition .= " AND `$datetype`<=$totime";
	if($thumb) $condition .= " AND thumb<>''";
	if($video) $condition .= " AND video<>''";
	if($link) $condition .= " AND linkto<>''";
	if($topic) $condition .= " AND topicid>0";
	if($quote) $condition .= " AND quoteid>0";
	if($ip) $condition .= " AND ip='$ip'";
	if($username) $condition .= " AND username='$username'";
	if($passport) $condition .= " AND passport='$passport'";
	if($mid) $condition .= " AND mid='$mid'";
	if($tid) $condition .= " AND tid='$tid'";
	if($topicid) $condition .= " AND topicid=$topicid";
	if($quoteid) $condition .= " AND quoteid=$quoteid";
	if($open > -1) $condition .= " AND open=$open";
	if($comment > -1) $condition .= " AND comment=$comment";
	if($itemid) $condition .= " AND itemid=$itemid";

	$timetype = strpos($dorder[$order], 'add') !== false ? 'add' : '';
}
switch($action) {
	case 'add':
		if($submit) {
			if($do->pass($post)) {
				if($FD) fields_check($post_fields);
				if($CP) property_check($post_ppt);
				$do->add($post);
				if($FD) fields_update($post_fields, $table, $do->itemid);
				if($CP) property_update($post_ppt, $moduleid, $post['catid'], $do->itemid);
				if($MOD['show_html'] && $post['status'] > 2) $do->tohtml($do->itemid);
				dmsg('添加成功', '?moduleid='.$moduleid.'&action='.$action.'&catid='.$post['catid']);
			} else {
				msg($do->errmsg);
			}
		} else {
			include DT_ROOT.'/file/config/face.inc.php';
			$topicid = isset($topicid) ? intval($topicid) : 0;
			$quoteid = isset($quoteid) ? intval($quoteid) : 0;
			foreach($do->fields as $v) {
				isset($$v) or $$v = '';
			}
			$content = '';
			$status = 3;
			$username = $_username;
			$item = $thumbs = array();
			$addtime = timetodate($DT_TIME);
			$open = $comment = 1;
			$topid = isset($topicid) ? intval($topicid) : 0;
			$topic = $topicid ? $do->get_topic($topicid) : array();
			if($topic && $topic['status'] != 3) $topic = array();
			$quote = $quoteid ? $do->get_quote($quoteid) : array();
			if($quote && ($quote['status'] != 3 || $quote['open'] != 1)) $quote = array();
			$quoteid > 0 or $quoteid = '';
			$topicid > 0 or $topicid = '';
			$mid > 0 or $mid = '';
			$tid > 0 or $tid = '';
			$menuid = 0;
			isset($url) or $url = '';
			if($url) {
				$tmp = fetch_url($url);
				if($tmp) extract($tmp);
			}
			$history = 0;
			include tpl('edit', $module);
		}
	break;
	case 'edit':
		$itemid or msg();
		$do->itemid = $itemid;
		if($submit) {
			if($do->pass($post)) {
				if($FD) fields_check($post_fields);
				if($CP) property_check($post_ppt);
				if($FD) fields_update($post_fields, $table, $do->itemid);
				if($CP) property_update($post_ppt, $moduleid, $post['catid'], $do->itemid);
				$do->edit($post);
				dmsg('修改成功', $forward);
			} else {
				msg($do->errmsg);
			}
		} else {
			include DT_ROOT.'/file/config/face.inc.php';
			$item = $do->get_one();
			extract($item);
			$history = history($moduleid, $itemid);
			$thumbs = get_thumbs($item);
			$addtime = timetodate($addtime);
			$topic = $topicid ? $do->get_topic($topicid) : array();
			if($topic && $topic['status'] != 3) $topic = array();
			$quote = $quoteid ? $do->get_quote($quoteid) : array();
			if($quote && ($quote['status'] != 3 || $quote['open'] != 1)) $quote = array();
			$quoteid > 0 or $quoteid = '';
			$topicid > 0 or $topicid = '';
			$mid > 0 or $mid = '';
			$tid > 0 or $tid = '';
			$content = strip_tags($content);
			$menuon = array('5', '4', '2', '1', '3');
			$menuid = $menuon[$status];
			include tpl($action, $module);
		}
	break;
	case 'move':
		if($submit) {
			$fromids or msg('请填写来源ID');
			if($tocatid) {
				$db->query("UPDATE {$table} SET catid=$tocatid WHERE `{$fromtype}` IN ($fromids)");
				dmsg('移动成功', $forward);
			} else {
				msg('请选择目标分类');
			}
		} else {
			$itemid = $itemid ? implode(',', $itemid) : '';
			$menuid = 6;
			include tpl($action);
		}
	break;
	case 'update':
		is_array($itemid) or msg('请选择'.$MOD['name']);
		foreach($itemid as $v) {
			$do->update($v);
		}
		dmsg('更新成功', $forward);
	break;
	case 'tohtml':
		is_array($itemid) or msg('请选择'.$MOD['name']);
		foreach($itemid as $itemid) {
			tohtml('show', $module);
		}
		dmsg('生成成功', $forward);
	break;
	case 'delete':
		$itemid or msg('请选择'.$MOD['name']);
		isset($recycle) ? $do->recycle($itemid) : $do->delete($itemid);
		dmsg('删除成功', $forward);
	break;
	case 'restore':
		$itemid or msg('请选择'.$MOD['name']);
		$do->restore($itemid);
		dmsg('还原成功', $forward);
	break;
	case 'refresh':
		$itemid or msg('请选择'.$MOD['name']);
		$do->refresh($itemid);
		dmsg('刷新成功', $forward);
	break;
	case 'clear':
		$do->clear();
		dmsg('清空成功', $forward);
	break;
	case 'level':
		$itemid or msg('请选择'.$MOD['name']);
		$level = intval($level);
		$do->level($itemid, $level);
		dmsg('级别设置成功', $forward);
	break;
	case 'recycle':
		$lists = $do->get_list('status=0'.$condition, $dorder[$order]);
		$menuid = 5;
		include tpl('index', $module);
	break;
	case 'reject':
		if($itemid && !$psize) {
			$do->reject($itemid);
			dmsg('拒绝成功', $forward);
		} else {
			$lists = $do->get_list('status=1'.$condition, $dorder[$order]);
			$menuid = 4;
			include tpl('index', $module);
		}
	break;
	case 'expire':
		if(isset($refresh)) {
			$db->query("UPDATE {$table} SET status=3 WHERE status=4 AND addtime<$DT_TIME");
			dmsg('刷新成功', $forward);
		} else {
			$lists = $do->get_list('status=4'.$condition);
			$menuid = 3;
			include tpl('index', $module);
		}
	break;
	case 'check':
		if($itemid && !$psize) {
			$do->check($itemid);
			dmsg('审核成功', $forward);
		} else {
			$lists = $do->get_list('status=2'.$condition, $dorder[$order]);
			$menuid = 2;
			include tpl('index', $module);
		}
	break;
	default:
		$lists = $do->get_list('status=3'.$condition, $dorder[$order]);
		$menuid = 1;
		include tpl('index', $module);
	break;
}
?>