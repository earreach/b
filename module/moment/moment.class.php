<?php 
defined('IN_DESTOON') or exit('Access Denied');
class moment {
	var $moduleid;
	var $itemid;
	var $topicid;
	var $table;
	var $table_topic;
	var $table_data;
	var $split;
	var $fields;
	var $ats = array();
	var $errmsg = errmsg;

    function __construct($moduleid) {
		global $table, $table_topic, $table_data, $MOD;
		$this->moduleid = $moduleid;
		$this->table = $table;
		$this->table_topic = $table_topic;
		$this->table_data = $table_data;
		$this->split = $MOD['split'];
		$this->fields = array('catid','areaid','level','title','style','fee','introduce','thumb','thumb1','thumb2','thumbs','video','linkto','mid','tid','quoteid','topicid','status','comment','open','more','hits','userid','username','addtime','editor','edittime','ip','template', 'linkurl','filepath','note');
    }

    function moment($moduleid) {
		$this->__construct($moduleid);
    }

	function pass($post) {
		global $MOD, $L;
		if(!is_array($post)) return false;
		if(word_count($post['content']) < 1) return $this->_(lang('message->pass_content'));
		#if(strpos($post['content'], '##') !== false) return $this->_($L['pass_ht2']);
		if(strpos($post['content'], '#') !== false && substr_count($post['content'], '#')%2 != 0) return $this->_($L['pass_ht']);
		if(strpos($post['content'], '@ ') !== false) return $this->_($L['pass_at1']);
		if(strpos($post['content'], '@@') !== false) return $this->_($L['pass_at2']);
		if(strpos($post['content'], '@') !== false && substr_count($post['content'], '@') > substr_count($post['content'], ' ')) return $this->_($L['pass_at']);
		if(DT_MAX_LEN && strlen(clear_img($post['content'])) > DT_MAX_LEN) return $this->_(lang('message->pass_max'));
		return true;
	}

	function set($post) {
		global $MOD, $AREA, $_username, $_userid, $_cname;
		$thumbs = array();
		foreach($post['thumbs'] as $v) {
			if(is_url($v)) $thumbs[] = $v;
		}
		if($thumbs) {
			$post['thumb'] = $thumbs[0];
			array_shift($thumbs);
			$post['thumbs'] = implode('|', $thumbs);
		} else {
			$post['thumb'] = $post['thumbs'] = '';
		}
		$post['thumb1'] = $post['thumb2'] = '';
		is_url($post['video']) or $post['video'] = '';
		save_poster($post['video'], $post['thumb']);
		is_url($post['linkto']) or $post['linkto'] = '';
		$post['mid'] = isset($post['mid']) ? intval($post['mid']) : 0;
		$post['tid'] = isset($post['tid']) ? intval($post['tid']) : 0;
		$post['topicid'] = intval($post['topicid']);
		$post['comment'] = intval($post['comment']);
		in_array($post['comment'], array(0, 1, 2)) or $post['comment'] = 1;
		$post['open'] = intval($post['open']);
		in_array($post['open'], array(0, 1, 2, 3)) or $post['open'] = 1;
		$post['filepath'] = (isset($post['filepath']) && is_filepath($post['filepath'])) ? file_vname($post['filepath']) : '';
		$post['addtime'] = (isset($post['addtime']) && is_time($post['addtime'])) ? datetotime($post['addtime']) : DT_TIME;
		$post['editor'] = $_cname ? $_cname : $_username;
		$post['edittime'] = DT_TIME;
		$post['fee'] = dround($post['fee']);
		$content = strip_tags(stripslashes($post['content']));
		$content = preg_replace("/&([a-z]{1,});/", '', $content);
		$post['more'] = word_count($content) > $MOD['introduce_length'] ? 1 : 0;
		if(!$post['title']) $post['title'] = $this->get_title($content);
		$post['content'] = $this->parse($content);
		$post['introduce'] = $post['more'] ? $this->get_intro($content, $MOD['introduce_length']) : $post['content'];
		if(!$post['topicid'] && $this->topicid) $post['topicid'] = $this->topicid;
		if($this->itemid) {
			$new = $post['content'];
			$new .= '<img src="'.$post['thumb'].'"/>';
			foreach($thumbs as $v) {
				$new .= '<img src="'.$v.'"/>';
			}
			$r = $this->get_one();
			$old = $r['content'];
			$old .= '<img src="'.$r['thumb'].'"/>';
			foreach(explode('|', $r['thumbs']) as $v) {
				$old .= '<img src="'.$v.'"/>';
			}
			delete_diff($new, $old, $this->itemid);
			if($r['video'] != $post['video']) delete_upload($r['video'], match_userid($r['video']), $this->itemid);
		} else {			
			$post['ip'] = DT_IP;
		}
		$introduce = $post['introduce'];
		$content = $post['content'];
		unset($post['introduce'], $post['content']);
		$post = dhtmlspecialchars($post);
		$post['introduce'] = $introduce;
		$post['content'] = $content;
		return array_map("trim", $post);
	}

