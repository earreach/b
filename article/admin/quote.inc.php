<?php
// module/article/admin/quote.inc.php
defined('IN_DESTOON') or exit('Access Denied');

// require DT_ROOT.'/module/'.$module.'/common.inc.php';
// require DT_ROOT.'/include/post.func.php';

$table          = $DT_PRE.'article_quote_'.$moduleid; // dt_article_quote_21
$table_company  = $DT_PRE.'company';
$menus = array(
    array('报价管理', '?moduleid='.$moduleid.'&file='.$file),
    array('报价统计', '?moduleid='.$moduleid.'&file='.$file.'&action=stats'),
);

$STATUS = array(
    0 => '待审核',
    1 => '已通过',
    2 => '已拒绝',
);
// 新增：维修状态文字
$REPAIR_STATUS = array(
    0 => '未设置',
    1 => '已预约',
    2 => '维修中',
    3 => '已完修',
    4 => '已取消',
);
$action = isset($action) ? $action : '';

// ====== 列表 ======
// ====== 列表 ======
if($action == 'list' || $action == '') {

    $menuid = 0; // 高亮“报价管理”

    // 筛选条件：状态 / 关键词 / 提交时间 / 门店 / 预约日期
    $status      = isset($status) ? intval($status) : -1;
    $keyword     = isset($keyword) ? trim($keyword) : '';
    $fromdate    = isset($fromdate) ? trim($fromdate) : '';
    $todate      = isset($todate) ? trim($todate) : '';
    $company_id  = isset($company_id) ? intval($company_id) : 0;
    $user_confirm_status = isset($user_confirm_status) ? intval($user_confirm_status) : -1;
    $appointdate = isset($appointdate) ? trim($appointdate) : '';
    $repair_status = isset($repair_status) ? intval($repair_status) : -1;
    $condition = '1';
    if($status >= 0) {
        $condition .= " AND q.status={$status}";
    }
    if($keyword) {
        $kw = daddslashes($keyword);
        $condition .= " AND (q.first_name LIKE '%{$kw}%' OR q.last_name LIKE '%{$kw}%' OR q.email LIKE '%{$kw}%' OR q.mobile LIKE '%{$kw}%')";
    }
    if($user_confirm_status > -1) $condition .= " AND user_confirm_status={$user_confirm_status}";
    if($repair_status >= 0) $condition .= " AND q.repair_status={$repair_status}";

    if($fromdate) {
        $f = strtotime($fromdate.' 00:00:00');
        $condition .= " AND q.addtime>={$f}";
    }
    if($todate) {
        $t = strtotime($todate.' 23:59:59');
        $condition .= " AND q.addtime<={$t}";
    }
    if($company_id) {
        $condition .= " AND q.company_id={$company_id}";
    }
    if($appointdate) {
        $af = strtotime($appointdate.' 00:00:00');
        $at = strtotime($appointdate.' 23:59:59');
        $condition .= " AND q.appoint_time>={$af} AND q.appoint_time<={$at}";
    }

    // 公司列表供下拉筛选
    $companys = array();
    $result_c = $db->query("SELECT userid,company FROM {$table_company} WHERE company<>'' ORDER BY company ASC");
    while($r_c = $db->fetch_array($result_c)) {
        $companys[] = $r_c;
    }

    $page     = max(intval($page), 1);
    $pagesize = 20;
    $offset   = ($page-1)*$pagesize;

    $r = $db->get_one("SELECT COUNT(*) AS num FROM {$table} q WHERE {$condition}");
    $items = $r ? $r['num'] : 0;
    $pages = pages($items, $page, $pagesize);

    $lists = array();
    if($items) {
        $result = $db->query("SELECT q.*,c.company AS company_name 
                              FROM {$table} q 
                              LEFT JOIN {$table_company} c ON q.company_id=c.userid
                              WHERE {$condition}
                              ORDER BY q.itemid DESC
                              LIMIT {$offset},{$pagesize}");
        while($r = $db->fetch_array($result)) {
            $r['addtime_str']   = timetodate($r['addtime'], 5);
            $r['status_str']    = isset($STATUS[$r['status']]) ? $STATUS[$r['status']] : '未知';
            $r['customer_name'] = $r['first_name'].$r['last_name'];

            // 金额类字段
            $total_fault_amount = floatval($r['total_fault_amount']);
            $quote_amount       = floatval($r['quote_amount']);
            $manual_discount    = floatval($r['discount_amount']);
            $coupon_amount      = 0.00;
            if($total_fault_amount > 0 && $quote_amount >= 0 && $total_fault_amount >= $quote_amount) {
                // 优惠码抵扣 = 原价合计 - quote_amount（优惠码后金额）
                $coupon_amount = $total_fault_amount - $quote_amount;
            }
            $r['coupon_amount']           = $coupon_amount;
            $r['final_amount_str']        = number_format($r['final_amount']);
            $r['total_fault_amount_str']  = number_format($total_fault_amount);
            $r['coupon_amount_str']       = number_format($coupon_amount);
            $r['discount_amount_str']     = number_format($manual_discount);
            $r['appointtime_str']         = $r['appoint_time'] ? timetodate($r['appoint_time'], 5) : '';
            // 维修状态 & 实收金额
            $repair_status_val      = isset($r['repair_status']) ? intval($r['repair_status']) : 0;
            $r['repair_status']     = $repair_status_val;
            $r['repair_status_str'] = isset($REPAIR_STATUS[$repair_status_val]) ? $REPAIR_STATUS[$repair_status_val] : '未设置';

            $repair_amount_val      = isset($r['repair_amount']) ? floatval($r['repair_amount']) : 0;
            $r['repair_amount_str'] = number_format($repair_amount_val);


            $lists[] = $r;
        }
    }

    include tpl('quote', $module);
} elseif($action == 'stats') {

    $menuid = 1; // 高亮“报价统计”

    // 筛选条件：提交时间 / 门店 / 维修状态
    $fromdate      = isset($fromdate) ? trim($fromdate) : '';
    $todate        = isset($todate) ? trim($todate) : '';
    $company_id    = isset($company_id) ? intval($company_id) : 0;
    $repair_status = isset($repair_status) ? intval($repair_status) : -1;

    $condition = '1';
    if($fromdate) {
        $fromtime = strtotime($fromdate.' 00:00:00');
        $condition .= " AND q.addtime>={$fromtime}";
    }
    if($todate) {
        $totime = strtotime($todate.' 23:59:59');
        $condition .= " AND q.addtime<={$totime}";
    }
    if($company_id) {
        $condition .= " AND q.company_id={$company_id}";
    }
    if($repair_status >= 0) {
        $condition .= " AND q.repair_status={$repair_status}";
    }

    // 门店下拉（给模板用）
    $companys = array();
    $result_c = $db->query("SELECT userid,company FROM {$table_company} WHERE company<>'' ORDER BY company ASC");
    while($r_c = $db->fetch_array($result_c)) {
        $companys[] = $r_c;
    }

    // 汇总数据
    $stats = array();
    $total_row = array(
        'company_name'        => '合计',
        'total_quotes'        => 0,
        'passed_count'        => 0,
        'accept_count'        => 0,
        'reject_count'        => 0,
        'finished_count'      => 0,
        'total_final_amount'  => 0,
        'total_repair_amount' => 0,
    );

    $sql = "SELECT q.company_id,
                   c.company AS company_name,
                   COUNT(*) AS total_quotes,
                   SUM(CASE WHEN q.status=1 THEN 1 ELSE 0 END) AS passed_count,
                   SUM(CASE WHEN q.user_confirm_status=1 THEN 1 ELSE 0 END) AS accept_count,
                   SUM(CASE WHEN q.user_confirm_status=2 THEN 1 ELSE 0 END) AS reject_count,
                   SUM(CASE WHEN q.repair_status=3 THEN 1 ELSE 0 END) AS finished_count,
                   SUM(q.final_amount) AS total_final_amount,
                   SUM(q.repair_amount) AS total_repair_amount
            FROM {$table} q
            LEFT JOIN {$table_company} c ON q.company_id=c.userid
            WHERE {$condition}
            GROUP BY q.company_id
            ORDER BY c.company ASC";

    $result = $db->query($sql);
    while($r = $db->fetch_array($result)) {
        $r['total_quotes']        = intval($r['total_quotes']);
        $r['passed_count']        = intval($r['passed_count']);
        $r['accept_count']        = intval($r['accept_count']);
        $r['reject_count']        = intval($r['reject_count']);
        $r['finished_count']      = intval($r['finished_count']);
        $r['total_final_amount']  = floatval($r['total_final_amount']);
        $r['total_repair_amount'] = floatval($r['total_repair_amount']);

        // 累加总计
        $total_row['total_quotes']        += $r['total_quotes'];
        $total_row['passed_count']        += $r['passed_count'];
        $total_row['accept_count']        += $r['accept_count'];
        $total_row['reject_count']        += $r['reject_count'];
        $total_row['finished_count']      += $r['finished_count'];
        $total_row['total_final_amount']  += $r['total_final_amount'];
        $total_row['total_repair_amount'] += $r['total_repair_amount'];

        // 接受率 & 完修率
        if($r['passed_count'] > 0) {
            $r['accept_rate'] = round($r['accept_count'] * 100 / $r['passed_count'], 1).'%';
        } else {
            $r['accept_rate'] = '--';
        }
        if($r['total_quotes'] > 0) {
            $r['finish_rate'] = round($r['finished_count'] * 100 / $r['total_quotes'], 1).'%';
        } else {
            $r['finish_rate'] = '--';
        }

        // 金额格式化
        $r['total_final_amount_str']  = number_format($r['total_final_amount']);
        $r['total_repair_amount_str'] = number_format($r['total_repair_amount']);

        // 门店名兜底
        if(!$r['company_name']) $r['company_name'] = '未指定门店';

        $stats[] = $r;
    }

    // 合计行的比例 & 金额格式
    if($total_row['passed_count'] > 0) {
        $total_row['accept_rate'] = round($total_row['accept_count'] * 100 / $total_row['passed_count'], 1).'%';
    } else {
        $total_row['accept_rate'] = '--';
    }
    if($total_row['total_quotes'] > 0) {
        $total_row['finish_rate'] = round($total_row['finished_count'] * 100 / $total_row['total_quotes'], 1).'%';
    } else {
        $total_row['finish_rate'] = '--';
    }
    $total_row['total_final_amount_str']  = number_format($total_row['total_final_amount']);
    $total_row['total_repair_amount_str'] = number_format($total_row['total_repair_amount']);

    include tpl('quote_stats', $module);
} elseif($action == 'export') {

// 导出当前筛选结果为 CSV
    $status      = isset($status) ? intval($status) : -1;
    $keyword     = isset($keyword) ? trim($keyword) : '';
    $fromdate    = isset($fromdate) ? trim($fromdate) : '';
    $todate      = isset($todate) ? trim($todate) : '';
    $company_id  = isset($company_id) ? intval($company_id) : 0;
    $user_confirm_status = isset($user_confirm_status) ? intval($user_confirm_status) : -1;
    $repair_status      = isset($repair_status) ? intval($repair_status) : -1;
    $appointdate = isset($appointdate) ? trim($appointdate) : '';

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
    if($company_id) {
        $condition .= " AND q.company_id={$company_id}";
    }
    if($appointdate) {
        $af = strtotime($appointdate.' 00:00:00');
        $at = strtotime($appointdate.' 23:59:59');
        $condition .= " AND q.appoint_time>={$af} AND q.appoint_time<={$at}";
    }

    if($user_confirm_status > -1) $condition .= " AND q.user_confirm_status={$user_confirm_status}";
    if($repair_status >= 0)       $condition .= " AND q.repair_status={$repair_status}";

    $result = $db->query("SELECT q.*,c.company AS company_name 
                          FROM {$table} q 
                          LEFT JOIN {$table_company} c ON q.company_id=c.userid
                          WHERE {$condition}
                          ORDER BY q.itemid DESC");

    $filename = 'quote_'.$moduleid.'_'.timetodate($DT_TIME, 'YmdHis').'.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    echo "\xEF\xBB\xBF"; // BOM 防止 Excel 乱码

    // 表头
    $header = array(
        '报价ID','提交时间','客户姓名','邮箱','手机',
        '门店','预约时间',
        '故障原价合计','优惠码抵扣金额','人工优惠金额','最终报价金额',
        '审核状态','用户确认状态','用户确认时间',
        '维修状态','实收金额','完修时间','维修备注','管理员备注'
    );
    echo implode(',', $header)."\r\n";

    while($r = $db->fetch_array($result)) {
        $total_fault_amount = floatval($r['total_fault_amount']);
        $quote_amount       = floatval($r['quote_amount']);
        $manual_discount    = floatval($r['discount_amount']);
        $coupon_amount      = 0.00;
        if($total_fault_amount > 0 && $quote_amount >= 0 && $total_fault_amount >= $quote_amount) {
            $coupon_amount = $total_fault_amount - $quote_amount;
        }

        $final_amount = floatval($r['final_amount']);

        if($r['status'] == 1) {
            $status_text = '已通过';
        } elseif($r['status'] == 2) {
            $status_text = '已拒绝';
        } elseif($r['status'] == 0) {
            $status_text = '待审核';
        } else {
            $status_text = '未知';
        }

        $addtime_str     = $r['addtime'] ? timetodate($r['addtime'], 6) : '';
        $appointtime_str = $r['appoint_time'] ? timetodate($r['appoint_time'], 5) : '';
        $customer_name   = trim($r['first_name'].$r['last_name']);
        $company_name    = $r['company_name'];
        $user_confirm_status = $r['user_confirm_status']==1?"同意":"拒绝";
        if ($user_confirm_status==1){
            $user_confirm_status="同意";
        }elseif ($user_confirm_status==0){
            $user_confirm_status="---";
        }else {
            $user_confirm_status="拒绝";
        }
        $user_confirm_time = $r['user_confirm_time'] ? timetodate($r['user_confirm_time'], 5) : '---';
        $repair_status_val = isset($r['repair_status']) ? intval($r['repair_status']) : 0;
        $repair_status_text = isset($REPAIR_STATUS[$repair_status_val]) ? $REPAIR_STATUS[$repair_status_val] : '未设置';

        $repair_amount_val = isset($r['repair_amount']) ? floatval($r['repair_amount']) : 0;
        $repair_amount_str = number_format($repair_amount_val, 2, '.', '');

        $repair_time_str = $r['repair_time'] ? timetodate($r['repair_time'], 5) : '---';

// 维修备注做 CSV 转义
        $repair_note = str_replace('"', '""', $r['repair_note']);

        // 管理员备注做简单 CSV 转义
        $admin_note = str_replace('"', '""', $r['admin_note']);

        $line = array(
            $r['itemid'],
            $addtime_str,
            $customer_name,
            $r['email'],
            $r['mobile'],
            $company_name,
            $appointtime_str,
            number_format($total_fault_amount, 2, '.', ''),
            number_format($coupon_amount,      2, '.', ''),
            number_format($manual_discount,    2, '.', ''),
            number_format($final_amount,       2, '.', ''),
            $status_text,
            $user_confirm_status,
            $user_confirm_time,
            $repair_status_text,
            $repair_amount_str,
            $repair_time_str,
            $repair_note,
            $admin_note
        );

        echo implode(',', $line)."\r\n";
    }
    exit;

// ====== 查看 + 编辑 ======
} elseif($action == 'edit') {

    $itemid = intval($itemid);
    $itemid or msg('参数错误：itemid 不能为空');

    if($submit) {
       // var_dump(  $post);
       // die();
        $post = dhtmlspecialchars($post);
        $status_new      = isset($post['status']) ? intval($post['status']) : 0;
        $final_amount    = isset($post['final_amount']) ? floatval($post['final_amount']) : 0;
        $discount_amount = isset($post['discount_amount']) ? floatval($post['discount_amount']) : 0;
        $admin_note      = isset($post['admin_note']) ? trim($post['admin_note']) : '';
        $repair_status   = isset($post['repair_status']) ? intval($post['repair_status']) : 0;
        $repair_amount   = isset($post['repair_amount']) ? floatval($post['repair_amount']) : 0;
        $repair_note     = isset($post['repair_note']) ? trim($post['repair_note']) : '';
        // 邮件标题 & 正文（本次发送用，可在审核页编辑）
        $mail_subject    = isset($_POST['mail_subject']) ? trim($_POST['mail_subject']) : '';
        $mail_body       = isset($_POST['mail_body']) ? trim($_POST['mail_body']) : '';


        // var_dump($final_amount);
       // die();


        if($status_new < 0 || $status_new > 2) $status_new = 0;
        if($final_amount < 0) $final_amount = 0;
        if($discount_amount < 0) $discount_amount = 0;

        // 取旧记录
        $r = $db->get_one("SELECT * FROM {$table} WHERE itemid={$itemid}");
        $r or msg('记录不存在');

        // 完修时间：第一次设为“已完修”时记录时间戳
        $repair_time = intval($r['repair_time']);
        if($repair_status == 3 && !$repair_time) {
            $repair_time = $DT_TIME;
        }


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
                repair_status={$repair_status},
                repair_amount='".number_format($repair_amount, 2, '.', '')."',
                repair_note='{$repair_note}',
                repair_time={$repair_time},
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

            // 邮件标题：优先用审核页填写的标题
            $subject = $mail_subject ? dhtmlspecialchars($mail_subject) : '报价回执通知';

            // 邮件正文主体：优先用审核页填写的正文（按行转换为带 <br/> 的 HTML）
            if($mail_body) {
                // 先做转义，再把换行变成 <br/>
                $content = nl2br(dhtmlspecialchars($mail_body));
            } else {
                // 兼容：未填写正文时使用原来的默认文案
                $content = '您好，'.$r['first_name'].$r['last_name'].'：<br/><br/>'
                    . '您的设备报价已审核通过，最终报价金额为：'
                    . number_format($final_amount).' 円。<br/>'
                    . '预约到店时间：'.$appoint_str.'<br/><br/>';
            }

            // 管理员备注：有才显示，没有就整段不显示（保持和原来一致）
            if($admin_note) {
                $content .= '管理员备注：<br/>'.nl2br($admin_note).'<br/><br/>';
            }

            // 最后加上报价回执链接
            $content .= '请点击下面的链接查看详细报价回执：<br/>'
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
        $r['total_fault_amount'] = number_format($r['total_fault_amount']);
        $r['final_amount']       = number_format($r['final_amount']);
        $r['discount_amount']    = number_format($r['discount_amount']);

// 维修金额 & 完修时间
        $r['repair_amount']   = number_format($r['repair_amount']);
        $r['repair_time_str'] = $r['repair_time'] ? timetodate($r['repair_time'], 5) : '';

        $item = $r;
        include tpl('quote_edit', $module);

    }

} else {
    msg('未知操作');
}
