<?php
/*
	DESTOON Copyright (C)2008-2099 www.destoon.com
	This is NOT a freeware,Use is subject to license.txt
*/
defined('DT_ADMIN') or exit('Access Denied');
$menus = array (
    array('属性添加', '?moduleid='.$moduleid.'&file='.$file.'&action=add&parentid='.$parentid),
    array('属性管理', '?moduleid='.$moduleid.'&file='.$file),
    array('更新缓存', '?moduleid='.$moduleid.'&file='.$file.'&action=cache'),
// 	array('导入属性', '?moduleid='.$moduleid.'&file='.$file.'&action=import','onclick="return confirm(\'确定导入属性数据吗？ 当前数据将被覆盖 \');"'),
    array('批量添加', '?moduleid='.$moduleid.'&file='.$file.'&action=addd'),
);
// echo $file;die();
// if($_POST) die(var_dump($_POST));
// cache_shuxing();
$SHUXING = cache_read('shuxing.php');
// die(var_dump($shuxingid));
$shuxingid = isset($shuxingid) ? intval($shuxingid) : 0;
$table = $DT_PRE.$module.'_shuxing_'.$moduleid;

$do = new shuxing($shuxingid);
// die(var_dump($table));
$type = $do->get_type();
if($type) $menus[] = array('导入省市县', '?file='.$file.'&action=import&job=shuxing','onclick="return confirm(\'确定导入中国省市县数据吗？ 当前数据将被覆盖 \');"');
$parentid = isset($parentid) ? intval($parentid) : 0;

