<?php
defined('IN_DESTOON') or exit('Access Denied');
class article {
	var $moduleid;
	var $itemid;
	var $table;
	var $table_data;
	var $split;
	var $fields;
	var $errmsg = errmsg;

    function __construct($moduleid) {
		global $table, $table_data, $MOD;
		$this->moduleid = $moduleid;
		$this->table = $table;
		$this->table_data = $table_data;
		$this->split = $MOD['split'];
        // $this->split = 1;
		$this->fields = array('catid','areaid','level','title','style','fee','subtitle','introduce','thumb','tag','author','copyfrom','fromurl','voteid','status','hits','username','addtime','editor','edittime','ip','template','islink','linkurl','filepath','note');
    }

    function article($moduleid) {
		$this->__construct($moduleid);
    }

	function pass($post) {
		if(!is_array($post)) return false;
        // die(var_dump(lang('message->pass_catid')));string(15) "请选择分类"
		if(!$post['catid']) return $this->_(lang('message->pass_catid'));
        // 		die(var_dump(lang('message->pass_title')));string(15) "请填写标题"
		if(strlen($post['title']) < 3) return $this->_(lang('message->pass_title'));
        //  如果是站外链接，则链接不能为空
		if(isset($post['islink'])) {
            //  如果是站外链接，则链接不能为空
			if(!$post['linkurl']) return $this->_(lang('message->pass_linkurl'));
		} else {
            //  否则内容不能为空
			if(!$post['content']) return $this->_(lang('message->pass_content'));
		}
        // die(var_dump(lang('message->pass_max')));// 		内容最大字数限制
		if(DT_MAX_LEN && strlen(clear_img($post['content'])) > DT_MAX_LEN) return $this->_(lang('message->pass_max'));
		return true;
	}