	function get_one($content = 1) {
		$r = DB::get_one("SELECT * FROM {$this->table} WHERE itemid=$this->itemid");
		if(!$content) return $r;
		if($r) {
			$content_table = content_table($this->moduleid, $this->itemid, $this->split, $this->table_data);
			$t = DB::get_one("SELECT content FROM {$content_table} WHERE itemid=$this->itemid");
			$r['content'] = $t ? $t['content'] : '';
			return $r;
		} else {
			return array();
		}
	}

	function get_list($condition = 'status=3', $order = 'edittime DESC', $cache = '') {
		global $MOD, $pages, $page, $pagesize, $offset, $items, $sum;
		if($page > 1 && $sum) {
			$items = $sum;
		} else {
			$r = DB::get_one("SELECT COUNT(*) AS num FROM {$this->table} WHERE {$condition}", $cache);
			$items = $r['num'];
		}
		$pages = defined('CATID') ? listpages(1, CATID, $items, $page, $pagesize, 10, $MOD['linkurl']) : pages($items, $page, $pagesize);
		if($items < 1) return array();
		$lists = $catids = $CATS = array();
		$result = DB::query("SELECT * FROM {$this->table} WHERE {$condition} ORDER BY {$order} LIMIT {$offset},{$pagesize}", $cache);
		while($r = DB::fetch_array($result)) {
			$r['adddate'] = timetodate($r['addtime'], 5);
			$r['editdate'] = timetodate($r['edittime'], 5);
			$r['alt'] = $r['title'];
			$r['title'] = set_style($r['title'], $r['style']);
			$r['linkurl'] = $MOD['linkurl'].$r['linkurl'];
			$r['pics'] = get_thumbs($r);
			if(strpos($r['introduce'], ')') !== false) $r['introduce'] = parse_face($r['introduce']);
			$r['catname'] = $r['caturl'] = '';
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
					$lists[$k]['caturl'] = $v['catid'] ? $MOD['linkurl'].$CATS[$v['catid']]['linkurl'] : '';
				}
			}
		}
		return $lists;
	}

	function add($post) {
		global $MOD;
		$post = $this->set($post);
		DB::query("INSERT INTO {$this->table} ".arr2sql($post, 0, $this->fields));
		$this->itemid = DB::insert_id();
		$content_table = content_table($this->moduleid, $this->itemid, $this->split, $this->table_data);
		DB::query("REPLACE INTO {$content_table} (itemid,content) VALUES ('$this->itemid', '$post[content]')");
		$this->update($this->itemid, 1);
		if($post['status'] == 3 && $post['username'] && $MOD['credit_add']) {
			credit_add($post['username'], $MOD['credit_add']);
			credit_record($post['username'], $MOD['credit_add'], 'system', lang('my->credit_record_add', array($MOD['name'])), 'ID:'.$this->itemid);
		}
		clear_upload($post['content'].$post['thumb'].$post['thumbs'].$post['video'], $this->itemid);
		return $this->itemid;
	}

	function edit($post) {
		$this->delete($this->itemid, false);
		$post = $this->set($post);
	    DB::query("UPDATE {$this->table} SET ".arr2sql($post, 1, $this->fields)." WHERE itemid=$this->itemid");
		$content_table = content_table($this->moduleid, $this->itemid, $this->split, $this->table_data);
		DB::query("REPLACE INTO {$content_table} (itemid,content) VALUES ('$this->itemid', '$post[content]')");
		$this->update($this->itemid);
		clear_upload($post['content'].$post['thumb'].$post['thumbs'].$post['video'], $this->itemid);
		if($post['status'] > 2) {
			history($this->moduleid, $this->itemid, 'del');
			$this->tohtml($this->itemid, $post['catid']);
		}
		return true;
	}

	function tohtml($itemid = 0, $catid = 0) {
		global $module, $MOD;
		if($MOD['show_html'] && $itemid) tohtml('show', $module, "itemid=$itemid");
	}

	function update($itemid, $type = 0) {
		global $MOD, $L;
		$item = DB::get_one("SELECT * FROM {$this->table} WHERE itemid=$itemid");
		$update = '';
		$keyword = $item['title'].','.$item['topic'].','.strip_tags(cat_pos(get_cat($item['catid']), ','));
		if($keyword != $item['keyword']) {
			$keyword = str_replace("//", '', addslashes($keyword));
			$update .= ",keyword='$keyword'";
		}
		$video = url2video($item['video']);
		if($video && $video != $item['video']) $update .= ",video='$video'";
		$item['itemid'] = $itemid;
		$linkurl = itemurl($item);
		if($linkurl != $item['linkurl']) $update .= ",linkurl='$linkurl'";
		if($item['addtime'] > DT_TIME && $item['status'] == 3) $update .= ",status=4";
		if($item['addtime'] < DT_TIME && $item['status'] == 4) $update .= ",status=3";
		$member = check_name($item['username']) ? userinfo($item['username']) : array();
		$update .= $member ? ",userid=".$member['userid'].",passport='".addslashes($member['passport'])."'" : ",userid=0,username='',passport=''";
		if($item['topicid']) {
			$t = $this->get_topic($item['topicid']);
			if($t) {
				$item['topic'] = $t['title'];
				$update .= ",topic='".addslashes($item['topic'])."'";
			} else {
				$update .= ",topicid=0,topic=''";
			}
		}
		if($item['quoteid']) {
			$t = $this->get_quote($item['quoteid']);
			if($t && $t['status'] == 3 && $t['open'] == 1) {
				if($type) {
					DB::query("UPDATE {$this->table} SET quotes=quotes+1,hits=hits+1 WHERE itemid=$t[itemid]");
					if($t['username'] != $item['username']) send_message($t['username'], lang($L['message_quote_title'], array($member['passport'], $MOD['name'])), lang($L['message_quote'], array($member['passport'], $MOD['name'], $MOD['linkurl'].$t['linkurl'], $t['introduce'], $MOD['linkurl'].$linkurl, $item['introduce'])));
				}
			} else {
				$update .= ",quoteid=0";
			}
		}
		if($type && $this->ats) {
			foreach($this->ats as $touser) {
				if($touser != $item['username']) send_message($touser, lang($L['message_at_title'], array($member['passport'])), lang($L['message_at'], array($member['passport'], $MOD['linkurl'].$linkurl, $item['introduce'])));
			}
		}
		if($update) DB::query("UPDATE {$this->table} SET ".(substr($update, 1))." WHERE itemid=$itemid");
		if($member && $type && $item['open'] > 0) {
			DB::query("UPDATE ".DT_PRE."follow SET posttime=".DT_TIME." WHERE fuserid=".$member['userid']);
			DB::query("UPDATE ".DT_PRE."friend SET posttime=".DT_TIME." WHERE fuserid=".$member['userid']);
		}
	}

	function recycle($itemid) {
		if(is_array($itemid)) {
			foreach($itemid as $v) { $this->recycle($v); }
		} else {
			DB::query("UPDATE {$this->table} SET status=0 WHERE itemid=$itemid");
			$this->delete($itemid, false);
			return true;
		}		
	}

	function restore($itemid) {
		global $module, $MOD;
		if(is_array($itemid)) {
			foreach($itemid as $v) { $this->restore($v); }
		} else {
			DB::query("UPDATE {$this->table} SET status=3 WHERE itemid=$itemid");
			if($MOD['show_html']) tohtml('show', $module, "itemid=$itemid");
			return true;
		}		
	}

	function delete($itemid, $all = true) {
		global $MOD;
		if(is_array($itemid)) {
			foreach($itemid as $v) { 
				$this->delete($v, $all);
			}
		} else {
			$this->itemid = $itemid;
			$r = $this->get_one();
			if($MOD['show_html']) {
				$_file = DT_ROOT.'/'.$MOD['moduledir'].'/'.$r['linkurl'];
				html_del($_file);
			}
			if($all) {
				$userid = get_user($r['username']);
				if(!$r['mid'] && !$r['tid']) {
					if($r['video']) delete_upload($r['video'], $userid, $itemid);
					if($r['thumb']) delete_upload($r['thumb'], $userid, $itemid);
					if($r['thumbs']) {
						foreach(explode('|', $r['thumbs']) as $v) {
							if($v) delete_upload($v, $userid, $itemid);
						}
					}
				}
				if($r['content']) delete_local($r['content'], $userid, $itemid);
				DB::query("DELETE FROM {$this->table} WHERE itemid=$itemid");
				$content_table = content_table($this->moduleid, $this->itemid, $this->split, $this->table_data);
				DB::query("DELETE FROM {$content_table} WHERE itemid=$itemid");
				if($MOD['cat_property']) DB::query("DELETE FROM ".DT_PRE."category_value WHERE moduleid=$this->moduleid AND itemid=$itemid");
				if($r['username'] && $MOD['credit_del']) {
					credit_add($r['username'], -$MOD['credit_del']);
					credit_record($r['username'], -$MOD['credit_del'], 'system', lang('my->credit_record_del', array($MOD['name'])), 'ID:'.$this->itemid);
				}
				history($this->moduleid, $itemid, 'del');
			}
		}
	}

	function check($itemid) {
		global $_username, $MOD;
		if(is_array($itemid)) {
			foreach($itemid as $v) { $this->check($v); }
		} else {
			$this->itemid = $itemid;
			$item = $this->get_one(0);
			if($MOD['credit_add'] && $item['username'] && $item['hits'] < 1) {
				credit_add($item['username'], $MOD['credit_add']);
				credit_record($item['username'], $MOD['credit_add'], 'system', lang('my->credit_record_add', array($MOD['name'])), 'ID:'.$this->itemid);
			}
			$status = $item['addtime'] > DT_TIME ? 4 : 3;
			DB::query("UPDATE {$this->table} SET status=$status,editor='$_username',edittime=".DT_TIME." WHERE itemid=$itemid");
			history($this->moduleid, $itemid, 'del');
			$this->tohtml($itemid);			
			if($item['userid'] > 0 && $item['hits'] < 1 && $item['open'] > 0) {
				DB::query("UPDATE ".DT_PRE."follow SET posttime=".$item['addtime']." WHERE fuserid=".$item['userid']);
				DB::query("UPDATE ".DT_PRE."friend SET posttime=".$item['addtime']." WHERE fuserid=".$item['userid']);
			}
			return true;
		}
	}

	function reject($itemid) {
		global $_username;
		if(is_array($itemid)) {
			foreach($itemid as $v) { $this->reject($v); }
		} else {
			DB::query("UPDATE {$this->table} SET status=1,editor='$_username' WHERE itemid=$itemid");
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

	function parse($content) {
		$this->ats = array();
		if(strpos($content, '@') !== false) {
			if(preg_match_all("/\@([^\s\@]+)\s/i", $content, $matches)) {
				foreach($matches[1] as $k=>$v) {
					$url = $this->get_space($v);
					if($url) $content = str_replace($matches[0][$k], '<a href="'.$url.'" target="_blank">'.$matches[0][$k].'</a>', $content);
				}
			}
		}
		if(strpos($content, '#') !== false) {
			if(preg_match_all("/\#([^\#]+)\#/i", $content, $matches)) {				
				foreach($matches[1] as $k=>$v) {
					$url = $this->get_link($v);
					if($url) $content = str_replace($matches[0][$k], '<a href="'.$url.'" target="_blank">'.$matches[0][$k].'</a>', $content);
				}
			}
		}
		return addslashes($content);
	}

	function get_title($content, $len = 30) {
		$content = preg_replace("/\@([^\s\@]+)\s/i", '', $content);
		return addslashes(get_intro($content, 30, ''));
	}

	function get_intro($content, $len = 140) {
		return $this->parse(dsubstr($content, $len, '...'));
	}

	function get_space($name) {
		$username = '';
		if(check_name($name)) {
			$t = DB::get_one("SELECT username FROM ".DT_PRE."member WHERE username='$name'");
			if($t) $username = $t['username'];
		}
		if(!$username && is_passport($name)) {
			$t = DB::get_one("SELECT username FROM ".DT_PRE."member WHERE passport='$name'");
			if($t) $username = $t['username'];
		}
		if(check_name($username)) {
			$this->ats[$username] = $username;
			return userurl($username, 'file=space&mid='.$this->moduleid);
		}
		return '';
	}

	function get_link($kw) {
		global $MOD;
		$topicid = 0;
		if(is_clean($kw) && word_count($kw) < 30) {
			$t = DB::get_one("SELECT itemid FROM {$this->table_topic} WHERE title='$kw'");
			if($t) {
				$topicid = $t['itemid'];
				if(!$this->topicid) $this->topicid = $topicid;
			}
		}
		return $MOD['linkurl'].rewrite($topicid ? 'topic'.DT_EXT.'?itemid='.$topicid : 'search'.DT_EXT.'?kw='.urlencode($kw));
	}

	function get_topic($itemid) {
		$itemid = intval($itemid);
		$r = DB::get_one("SELECT * FROM {$this->table_topic} WHERE itemid=$itemid");
		return $r ? $r : array();
	}

	function get_quote($itemid) {
		$itemid = intval($itemid);
		$r = DB::get_one("SELECT * FROM {$this->table} WHERE itemid=$itemid");
		return $r ? $r : array();
	}

	function _($e) {
		$this->errmsg = $e;
		return false;
	}
}
?>