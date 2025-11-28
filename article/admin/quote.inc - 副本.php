<?php
defined('DT_ADMIN') or exit('Access Denied');

$table = $DT_PRE.'article_quote_21';
$menus = array(
    array('报价列表', '?moduleid='.$moduleid.'&file=quote'),
    array('待审核', '?moduleid='.$moduleid.'&file=quote&action=check'),
    array('已审核', '?moduleid='.$moduleid.'&file=quote&action=approved'),
    array('已拒绝', '?moduleid='.$moduleid.'&file=quote&action=rejected'),
    array('数据统计', '?moduleid='.$moduleid.'&file=quote&action=stats'),
    array('导出数据', '?moduleid='.$moduleid.'&file=quote&action=export'),
);

// 搜索条件
$sfields = array('模糊', '报价单号', '姓名', '邮箱', '手机', '公司ID', '设备名称');
$dfields = array('keyword', 'itemid', 'last_name', 'email', 'mobile', 'company_id', 'devices');
isset($fields) && isset($dfields[$fields]) or $fields = 0;

$sorder = array('添加时间降序', '添加时间升序', '审核时间降序', '审核时间升序');
$dorder = array('addtime DESC', 'addtime ASC', 'edittime DESC', 'edittime ASC');
isset($order) && isset($dorder[$order]) or $order = 0;

// 时间筛选
$datetype = isset($datetype) ? $datetype : 'addtime';
$fromdate = isset($fromdate) ? $fromdate : '';
$todate = isset($todate) ? $todate : '';
$fromtime = $fromdate ? datetotime($fromdate) : 0;
$totime = $todate ? datetotime($todate) : 0;

// 状态筛选
$status = isset($status) ? intval($status) : '';
$verify_status = isset($verify_status) ? intval($verify_status) : '';

$condition = '';
if($keyword) {
    if($fields == 0) {
        $condition .= " AND (last_name LIKE '%$keyword%' OR email LIKE '%$keyword%' OR mobile LIKE '%$keyword%')";
    } else if($fields == 6) {
        // 设备名称搜索
        $condition .= " AND devices LIKE '%\"name\":\"%$keyword%\"%'";
    } else {
        $condition .= match_kw($dfields[$fields], $keyword);
    }
}

if($fromtime) $condition .= " AND $datetype>=$fromtime";
if($totime) $condition .= " AND $datetype<=$totime";
if($status !== '') $condition .= " AND status=$status";
if($verify_status !== '') $condition .= " AND verify_status=$verify_status";

