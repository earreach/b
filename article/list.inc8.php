<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
if($DT['max_list'] > 0 && $page > $DT['max_list']) $page = 1;
if($DT_PC) {




//    print_r($CAT);die();
//    判断是否存在真正的cat
	if(!$CAT || $CAT['moduleid'] != $moduleid) include load('404.inc');
//    判断静态list
	if($MOD['list_html']) {
		$html_file = listurl($CAT, $page);
		if(is_file(DT_ROOT.'/'.$MOD['moduledir'].'/'.$html_file)) d301($MOD['linkurl'].$html_file);
	}
//    检查分类
	if(!check_group($_groupid, $MOD['group_list']) || !check_group($_groupid, $CAT['group_list'])) include load('403.inc');

//    获取content所在的表名 //所有分类只有一个表不需要
//    $content_table = content_table($moduleid, $itemid, $MOD['split'], $table_data);
//    获取content
    $t = $db->get_one("SELECT content FROM ".$DT_PRE."category_content WHERE catid=$catid");
    $user_status=3;
    $content = $t['content'];
//    自带的提取简介把格式也去掉了
//    $content1 =get_intro($t['content'],1000);
//var_dump($content);die();
$matchs = [];
$images=[];
if (preg_match_all('/<img.*?src="(.*?)"/', $content, $matchs,0)){
//    var_dump(($matchs[1]==[]));die();
    if(!$matchs[1]){
        $images=[];
//        var_dump(8888888888);die();
    }
    $images = $matchs[1];
}

//var_dump($images);die();
    $content= preg_replace('/<img.*?src="(.*?)"\/>/', '', $content);
//    var_dump($maincat);die();
	$FD = cache_read('fields-'.substr($table, strlen($DT_PRE)).'.php');
	if($FD) require DT_ROOT.'/include/fields.func.php';
	$CP = $MOD['cat_property'] && $catid && $CAT['property'];
	if($CP) require DT_ROOT.'/include/property.func.php';
	$PP = $CP ? property_search_arr($catid) : array();
	unset($CAT['moduleid']);
	extract($CAT);
//    获取父级栏目

    $cache_key = 'module21_category_tree';
    $aa = cache_read($cache_key);
// 如果缓存不存在或读取失败，从数据库获取
    if ($aa === []) {
        // 记录缓存未命中
        log_write("分类数据缓存未命中，从数据库加载", 'info');

        // 原来的数据库查询代码
        $ab = get_cattoarr(8, $moduleid);
        $cd = get_cattoarr(20, $moduleid);
        $ef = get_cattoarr(22, $moduleid);
        $aa = array_merge($ab, $cd, $ef);

        // 写入缓存，缓存30天
        $cache_result = cache_write($cache_key, $aa, 30 * 24 * 3600);
        if (!$cache_result) {
            log_write("分类数据缓存写入失败", 'error');
        }
    } else {
        // 缓存命中，可以记录统计信息
        // 这里可以添加命中率统计代码
    }
// 可以提取出去
//    if ($template=='list-4'){
////       获取所有数组，并且以字符串的形式展示
////        8+20
//       $ab = get_cattoarr(8,$moduleid);
//        $cd = get_cattoarr(20,$moduleid);
//        $ef = get_cattoarr(22,$moduleid);
//        $aa = array_merge ($ab,$cd,$ef);
////        		 echo  "<pre>";
////        var_dump($aa);die();
//    }



 function get_device_data() {
    $category = trim($_POST['category']);
    $model = trim($_POST['model']);
    
    if (empty($category) || empty($model)) {
        exit(json_encode(array('success' => false, 'message' => '参数不完整')));
    }
    
    // 根据类别和型号查询数据库
    // 这里需要根据您的实际表结构调整
    $result = $this->db->get_one("SELECT * FROM destoon_device_table WHERE category = '{$category}' AND model = '{$model}'");
    
    if ($result) {
        exit(json_encode(array(
            'success' => true,
            'data' => array(
                'price' => $result['price'],
                'stock' => $result['stock'],
                'description' => $result['description']
            )
        )));
    } else {
        exit(json_encode(array('success' => false, 'message' => '未找到相关数据')));
    }
}







    if($parentid){



        $f = DB::get_one("SELECT icon FROM ".DT_PRE."category WHERE catid=".$parentid);
        $icon=$icon!=''?$icon:$f['icon'];
//        var_dump($f);die();
    }
	$maincat = get_maincat($child ? $catid : $parentid, $moduleid);
//    $maincat1 = get_maincat_1($child ? $catid : $parentid, $moduleid);
//    echo "<pre>";
//    var_dump($maincat);die();
	$condition = 'status=3';
	$condition .= ($CAT['child']) ? " AND catid IN (".$CAT['arrchildid'].")" : " AND catid=$catid";
	if($cityid) {
		$areaid = $cityid;
		$ARE = get_area($areaid);
		$condition .= $ARE['child'] ? " AND areaid IN (".$ARE['arrchildid'].")" : " AND areaid=$areaid";
		$items = $db->count($table, $condition, $CFG['db_expires']*split_id($CAT['item']));
	} else {
		if($page == 1) {
			$items = $db->count($table, $condition, $CFG['db_expires']*split_id($CAT['item']));
			if($items != $CAT['item']) {
				$CAT['item'] = $items;
				$db->query("UPDATE {$DT_PRE}category SET item=$items WHERE catid=$catid");
			}
		} else {
			$items = $CAT['item'];
		}
	}
	$pagesize = $MOD['pagesize'];
	$offset = ($page-1)*$pagesize;
	$pages = listpages($CAT, $items, $page, $pagesize);
	$tags = array();
	if($items) {
		$result = $db->query("SELECT ".$MOD['fields']." FROM {$table} WHERE {$condition} ORDER BY ".$MOD['order']." LIMIT {$offset},{$pagesize}", ($CFG['db_expires'] && $page <= $DT['cache_page']) ? 'CACHE' : '', $CFG['db_expires']);
		while($r = $db->fetch_array($result)) {
			$r['adddate'] = timetodate($r['addtime'], 5);
			$r['editdate'] = timetodate($r['edittime'], 5);
			if($lazy && isset($r['thumb']) && $r['thumb']) $r['thumb'] = DT_STATIC.'image/lazy.gif" original="'.$r['thumb'];
			$r['alt'] = $r['title'];
			$r['title'] = set_style($r['title'], $r['style']);
			if(!$r['islink']) $r['linkurl'] = $MOD['linkurl'].$r['linkurl'];
			$tags[] = $r;
		}
		$db->free_result($result);
	}
	$showpage = 1;
	$datetype = 3;
	$cols = 5;
	if($EXT['mobile_enable']) $head_mobile = $MOD['mobile'].listurl($CAT, $page > 1 ? $page : 0);
} else {



	if(!$CAT || $CAT['moduleid'] != $moduleid) message($L['msg_not_cate']);
	if(!check_group($_groupid, $MOD['group_list']) || !check_group($_groupid, $CAT['group_list'])) message($L['msg_no_right']);
	$condition = "status=3";
	$condition .= $CAT['child'] ? " AND catid IN (".$CAT['arrchildid'].")" : " AND catid=$catid";
	if($cityid) {
		$areaid = $cityid;
		$ARE = get_area($areaid);
		$condition .= $ARE['child'] ? " AND areaid IN (".$ARE['arrchildid'].")" : " AND areaid=$areaid";
		$items = $db->count($table, $condition, $CFG['db_expires']*split_id($CAT['item']));
	} else {
		$items = $CAT['item'];
	}
	$pages = mobile_pages($items, $page, $pagesize, $MOD['mobile'].listurl($CAT, '{destoon_page}'));
	$tags = array();
	if($items) {
		$result = $db->query("SELECT ".$MOD['fields']." FROM {$table} WHERE {$condition} ORDER BY ".$MOD['order']." LIMIT {$offset},{$pagesize}", ($CFG['db_expires'] && $page <= $DT['cache_page']) ? 'CACHE' : '', $CFG['db_expires']);
		while($r = $db->fetch_array($result)) {
			$r['title'] = set_style($r['title'], $r['style']);
			if(!$r['islink']) $r['linkurl'] = $MOD['mobile'].$r['linkurl'];
			$tags[] = $r;
		}
		$db->free_result($result);
		$js_load = $MOD['mobile'].'search'.DT_EXT.'?job=ajax&catid='.$catid;
	}
	if($CAT['parentid']) $PCAT = get_cat($CAT['parentid']);
	$head_title = $head_name = $CAT['catname'];


}

$seo_file = 'list';
include DT_ROOT.'/include/seo.inc.php';
$template = $CAT['template'] ? $CAT['template'] : ($MOD['template_list'] ? $MOD['template_list'] : 'list');
include template($template, $module);
?>