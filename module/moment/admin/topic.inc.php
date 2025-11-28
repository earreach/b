<?php
defined('DT_ADMIN') or exit('Access Denied');
$menus = array (
    array('添加话题', '?file='.$file.'&moduleid='.$moduleid.'&action=add'),
    array('话题列表', '?file='.$file.'&moduleid='.$moduleid),
    array('审核话题', '?file='.$file.'&moduleid='.$moduleid.'&action=check'),
    array('未通过', '?file='.$file.'&moduleid='.$moduleid.'&action=reject'),
    array('回收站', '?file='.$file.'&moduleid='.$moduleid.'&action=recycle'),
);
$MOD['level'] = '';
$do = new topic();
if(in_array($action, array('', 'check', 'reject', 'recycle'))) {
	$sfields = array('按条件', '话题', '简介', '主持人', '编辑');
	$dfields = array('title', 'title', 'content', 'username', 'editor');
	isset($fields) && isset($dfields[$fields]) or $fields = 0;
	$sorder  = array('结果排序方式', '添加时间降序', '添加时间升序', '修改时间降序', '修改时间升序', '最后参与降序', '最后参与升序', '信息数量降序', '信息数量升序', '浏览次数降序', '浏览次数升序', '推荐级别降序', '推荐级别升序', '信息ID降序', '信息ID升序');
	$dorder  = array('addtime DESC', 'addtime DESC', 'addtime ASC', 'edittime DESC', 'edittime ASC', 'lasttime DESC', 'lasttime ASC', 'item DESC', 'item ASC', 'hits DESC', 'hits ASC', 'level DESC', 'level ASC', 'itemid DESC', 'itemid ASC');			
	isset($order) && isset($dorder[$order]) or $order = 0;
	$level = isset($level) ? intval($level) : 0;
	isset($datetype) && in_array($datetype, array('addtime', 'edittime', 'lasttime')) or $datetype = 'addtime';
	(isset($fromdate) && is_time($fromdate)) or $fromdate = '';
	$fromtime = $fromdate ? datetotime($fromdate) : 0;
	(isset($todate) && is_time($todate)) or $todate = '';
	$totime = $todate ? datetotime($todate) : 0;
	(isset($username) && check_name($username)) or $username = '';
	$itemid or $itemid = '';

	$fields_select = dselect($sfields, 'fields', '', $fields);
	$level_select = level_select('level', '级别', $level, 'all');
	$order_select  = dselect($sorder, 'order', '', $order);

	$condition = '';
	if($keyword) $condition .= match_kw($dfields[$fields], $keyword);
	if($catid) $condition .= ($CAT['child']) ? " AND catid IN (".$CAT['arrchildid'].")" : " AND catid=$catid";
	if($level) $condition .= $level > 9 ? " AND level>0" : " AND level=$level";
	if($fromtime) $condition .= " AND `$datetype`>=$fromtime";
	if($totime) $condition .= " AND `$datetype`<=$totime";
	if($username) $condition .= " AND username='$username'";
	if($itemid) $condition .= " AND itemid=$itemid";
}
switch($action) {
	case 'add':
		if($submit) {
			if($do->pass($post)) {
				$do->add($post);
				dmsg('添加成功', '?moduleid='.$moduleid.'&file='.$file.'&action='.$action.'&catid='.$post['catid']);
			} else {
				msg($do->errmsg);
			}
		} else {
			foreach($do->fields as $v) {
				isset($$v) or $$v = '';
			}
			$content = '';
			$username = $_username;
			$status = 3;
			$addtime = timetodate($DT_TIME);
			$menuid = 0;
			include tpl('topic_edit', $module);
		}
	break;
	case 'edit':
		$itemid or msg();
		$do->itemid = $itemid;
		if($submit) {
			if($do->pass($post)) {
				$do->edit($post);
				dmsg('修改成功', $forward);
			} else {
				msg($do->errmsg);
			}
		} else {
			extract($do->get_one());
			$addtime = timetodate($addtime);
			$menuid = 1;
			include tpl('topic_edit', $module);
		}
	break;
	case 'delete':
		$itemid or msg('请选择话题');
		isset($recycle) ? $do->recycle($itemid) : $do->delete($itemid);
		dmsg('删除成功', $forward);
	break;
	case 'restore':
		$itemid or msg('请选择话题');
		$do->restore($itemid);
		dmsg('还原成功', $forward);
	break;
	case 'clear':
		$do->clear();
		dmsg('清空成功', $forward);
	break;
	case 'level':
		$itemid or msg('请选择话题');
		$level = intval($level);
		$do->level($itemid, $level);
		dmsg('级别设置成功', $forward);
	break;
	case 'recycle':
		$lists = $do->get_list('status=0'.$condition, $dorder[$order]);
		$menuid = 4;
		include tpl('topic', $module);
	break;
	case 'reject':
		$lists = $do->get_list('status=1'.$condition, $dorder[$order]);
		$menuid = 3;
		include tpl('topic', $module);
	break;
	case 'check':
		if($itemid && !$psize) {
			$do->check($itemid);
			dmsg('审核成功', $forward);
		} else {
			$lists = $do->get_list('status=2'.$condition, $dorder[$order]);
			$menuid = 2;
			include tpl('topic', $module);
		}
	break;
	default:
		$lists = $do->get_list('status=3'.$condition, $dorder[$order]);
		$menuid = 1;
		include tpl('topic', $module);
	break;
}

class topic {
	var $itemid;
	var $table;
	var $fields;