	function set($post) {
		global $MOD, $_username, $_userid, $_cname;
//        var_dump($post);die();
		is_url($post['thumb']) or $post['thumb'] = '';
		$post['filepath'] = (isset($post['filepath']) && is_filepath($post['filepath'])) ? file_vname($post['filepath']) : '';
		$post['islink'] = isset($post['islink']) ? 1 : 0;
		$post['addtime'] = (isset($post['addtime']) && is_time($post['addtime'])) ? datetotime($post['addtime']) : DT_TIME;
		if($post['addtime'] > DT_TIME && $post['status'] == 3) $post['status'] = 4;
		$post['editor'] = $_cname ? $_cname : $_username;
		$post['edittime'] = DT_TIME;
		$post['fee'] = dround($post['fee']);
		$post['content'] = stripslashes($post['content']);

		$post['content'] = save_local($post['content']);
//
		if($post['content'] && isset($post['clear_link']) && $post['clear_link']) $post['content'] = clear_link($post['content']);
		if($post['content'] && isset($post['save_remotepic']) && $post['save_remotepic']) $post['content'] = save_remote($post['content']);

        //        选择文章第几个图片作为缩略图
		if($post['content'] && $post['thumb_no'] && !$post['thumb']) $post['thumb'] = save_thumb($post['content'], $post['thumb_no'], $MOD['thumb_width'], $MOD['thumb_height']);
		if(strpos($post['content'], 'de-pagebreak') !== false) {
			$post['content'] = str_replace('"de-pagebreak" /', '"de-pagebreak"/', $post['content']);
			$post['content'] = str_replace(array('<hr class="de-pagebreak"/></p>', '<p><hr class="de-pagebreak"/>', '<hr class="de-pagebreak"/></div>', '<div><hr class="de-pagebreak"/>'), array('</p><hr class="de-pagebreak"/>', '<hr class="de-pagebreak"/><p>', '</div><hr class="de-pagebreak"/>', '<hr class="de-pagebreak"/><div>'), $post['content']);
		}
		if($post['content'] && !$post['introduce'] && $post['introduce_length']) $post['introduce'] = addslashes(get_intro($post['content'], $post['introduce_length']));
//        var_dump($post);die();
        //      替换缩略图，和删除不使用的图片
        if($this->itemid) {
			$new = $post['content'];
			if($post['thumb']) $new .= '<img src="'.$post['thumb'].'"/>';
			$r = $this->get_one();
			$old = $r['content'];
			if($r['thumb']) $old .= '<img src="'.$r['thumb'].'"/>';
			delete_diff($new, $old, $this->itemid);
		} else {

			$post['ip'] = DT_IP;
		}
//

//        var_dump($post);die();
		$content = $post['content'];
		unset($post['content']);
		$post = dhtmlspecialchars($post);
		$post['content'] = addslashes(dsafe($content));


        // 		以下是个别文章模块不同的入库规则。根据不同的采集文章修改、开启即可
//        $post['areaid']=getFirst($post['areaid'],"^");
//        $post['areaid']=$post['areaid']==''?0:$post['areaid'];
                 /*
                目前只有21模块才有这两个字段。
                $post['schoolid']=getAllStr($post['schoolid'],'^');
                $post['schoolid']=$post['schoolid']==''?0:$post['schoolid'];
                $post['shuxingid']=getAllStr($post['shuxingid'],'^');
                $post['shuxingid']=$post['shuxingid']==''?0:$post['shuxingid'];
                */
                /*
               start  这部分是dxsbb.com转么定制的分类
              $post['catid'] = getLastNumOrFirst($post['catid'],'^');
               */
                /*
              END 这部分是dxsbb.com转么定制的分类
              */
                /*第一范文网，原分类直接对应本站分类*/
                // 如果是数字，获取最后一个数字。
                // 如果是字符串，因为数据库字段要求是int，则转为0
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

	function get_list($condition = 'status=3', $order = 'addtime DESC', $cache = '') {
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
			if(!$r['islink']) $r['linkurl'] = $MOD['linkurl'].$r['linkurl'];
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
		$this->update($this->itemid);
		if($post['status'] == 3 && $post['username'] && $MOD['credit_add']) {
			credit_add($post['username'], $MOD['credit_add']);
			credit_record($post['username'], $MOD['credit_add'], 'system', lang('my->credit_record_add', array($MOD['name'])), 'ID:'.$this->itemid);
		}
		clear_upload($post['content'].$post['thumb'], $this->itemid);
//        die();
		return $this->itemid;
	}

	function edit($post) {
//        print_r("5555");die();
		$this->delete($this->itemid, false);
//                print_r("5555");die();
//        删除不使用的图片在这里面 set($post)
		$post = $this->set($post);
//        print_r(8);
//        die();
	    DB::query("UPDATE {$this->table} SET ".arr2sql($post, 1, $this->fields)." WHERE itemid=$this->itemid");
		$content_table = content_table($this->moduleid, $this->itemid, $this->split, $this->table_data);
		DB::query("REPLACE INTO {$content_table} (itemid,content) VALUES ('$this->itemid', '$post[content]')");
//        print_r(8);
//        die();
        $this->update($this->itemid);
//        print_r(8);
//        die();
		clear_upload($post['content'].$post['thumb'], $this->itemid);

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

	function update($itemid) {
		$item = DB::get_one("SELECT * FROM {$this->table} WHERE itemid=$itemid");
		$update = '';
		$keyword = $item['title'].','.($item['tag'] ? str_replace(' ', ',', trim($item['tag'])).',' : '').strip_tags(cat_pos(get_cat($item['catid']), ','));
		if($keyword != $item['keyword']) {
			$keyword = str_replace("//", '', addslashes($keyword));
			$update .= ",keyword='$keyword'";
		}
		$item['itemid'] = $itemid;
		$linkurl = itemurl($item);
		if($linkurl != $item['linkurl']) $update .= ",linkurl='$linkurl'";
		if($item['addtime'] > DT_TIME && $item['status'] == 3) $update .= ",status=4";
		if($item['addtime'] < DT_TIME && $item['status'] == 4) $update .= ",status=3";
		if($update) DB::query("UPDATE {$this->table} SET ".(substr($update, 1))." WHERE itemid=$itemid");
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
			if($MOD['show_html'] && !$r['islink']) {
				$_file = DT_ROOT.'/'.$MOD['moduledir'].'/'.$r['linkurl'];
				html_del($_file);
				$i = 1;
				while($i) {
					$_file = DT_ROOT.'/'.$MOD['moduledir'].'/'.itemurl($r, $i);
					if(is_file($_file)) {
						html_del($_file);
						$i++;
					} else {
						break;
					}
				}
			}
			if($all) {
				$userid = get_user($r['username']);
				if($r['thumb']) delete_upload($r['thumb'], $userid, $itemid);
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

	function _($e) {
		$this->errmsg = $e;
		return false;
	}
}
?>