// 在文件顶部添加统计函数
function get_quote_stats($condition = '') {
    global $db, $table, $DT_PRE;
    
    $stats = array();
    
    // 基础统计
    $stats['total_count'] = $db->count($table, "1 $condition");
    $stats['pending_count'] = $db->count($table, "status=0 $condition");
    $stats['approved_count'] = $db->count($table, "status=1 $condition");
    $stats['rejected_count'] = $db->count($table, "status=2 $condition");
    $stats['verified_count'] = $db->count($table, "verify_status=1 $condition");
    
    // 最近30天趋势
    $stats['trend_data'] = array();
    for($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $start_time = strtotime($date . ' 00:00:00');
        $end_time = strtotime($date . ' 23:59:59');
        $count = $db->count($table, "addtime BETWEEN $start_time AND $end_time $condition");
        $stats['trend_data'][] = array(
            'date' => $date,
            'count' => $count
        );
    }
    
    // 设备类型统计
    $stats['device_stats'] = array();
    $device_counts = array();
    $result = $db->query("SELECT devices FROM {$table} WHERE 1 $condition");
    while($r = $db->fetch_array($result)) {
        $devices_data = json_decode($r['devices'], true);
        foreach($devices_data as $catid => $device) {
            $device_name = $device['name'];
            if(!isset($device_counts[$device_name])) {
                $device_counts[$device_name] = array(
                    'count' => 0,
                    'faults' => array()
                );
            }
            $device_counts[$device_name]['count']++;
            
            // 统计故障类型
            foreach($device['faults'] as $fault_id) {
                if(!isset($device_counts[$device_name]['faults'][$fault_id])) {
                    $device_counts[$device_name]['faults'][$fault_id] = 0;
                }
                $device_counts[$device_name]['faults'][$fault_id]++;
            }
        }
    }
    
    // 处理设备统计结果
    foreach($device_counts as $device_name => $data) {
        // 计算占比
        $percent = $stats['total_count'] > 0 ? round(($data['count'] / $stats['total_count']) * 100, 2) : 0;
        
        // 获取主要故障
        arsort($data['faults']);
        $top_faults = array_slice($data['faults'], 0, 3, true);
        $faults_text = '';
        foreach($top_faults as $fault_id => $count) {
            $faults_text .= "故障{$fault_id}({$count}) ";
        }
        
        $stats['device_stats'][] = array(
            'name' => $device_name,
            'count' => $data['count'],
            'percent' => $percent,
            'top_faults' => trim($faults_text)
        );
    }
    
    // 按数量排序
    usort($stats['device_stats'], function($a, $b) {
        return $b['count'] - $a['count'];
    });
    
    // 地区统计
    $stats['area_stats'] = array();
    $area_counts = array();
    $result = $db->query("SELECT q.company_id, c.areaid, a.areaname, a.parentid 
                         FROM {$table} q 
                         LEFT JOIN {$DT_PRE}company c ON q.company_id=c.itemid 
                         LEFT JOIN {$DT_PRE}area a ON c.areaid=a.areaid 
                         WHERE 1 $condition AND c.areaid>0");
    
    while($r = $db->fetch_array($result)) {
        // 获取省份
        $province_id = $r['parentid'];
        $province_name = '';
        if($province_id) {
            $province = $db->get_one("SELECT areaname FROM {$DT_PRE}area WHERE areaid=$province_id");
            $province_name = $province ? $province['areaname'] : '未知';
        } else {
            $province_name = '未知';
        }
        
        if(!isset($area_counts[$province_name])) {
            $area_counts[$province_name] = array(
                'count' => 0,
                'cities' => array()
            );
        }
        $area_counts[$province_name]['count']++;
        
        // 统计城市
        $city_name = $r['areaname'];
        if(!isset($area_counts[$province_name]['cities'][$city_name])) {
            $area_counts[$province_name]['cities'][$city_name] = 0;
        }
        $area_counts[$province_name]['cities'][$city_name]++;
    }
    
    // 处理地区统计结果
    foreach($area_counts as $province_name => $data) {
        $percent = $stats['total_count'] > 0 ? round(($data['count'] / $stats['total_count']) * 100, 2) : 0;
        
        // 获取热门城市
        arsort($data['cities']);
        $top_cities = array_slice($data['cities'], 0, 3, true);
        $cities_text = '';
        foreach($top_cities as $city_name => $count) {
            $cities_text .= "{$city_name}({$count}) ";
        }
        
        $stats['area_stats'][] = array(
            'province' => $province_name,
            'count' => $data['count'],
            'percent' => $percent,
            'top_cities' => trim($cities_text)
        );
    }
    
    // 按数量排序
    usort($stats['area_stats'], function($a, $b) {
        return $b['count'] - $a['count'];
    });
    
    return $stats;
}

// 操作处理
switch($action) {
    case 'check':
        $condition .= " AND status=0";
        $menuid = 2;
        break;
    case 'approved':
        $condition .= " AND status=1";
        $menuid = 3;
        break;
    case 'rejected':
        $condition .= " AND status=2";
        $menuid = 4;
        break;
    case 'stats':
              // 基础统计
            $total_count = $db->count($table, "1");
            $pending_count = $db->count($table, "status=0");
            $approved_count = $db->count($table, "status=1");
            $rejected_count = $db->count($table, "status=2");
            $verified_count = $db->count($table, "verify_status=1");
            
            // 最近30天趋势
            $trend_data = array('dates' => array(), 'counts' => array());
            for($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $start_time = strtotime($date . ' 00:00:00');
                $end_time = strtotime($date . ' 23:59:59');
                $count = $db->count($table, "addtime BETWEEN $start_time AND $end_time");
                $trend_data['dates'][] = $date;
                $trend_data['counts'][] = $count;
            }
            
            // 设备类型分布
            $device_data = array();
            $result = $db->query("SELECT devices FROM {$table} WHERE devices != ''");
            while($r = $db->fetch_array($result)) {
                $devices = json_decode($r['devices'], true);
                foreach($devices as $device) {
                    $device_name = $device['name'];
                    if(isset($device_data[$device_name])) {
                        $device_data[$device_name]++;
                    } else {
                        $device_data[$device_name] = 1;
                    }
                }
            }
            
            // 转换为ECharts格式
            $device_chart_data = array();
            foreach($device_data as $name => $count) {
                $device_chart_data[] = array('value' => $count, 'name' => $name);
            }
            

        $stats = get_quote_stats($condition);
        extract($stats);
        $menuid = 5;
        include tpl('quote_stats', $module);
        exit;
    case 'export':
        export_quotes($condition);
        exit;
    case 'view':
        $itemid or msg('请选择报价单');
        $r = $db->get_one("SELECT * FROM {$table} WHERE itemid=$itemid");
        if(!$r) msg('报价单不存在');
        
        // 解析设备数据
        $r['devices_data'] = json_decode($r['devices'], true);
        $r['images_list'] = $r['images'] ? explode(',', $r['images']) : array();
        
        // 获取公司信息
        if($r['company_id']) {
            $company = $db->get_one("SELECT company, telephone, address FROM {$DT_PRE}company WHERE itemid=".$r['company_id']);
            $r['company_info'] = $company;
        }
        
        // 获取会员信息
        if($r['userid']) {
            $member = $db->get_one("SELECT username, regtime FROM {$DT_PRE}member WHERE userid=".$r['userid']);
            $r['member_info'] = $member;
        }
        
        extract($r);
        include tpl('quote_view', $module);
        exit;
    case 'approve':
        $itemid or msg('请选择报价单');
        if(is_array($itemid)) {
            $itemids = implode(',', $itemid);
        } else {
            $itemids = intval($itemid);
        }
        $db->query("UPDATE {$table} SET status=1, edittime=".DT_TIME." WHERE itemid IN ($itemids)");
        dmsg('审核通过成功', $forward);
        break;
    case 'reject':
        $itemid or msg('请选择报价单');
        if(is_array($itemid)) {
            $itemids = implode(',', $itemid);
        } else {
            $itemids = intval($itemid);
        }
        $db->query("UPDATE {$table} SET status=2, edittime=".DT_TIME." WHERE itemid IN ($itemids)");
        dmsg('拒绝成功', $forward);
        break;
    case 'delete':
        $itemid or msg('请选择报价单');
        if(is_array($itemid)) {
            $itemids = implode(',', $itemid);
        } else {
            $itemids = intval($itemid);
        }
        $db->query("DELETE FROM {$table} WHERE itemid IN ($itemids)");
        dmsg('删除成功', $forward);
        break;
    case 'set_amount':
        $itemid or msg('请选择报价单');
        $quote_amount = isset($quote_amount) ? floatval($quote_amount) : 0;
        $discount_amount = isset($discount_amount) ? floatval($discount_amount) : 0;
        
        $db->query("UPDATE {$table} SET quote_amount=$quote_amount, discount_amount=$discount_amount, edittime=".DT_TIME." WHERE itemid=$itemid");
        dmsg('金额设置成功', $forward);
        break;
    default:
        $menuid = 1;
        break;
}

// 获取报价列表
function get_quote_list($condition, $order = 'addtime DESC') {
    global $db, $table, $pages, $page, $pagesize, $offset, $sum;
    
    if($page > 1 && $sum) {
        $items = $sum;
    } else {
        $r = $db->get_one("SELECT COUNT(*) AS num FROM {$table} WHERE 1 $condition");
        $items = $r['num'];
    }
    
    $pages = pages($items, $page, $pagesize);
    if($items < 1) return array();
    
    $lists = array();
    $result = $db->query("SELECT * FROM {$table} WHERE 1 $condition ORDER BY $order LIMIT $offset,$pagesize");
    
    while($r = $db->fetch_array($result)) {
        // 解析设备数据
        $devices_data = json_decode($r['devices'], true);
        $device_names = array();
        foreach($devices_data as $device) {
            $device_names[] = $device['name'];
        }
        $r['device_names'] = implode(' + ', $device_names);
        
        // 状态文本
        $r['status_text'] = $r['status'] == 0 ? '待审核' : ($r['status'] == 1 ? '已审核' : '已拒绝');
        $r['status_color'] = $r['status'] == 0 ? 'orange' : ($r['status'] == 1 ? 'green' : 'red');
        
        // 验证状态
        $r['verify_text'] = $r['verify_status'] ? '已验证' : '未验证';
        $r['verify_color'] = $r['verify_status'] ? 'green' : 'gray';
        
        // 时间格式化
        $r['adddate'] = timetodate($r['addtime'], 6);
        $r['editdate'] = $r['edittime'] ? timetodate($r['edittime'], 6) : '-';
        
        $lists[] = $r;
    }
    
    return $lists;
}

$lists = get_quote_list($condition, $dorder[$order]);

// 搜索表单元素
$fields_select = dselect($sfields, 'fields', '', $fields);
$status_select = dselect(array('所有状态', '待审核', '已审核', '已拒绝'), 'status', '', $status);
$verify_select = dselect(array('所有验证', '已验证', '未验证'), 'verify_status', '', $verify_status);
$datetype_select = dselect(array('添加时间', '审核时间'), 'datetype', '', $datetype);
$order_select = dselect($sorder, 'order', '', $order);

include tpl('quote', $module);

// 导出功能
function export_quotes($condition) {
    global $db, $table, $DT_PRE;
    
    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=quotes_export_".date('YmdHis').".xls");
    
    echo "报价单号\t设备信息\t颜色\t故障说明\t图片数量\t公司ID\t姓\t名\t邮箱\t手机\t优惠码\t报价金额\t优惠金额\t验证状态\t审核状态\t提交时间\t审核时间\tIP地址\t会员ID\n";
    
    $result = $db->query("SELECT * FROM {$table} WHERE 1 $condition ORDER BY addtime DESC");
    while($r = $db->fetch_array($result)) {
        // 解析设备数据
        $devices_data = json_decode($r['devices'], true);
        $device_info = '';
        foreach($devices_data as $catid => $device) {
            $device_info .= $device['name'].'('.implode(',', $device['faults']).');';
        }
        
        // 图片数量
        $image_count = $r['images'] ? count(explode(',', $r['images'])) : 0;
        
        // 状态文本
        $status_text = $r['status'] == 0 ? '待审核' : ($r['status'] == 1 ? '已审核' : '已拒绝');
        $verify_text = $r['verify_status'] ? '已验证' : '未验证';
        
        // 时间格式化
        $adddate = timetodate($r['addtime'], 6);
        $editdate = $r['edittime'] ? timetodate($r['edittime'], 6) : '';
        
        echo "Q{$r['itemid']}\t{$device_info}\t{$r['color']}\t{$r['fault_desc']}\t{$image_count}\t{$r['company_id']}\t{$r['first_name']}\t{$r['last_name']}\t{$r['email']}\t{$r['mobile']}\t{$r['discount_code']}\t{$r['quote_amount']}\t{$r['discount_amount']}\t{$verify_text}\t{$status_text}\t{$adddate}\t{$editdate}\t{$r['ip']}\t{$r['userid']}\n";
    }
    exit;
}
?>