	function __construct() {
		global $table_topic;
		$this->table = $table_topic;
		$this->fields = array('title','catid','level','style','username','addtime','lasttime','editor','edittime','seo_title','seo_keywords','seo_description','content','status');
	}

	function topic() {
		$this->__construct();
	}

	function pass($post) {
		if(!is_array($post)) return false;
		if(!$post['catid']) return $this->_('请选择所属分类');
		if(!$post['title']) return $this->_('请填写标题');
		return true;
	}

	function set($post) {
		global $MOD, $_username;
		$post['addtime'] = (isset($post['addtime']) && is_time($post['addtime'])) ? datetotime($post['addtime']) : DT_TIME;
		$post['editor'] = $_username;
		$post['edittime'] = DT_TIME;
		$post['content'] = addslashes(save_remote(save_local(stripslashes($post['content']))));
		if($this->itemid) {
			$new = $post['content'];
			$r = $this->get_one();
			$old = $r['content'];
			delete_diff($new, $old, $this->itemid);
		} else {			
			$post['lasttime'] = DT_TIME;
		}
		return array_map("trim", $post);
	}

	function get_one($condition = '') {
        return DB::get_one("SELECT * FROM {$this->table} WHERE itemid='$this->itemid' $condition");
	}

	function get_list($condition = '1', $order = 'addtime DESC') {
		global $pages, $page, $offset, $pagesize, $MOD, $sum;
		if($page > 1 && $sum) {
			$items = $sum;
		} else {
			$r = DB::get_one("SELECT COUNT(*) AS num FROM {$this->table} WHERE {$condition}");
			$items = $r['num'];
		}
		$pages = pages($items, $page, $pagesize);
		$lists = $catids = $CATS = array();
		$result = DB::query("SELECT * FROM {$this->table} WHERE {$condition} ORDER BY {$order} LIMIT {$offset},{$pagesize}");
		while($r = DB::fetch_array($result)) {
			$r['adddate'] = timetodate($r['addtime'], 5);
			$r['editdate'] = timetodate($r['edittime'], 5);
			$r['lastdate'] = timetodate($r['lasttime'], 5);
			$r['alt'] = $r['title'];
			$r['title'] = set_style($r['title'], $r['style']);
			$r['linkurl'] = $MOD['linkurl'].rewrite('topic'.DT_EXT.'?itemid='.$r['itemid']);
			$catids[$r['catid']] = $r['catid'];
			$lists[] = $r;
		}
		if($catids) {
			$result = DB::query("SELECT catid,catname,linkurl FROM ".DT_PRE."category WHERE catid IN (".implode(',', $catids).")");
			while($r = DB::fetch_array($result)) {
				$CATS[$r['catid']] = $r;
			}
			if($CATS) {
				foreach($lists as $k=>$v) {
					$lists[$k]['catname'] = $v['catid'] ? $CATS[$v['catid']]['catname'] : '';
					$lists[$k]['caturl'] = $v['catid'] ? $MOD['linkurl'].rewrite('topic'.DT_EXT.'?catid='.$v['catid']) : '';
				}
			}
		}
		return $lists;
	}

	function add($post) {
		$post = $this->set($post);
		DB::query("INSERT INTO {$this->table} ".arr2sql($post, 0, $this->fields));
		$this->itemid = DB::insert_id();
		clear_upload($post['content'], $this->itemid, $this->table);
		return $this->itemid;
	}

	function edit($post) {
		$post = $this->set($post);
	    DB::query("UPDATE {$this->table} SET ".arr2sql($post, 1, $this->fields)." WHERE itemid=$this->itemid");
		clear_upload($post['content'], $this->itemid, $this->table);
		return true;
	}

	function delete($itemid) {
		global $table;
		if(is_array($itemid)) {
			foreach($itemid as $v) { $this->delete($v); }
		} else {
			$this->itemid = $itemid;
			$r = $this->get_one();
			$userid = get_user($r['username']);
			if($r['content']) delete_local($r['content'], $userid);
			DB::query("DELETE FROM {$this->table} WHERE itemid=$itemid");
			DB::query("UPDATE {$table} WHERE SET topicid=0,topic='' WHERE topicid=$itemid");
		}
	}

	function recycle($itemid) {
		global $_username;
		if(is_array($itemid)) {
			foreach($itemid as $v) { $this->recycle($v); }
		} else {
			DB::query("UPDATE {$this->table} SET status=0,editor='$_username' WHERE itemid=$itemid");
			return true;
		}
	}

	function restore($itemid) {
		if(is_array($itemid)) {
			foreach($itemid as $v) { $this->restore($v); }
		} else {
			DB::query("UPDATE {$this->table} SET status=3 WHERE itemid=$itemid");
			return true;
		}		
	}

	function check($itemid) {
		global $_username;
		if(is_array($itemid)) {
			foreach($itemid as $v) { $this->check($v); }
		} else {
			DB::query("UPDATE {$this->table} SET status=3,editor='$_username' WHERE itemid=$itemid");
			return true;
		}
	}

	function clear($condition = 'status=0') {		
		$result = DB::query("SELECT itemid FROM {$this->table} WHERE {$condition}");
		while($r = DB::fetch_array($result)) {
			$this->delete($r['itemid']);
		}
	}

	function level($itemid, $level) {
		$itemids = is_array($itemid) ? implode(',', $itemid) : $itemid;
		DB::query("UPDATE {$this->table} SET level=$level WHERE itemid IN ($itemids)");
	}

	function _($e) {
		$this->errmsg = $e;
		return false;
	}
}
?>