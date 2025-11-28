<?php
// module/article/admin/quote.inc.php
defined('IN_DESTOON') or exit('Access Denied');

// require DT_ROOT.'/module/'.$module.'/common.inc.php';
// require DT_ROOT.'/include/post.func.php';

$table          = $DT_PRE.'article_quote_'.$moduleid; // dt_article_quote_21
$table_company  = $DT_PRE.'company';
$menus = array(
    array('报价管理', '?moduleid='.$moduleid.'&file='.$file),
);

$STATUS = array(
    0 => '待审核',
    1 => '已通过',
    2 => '已拒绝',
);

$action = isset($action) ? $action : '';

// ====== 列表 ======
if($action == 'list' || $action == '') {

    $status = isset($status) ? intval($status) : -1;
    $keyword = isset($keyword) ? trim($keyword) : '';
    $fromdate = isset($fromdate) ? trim($fromdate) : '';
    $todate   = isset($todate) ? trim($todate) : '';

    $condition = '1';
    if($status >= 0) {
        $condition .= " AND q.status={$status}";
    }
    if($keyword) {
        $kw = daddslashes($keyword);
        $condition .= " AND (q.first_name LIKE '%{$kw}%' OR q.last_name LIKE '%{$kw}%' OR q.email LIKE '%{$kw}%' OR q.mobile LIKE '%{$kw}%')";
    }
    if($fromdate) {
        $f = strtotime($fromdate.' 00:00:00');
        $condition .= " AND q.addtime>={$f}";
    }
    if($todate) {
        $t = strtotime($todate.' 23:59:59');
        $condition .= " AND q.addtime<={$t}";
    }

    $page = max(intval($page), 1);
    $pagesize = 20;
    $offset = ($page-1)*$pagesize;

    $r = $db->get_one("SELECT COUNT(*) AS num FROM {$table} q WHERE {$condition}");
    $items = $r ? $r['num'] : 0;
    $pages = pages($items, $page, $pagesize);

    $lists = array();
    $result = $db->query("SELECT q.*,c.company AS company_name 
                          FROM {$table} q 
                          LEFT JOIN {$table_company} c ON q.company_id=c.userid
                          WHERE {$condition}
                          ORDER BY q.itemid DESC
                          LIMIT {$offset},{$pagesize}");
    while($r = $db->fetch_array($result)) {
        $r['addtime_str'] = timetodate($r['addtime'], 5);
        $r['status_str']  = isset($STATUS[$r['status']]) ? $STATUS[$r['status']] : '未知';
        $r['customer_name'] = $r['first_name'].$r['last_name'];
        $r['final_amount_str'] = number_format($r['final_amount'], 2);
        $r['total_fault_amount_str'] = number_format($r['total_fault_amount'], 2);
        $lists[] = $r;
    }

    include tpl('quote', $module);

// ====== 查看 + 编辑 ======
} elseif($action == 'edit') {

    $itemid = intval($itemid);
    $itemid or msg('参数错误：itemid 不能为空');

    if($submit) {
//        var_dump(  $post);
//        die();
        $post = dhtmlspecialchars($post);
        $status_new      = isset($post['status']) ? intval($post['status']) : 0;
        $final_amount    = isset($post['final_amount']) ? floatval($post['final_amount']) : 0;
        $discount_amount = isset($post['discount_amount']) ? floatval($post['discount_amount']) : 0;
        $admin_note      = isset($post['admin_note']) ? trim($post['admin_note']) : '';
//        var_dump(  $final_amount);
//        die();


        if($status_new < 0 || $status_new > 2) $status_new = 0;
        if($final_amount < 0) $final_amount = 0;
        if($discount_amount < 0) $discount_amount = 0;

        // 取旧记录
        $r = $db->get_one("SELECT * FROM {$table} WHERE itemid={$itemid}");
        $r or msg('记录不存在');

        // 如无 token 则生成一个
        $receipt_token = $r['receipt_token'];
        if(!$receipt_token) {
            $receipt_token = md5($DT_TIME.mt_rand(100000, 999999).$itemid.$r['email']);
        }

        // 更新主记录
        $db->query("UPDATE {$table} 
                    SET final_amount='".number_format($final_amount, 2, '.', '')."',
                        discount_amount='".number_format($discount_amount, 2, '.', '')."',
                        admin_note='{$admin_note}',
                        status={$status_new},
                        receipt_token='{$receipt_token}',
                        edittime={$DT_TIME}
                    WHERE itemid={$itemid}");

        // 是否需要发邮件通知：
        // 条件：本次设为“通过”(status=1) 且 之前未发送过通知 且 有邮箱
        $need_send = 0;
        if($status_new == 1 && !$r['notify_sent'] && $r['email']) {
            $need_send = 1;
        }

        if($need_send) {
            // 生成回执链接
            if(empty($MOD)) $MOD = cache_read('module-'.$moduleid.'.php');
            $receipt_url = $MOD['linkurl'].'quote_view.php?itemid='.$itemid.'&token='.$receipt_token;

            // 预约时间
            $appoint_str = $r['appoint_time'] ? timetodate($r['appoint_time'], 5) : '未填写';

            // 邮件标题
            $subject = '报价回执通知';

            // 管理员备注：有才显示，没有就整段不显示
            if($admin_note) {
                // 有备注：带标题
                $note_html = '管理员备注：<br/>'.nl2br($admin_note).'<br/><br/>';
            } else {
                // 没备注：不输出任何东西
                $note_html = '';
            }

// 邮件内容
            $content = '您好，'.$r['first_name'].$r['last_name'].'：<br/><br/>'
                . '您的设备报价已审核通过，最终报价金额为：'
                . number_format($final_amount, 2).' 円。<br/>'
                . '预约到店时间：'.$appoint_str.'<br/><br/>'
                . $note_html
                . '请点击下面的链接查看详细报价回执：<br/>'
                . '<a href="'.$receipt_url.'" target="_blank">'.$receipt_url.'</a><br/>';


            if(function_exists('send_mail')) {
                if(@send_mail($r['email'], $subject, $content)) {
                    // 标记已发送，避免重复发
                    $db->query("UPDATE {$table} SET notify_sent=1 WHERE itemid={$itemid}");
                }
            }
        }

        dmsg('保存成功', '?moduleid='.$moduleid.'&file='.$file);

    } else {
        // 展示编辑页面
        $r = $db->get_one("SELECT q.*,c.company AS company_name,c.areaid 
                           FROM {$table} q 
                           LEFT JOIN {$table_company} c ON q.company_id=c.userid
                           WHERE q.itemid={$itemid}");
        $r or msg('记录不存在');

        // 设备 JSON 解析
        $devices = array();
        if($r['devices']) {
            $tmp = json_decode($r['devices'], true);
            if($tmp && is_array($tmp)) $devices = $tmp;
        }

        // 预约时间
        $r['appoint_time_str'] = $r['appoint_time'] ? timetodate($r['appoint_time'], 5) : '未填写';

        // 图片
        $images = array();
        if($r['images']) {
            $paths = explode(',', $r['images']);
            foreach($paths as $p) {
                $p = trim($p);
                if(!$p) continue;
                // 还原为完整URL
                $images[] = linkurl($p, 1);
            }
        }

        // 显示金额
        $r['total_fault_amount'] = number_format($r['total_fault_amount'], 2);
        $r['final_amount']       = number_format($r['final_amount'], 2);
        $r['discount_amount']    = number_format($r['discount_amount'], 2);

        $item = $r; // 模板里用 $item

        include tpl('quote_edit', $module);
    }

} else {
    msg('未知操作');
}