// 获取跳转的前一页
$this_forward = '?moduleid='.$moduleid.'&file='.$file.'&parentid='.$parentid;
// die(var_dump($this_forward));
switch($action) {
    case 'add':
        if($submit) {
            if(!$shuxing['shuxingname']) msg('属性名不能为空');
            $shuxing['shuxingname'] = trim($shuxing['shuxingname']);
// 			如果来源数据没有回车就直接插入数据库
            if(strpos($shuxing['shuxingname'], "\n") === false) {
                $do->add($shuxing);
            } else {
                //   否则数据是数组
                $shuxingnames = explode("\n", $shuxing['shuxingname']);
                foreach($shuxingnames as $shuxingname) {
                    $shuxingname = trim($shuxingname);
                    if(!$shuxingname) continue;
                    $shuxing['shuxingname'] = $shuxingname;
                    $do->add($shuxing);
                }
            }
            $do->repair();
            dmsg('添加成功', $this_forward);
        } else {
            cache_shuxing();

            include tpl('shuxing_add',$module);
// 			var_dump($this_forward);
        }
        break;
    case 'addd':
        if($submit){
            //require DT_ROOT.'/upload.php';  把这个搞懂 怎么用


            //$uploaddir = 'file/upload/'.timetodate($DT_TIME, $DT['uploaddir']).'/';
            //is_dir(DT_ROOT.'/'.$uploaddir) or dir_create(DT_ROOT.'/'.$uploaddir);

            //  die(var_dump($uploaddir));
            // $do = new upload($_FILES, $uploaddir);

            //  var_dump($do->save()) ;
// 	die(444);
            //(var_dump($_FILES['file1']['tmp_name']));

            /*
            die(var_dump($_FILES));
            array(2) { ["fu"]=> array(5) { ["name"]=> string(0) "" ["type"]=> string(0) "" ["tmp_name"]=> string(0) "" ["error"]=> int(4) ["size"]=> int(0) } ["zi"]=> array(5) { ["name"]=> string(0) "" ["type"]=> string(0) "" ["tmp_name"]=> string(0) "" ["error"]=> int(4) ["size"]=> int(0) } }
            */
            extract($_FILES);
            //file（）函数作用是返回一维数组
            $fu = file($fu['tmp_name']);
            $zi = file($zi['tmp_name']);


            /*
            for($i=0;$i<count($cbody);$i++){ //count函数就是获取数组的长度的，长度为3 因为一行被识别为一个数组 有三行
                echo $cbody[$i];echo "<br/>"; //最后是循环输出每个数组，在每个数组输出完毕后 ，输出一个换行，这样就可以达到换行效果
            }
            */
//            $array = array("apple", "", 0, 2, null, -5, "0", "orange", 10, false);
//            var_dump($array);
//            echo "<br>";
//
//            /* 过滤数组 */
//            $result = array_filter($array);
//            var_dump($result);
//
//            die();

            if(!$fu || !$zi) msg('不能为空');
            // if(!$zi) msg('不能为空');
            // die(var_dump($_POST));
            // 手动赋值父级属性  2043-2076
            // $parentids=[2045,2046];
//            var_dump($fu);
//            var_dump($zi);
//            die();
//            $fu=array_filter ($fu, function($value) {
//                return $value !== '';
//            });
//            $zi = array_filter ($zi, function($value) {
//                return $value !== '';
//            });

            foreach($fu as $parentid){
//                var_dump($fu);
//                var_dump($parentid=='');
//                die();
//                    if(!$parentid) continue;
//                $shuxing['parentid']=$parentid;
                $parentid = intval($parentid);
//                var_dump(!$parentid);
//                die();
                if (!$parentid) continue;
                foreach($zi as $shuxingname) {
                    $shuxing['parentid']=$parentid;
                    $shuxingname = trim($shuxingname);
                    if(!$shuxingname) continue;
                    $shuxing['shuxingname'] = $shuxingname;
//                    var_dump($parentid,$moduleid,$action);
//                    die();
                    $do->add($shuxing);
                }

            }



            $do->repair();
            fclose($fu);

            fclose($zi);
            dmsg('添加成功', $this_forward);
        }else{
            cache_shuxing();

            include tpl('piliang_add',$module);

        }
        break;
    case 'import':
        if($type) {
            $name = $job == 'shuxing' ? 'area2021' : 'city2021';
        } else {
            $name = 'shuxing';
        }
        $file = DT_ROOT.'/file/backup/'.$name.'.sql';
        is_file($file) or msg('数据文件不存在，请上传程序包内 file/backup/'.$name.'.sql 文件至 file/backup 目录');
        require DT_ROOT.'/include/sql.func.php';
        sql_execute(file_get($file));
        cache_area();
        dmsg('导入成功', $this_forward);
        break;
    case 'cache':
        $do->repair();
        dmsg('更新成功', $forward);
        break;
    case 'delete':
        if($shuxingid) $shuxingids = $shuxingid;
        $shuxingids or msg();
        $do->delete($shuxingids);
        dmsg('删除成功', $this_forward);
        break;
    case 'update':
        if(!$shuxing || !is_array($shuxing)) msg();
        $do->update($shuxing);
        dmsg('更新成功', $this_forward);
        break;
    default:
        $keyword = $_POST['kw'];
        $DSHUXING = array();
        $condition = $keyword ? "shuxingname LIKE '%$keyword%'" : "parentid=$parentid";
        $result = $db->query("SELECT * FROM {$table} WHERE {$condition} ORDER BY listorder,shuxingid");
        while($r = $db->fetch_array($result)) {
            $r['childs'] = substr_count($r['arrchildid'], ',');
            $DSHUXING[$r['shuxingid']] = $r;
        }

        include tpl('shuxing', $module);
        // die(var_dump($keyword));
        break;
}

class shuxing {

    var $shuxingid;
    var $shuxing = array();
    var $table;

    function __construct($shuxingid = 0)	{
        global $SHUXING,$module,$moduleid;
        $this->shuxingid = $shuxingid;
        $this->shuxing = $SHUXING;
        $this->table = DT_PRE.$module.'_shuxing_'.$moduleid;
        // die(var_dump($this->table ));
    }

    function shuxing($shuxingid = 0)	{
        $this->__construct($shuxingid);
    }

