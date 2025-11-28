<?php
defined('IN_DESTOON') or exit('Access Denied');
function my_stats($userid, $job = '') {
	global $table;
	if($userid > 0) {
		if($job == 'follows') return numtoread(DB::count(DT_PRE.'follow', 'userid='.$userid, 'CACHE'));
		if($job == 'fans') return numtoread(DB::count(DT_PRE.'follow', 'fuserid='.$userid, 'CACHE'));
		return numtoread(DB::count($table, 'status=3 AND userid='.$userid, 'CACHE'));
	}
	return 0;
}

function my_fuid($type = 0) {
	global $_userid;
	$uids = '';
	if($_userid > 0) {
		$table = $type == 3 ? DT_PRE.'friend' : DT_PRE.'follow';
		$result = DB::query("SELECT fuserid FROM {$table} WHERE userid=$_userid ORDER BY posttime DESC LIMIT 50", 'CACHE');
		while($r = DB::fetch_array($result)) {
			$uids .= ','.$r['fuserid'];
		}
	}
	return $uids ? substr($uids, 1) : '0';
}

function get_quote($itemid) {	
	global $table, $DT_PC;
	$r = DB::get_one("SELECT * FROM {$table} WHERE itemid=$itemid", 'CACHE');
	if($r) {
		if($r['status'] != 3) return array();
		$r['pics'] = get_thumbs($r);
		if(strpos($r['introduce'], ')') !== false) $r['introduce'] = parse_face($r['introduce']);
		if(!$DT_PC) $r['introduce'] = parse_mob($r['introduce']);
		return $r;
	}
	return array();
}

function parse_mob($content) {
	global $MOD;
	$content = str_replace($MOD['linkurl'], $MOD['mobile'], $content);
	$content = str_replace('target="_blank">@', 'rel="external">@', $content);
	$content = str_replace(' target="_blank"', '', $content);
	return $content;
}
?>