    function add($shuxing)	{
        if(!is_array($shuxing)) return false;
        $sql1 = $sql2 = $s = '';
        foreach($shuxing as $key=>$value) {
            $sql1 .= $s.$key;
            $sql2 .= $s."'".$value."'";
            $s = ',';
        }
        DB::query("INSERT INTO {$this->table} ($sql1) VALUES($sql2)");
        $this->shuxingid = DB::insert_id();
        if($shuxing['parentid']) {
            $shuxing['shuxingid'] = $this->shuxingid;
            $this->shuxing[$this->shuxingid] = $shuxing;
            $arrparentid = $this->get_arrparentid($this->shuxingid);
        } else {
            $arrparentid = 0;
        }
        DB::query("UPDATE {$this->table} SET arrchildid='$this->shuxingid',listorder=0,arrparentid='$arrparentid' WHERE shuxingid=$this->shuxingid");
        return true;
    }

    function delete($shuxingids) {
        if(is_array($shuxingids)) {
            foreach($shuxingids as $shuxingid) {
                if(isset($this->shuxing[$shuxingid])) {
                    $arrchildid = $this->shuxing[$shuxingid]['arrchildid'];
                    DB::query("DELETE FROM {$this->table} WHERE shuxingid IN ($arrchildid)");
                }
            }
        } else {
            $shuxingid = $shuxingids;
            if(isset($this->shuxing[$shuxingid])) {
                $arrchildid = $this->shuxing[$shuxingid]['arrchildid'];
                DB::query("DELETE FROM {$this->table} WHERE shuxingid IN ($arrchildid)");
            }
        }
        $this->repair();
        return true;
    }

    function update($shuxing) {
        if(!is_array($shuxing)) return false;
        foreach($shuxing as $k=>$v) {
            if(!$v['shuxingname']) continue;
            $v['parentid'] = intval($v['parentid']);
            if($k == $v['parentid']) continue;
            if($v['parentid'] > 0 && !isset($this->shuxing[$v['parentid']])) continue;
            $v['listorder'] = intval($v['listorder']);
            DB::query("UPDATE {$this->table} SET shuxingname='$v[shuxingname]',parentid='$v[parentid]',listorder='$v[listorder]' WHERE shuxingid=$k");
        }
        cache_shuxing();
        return true;
    }

    function repair() {
        $query = DB::query("SELECT * FROM {$this->table} ORDER BY listorder,shuxingid");
        $SHUXING = array();
        while($r = DB::fetch_array($query)) {
            $SHUXING[$r['shuxingid']] = $r;
        }
        $childs = array();
        foreach($SHUXING as $shuxingid => $shuxing) {
            $arrparentid = $this->get_arrparentid($shuxingid);
            DB::query("UPDATE {$this->table} SET arrparentid='$arrparentid' WHERE shuxingid=$shuxingid");
            if($arrparentid) {
                $arr = explode(',', $arrparentid);
                foreach($arr as $a) {
                    if($a == 0) continue;
                    isset($childs[$a]) or $childs[$a] = '';
                    $childs[$a] .= ','.$shuxingid;
                }
            }
        }
        foreach($SHUXING as $shuxingid => $shuxing) {
            if(isset($childs[$shuxingid])) {
                $arrchildid = $shuxingid.$childs[$shuxingid];
                DB::query("UPDATE {$this->table} SET arrchildid='$arrchildid',child=1 WHERE shuxingid='$shuxingid'");
            } else {
                DB::query("UPDATE {$this->table} SET arrchildid='$shuxingid',child=0 WHERE shuxingid='$shuxingid'");
            }
        }
        cache_shuxing();
        return true;
    }

    function get_arrparentid($shuxingid) {
        $SX = get_shuxing($shuxingid);
        if($SX['parentid'] && $SX['parentid'] != $shuxingid) {
            $parents = array();
            $cid = $shuxingid;
            $i = 1;
            while($i++ < 10) {
                $SX = get_shuxing($cid);
                if($SX['parentid']) {
                    $parents[] = $cid = $SX['parentid'];
                } else {
                    break;
                }
            }
            $parents[] = 0;
            return implode(',', array_reverse($parents));
        } else {
            return '0';
        }
    }

    function get_type() {
        $t = DB::get_one("SELECT * FROM {$this->table} ORDER BY shuxingid");
        if($t) return $t['shuxingid'] < 110000 ? false : true;
        return true;
    }
}
?>