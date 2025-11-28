<?php
// module/article/quote_view.inc.php
defined('IN_DESTOON') or exit('Access Denied');

require DT_ROOT.'/module/'.$module.'/common.inc.php';

global $db, $DT_PRE, $DT_TIME, $MODULE;

$table         = $DT_PRE.'article_quote_'.$moduleid;
$table_company = $DT_PRE.'company';

// 支持 GET/POST 传 itemid, token（POST 用于用户在回执页点击确认）
$itemid = isset($_GET['itemid']) ? intval($_GET['itemid']) : 0;
if(!$itemid && isset($_POST['itemid'])) $itemid = intval($_POST['itemid']);

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
if(!$token && isset($_POST['token'])) $token = trim($_POST['token']);

if(!$itemid || !$token) {
    message('链接参数错误');
}

// 取报价数据 + 门店信息
$r = $db->get_one("SELECT q.*,
                          c.company   AS company_name,
                          c.areaid    AS company_areaid,
                          c.address   AS company_address,
                          c.telephone AS company_telephone
                
                   FROM {$table} q
                   LEFT JOIN {$table_company} c ON q.company_id=c.userid
                   WHERE q.itemid={$itemid} AND q.receipt_token='{$token}'");

if(!$r) {
    message('报价记录不存在或链接无效');
}

// 仅允许已审核通过的报价查看
if($r['status'] != 1) {
    message('该报价尚未审核通过，暂无法查看回执');
}
// 标记本次请求是否刚完成一次确认操作（用于发送通知邮件）
$just_changed_confirm = false;
$confirm_action       = '';




// =============== 用户确认（接受 / 暂不接受） ===============
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = isset($_POST['do']) ? trim($_POST['do']) : '';
    if($do == 'confirm' || $do == 'reject') {

        // 只允许当前这一轮里尚未表态的记录修改一次
        // 管理员如果想开启新一轮，会在后台把 user_confirm_status 重置为 0
        if(empty($r['user_confirm_status'])) {
            $new_status = ($do == 'confirm') ? 1 : 2; // 1=已接受, 2=暂不接受

            // 用来拼接额外要更新的字段（devices / repair_status）
            $extra_sql_parts = array();

            // 如果是“暂不接受”，尝试保存用户拒绝理由（按设备）
            if($do == 'reject') {
                $reject_reason = (isset($_POST['reject_reason']) && is_array($_POST['reject_reason']))
                    ? $_POST['reject_reason']
                    : array();

                if($reject_reason) {
                    $devices_for_update = array();
                    if(!empty($r['devices'])) {
                        $tmp = json_decode($r['devices'], true);
                        if($tmp && is_array($tmp)) $devices_for_update = $tmp;
                    }
                    if($devices_for_update) {
                        // 这里假设前端 textarea name 是 reject_reason[某个键]
                        foreach($reject_reason as $k => $text) {
                            $text = trim($text);
                            if($text === '') continue;
                            if(isset($devices_for_update[$k])) {
                                // 按设备记录拒绝理由
                                $devices_for_update[$k]['reject_reason'] = $text;
                            }
                        }
                        $devices_json = json_encode($devices_for_update);
                        if($devices_json !== false) {
                            if(function_exists('daddslashes')) {
                                $devices_sql = daddslashes($devices_json);
                            } else {
                                $devices_sql = addslashes($devices_json);
                            }
                            $extra_sql_parts[] = "devices='{$devices_sql}'";
                            // 当前页面使用更新后的 devices
                            $r['devices'] = $devices_json;
                        }
                    }
                }
            }

            // ===== 自动更新维修状态 repair_status =====
            $current_repair_status = isset($r['repair_status']) ? intval($r['repair_status']) : 0;

            if($do == 'confirm') {
                // 接受报价：当前为 未设置(0) 或 已取消(4) 时，自动设为 已预约(1)
                if($current_repair_status == 0 || $current_repair_status == 4) {
                    $extra_sql_parts[] = "repair_status=1";
                    $r['repair_status'] = 1;
                }
            } else { // 暂不接受
                // 用户明确拒绝：自动标记为 已取消(4)
                $extra_sql_parts[] = "repair_status=4";
                $r['repair_status'] = 4;
            }

            // 拼接额外 SQL 片段
            $extra_sql = '';
            if($extra_sql_parts) {
                $extra_sql = ','.implode(',', $extra_sql_parts);
            }

            // 更新当前这条报价的确认状态和时间
            $db->query("UPDATE {$table}
                        SET user_confirm_status={$new_status},
                            user_confirm_time={$DT_TIME}{$extra_sql}
                        WHERE itemid={$itemid}");

            $r['user_confirm_status'] = $new_status;
            $r['user_confirm_time']   = $DT_TIME;

            // 标记：本次请求完成了一次用户确认，用于后面发通知邮件
            $just_changed_confirm = true;
            $confirm_action       = $do; // 'confirm' 或 'reject'
        }
    }
}


// 解析设备 JSON
$devices = array();
if(!empty($r['devices'])) {
    $tmp = json_decode($r['devices'], true);
    if(is_array($tmp)) $devices = $tmp;
}

// 金额计算
$total_fault_amount = isset($r['total_fault_amount']) ? floatval($r['total_fault_amount']) : 0.00;
$quote_amount       = isset($r['quote_amount'])       ? floatval($r['quote_amount'])       : 0.00;
$manual_discount    = isset($r['discount_amount'])    ? floatval($r['discount_amount'])    : 0.00;

// 优惠码抵扣金额：原价合计 - quote_amount（quote_amount 是“使用优惠码后金额”）
$coupon_amount = 0.00;
if($total_fault_amount > 0 && $quote_amount >= 0 && $total_fault_amount >= $quote_amount) {
    $coupon_amount = $total_fault_amount - $quote_amount;
}

// 总优惠 = 优惠码 + 人工优惠
$total_discount = $coupon_amount + $manual_discount;

// 最终报价金额：优先用表里的 final_amount，没有则按公式兜底算一遍
$final_amount = isset($r['final_amount']) ? floatval($r['final_amount']) : 0.00;
if($final_amount <= 0 && $total_fault_amount > 0) {
    $final_amount = $total_fault_amount - $total_discount;
    if($final_amount < 0) $final_amount = 0.00;
}


// 格式化给模板用
$r['total_fault_amount_str'] = number_format($total_fault_amount, 2);
$r['coupon_amount_str']      = number_format($coupon_amount, 2);
$r['manual_discount_str']    = number_format($manual_discount, 2);
$r['discount_amount_str']    = number_format($total_discount, 2);
$r['final_amount_str']       = number_format($final_amount, 2);

// 如果本次请求刚刚完成了用户确认（接受或暂不接受），给管理员 & 门店发送通知邮件
if($just_changed_confirm) {
    // 门店信息（company_name 已在最开始的 JOIN 中取到）
    $company_name  = isset($r['company_name']) ? $r['company_name'] : '';
    $company_email = '';
    if(!empty($r['company_id'])) {
        // company 表字段是 mail，这里起别名为 email 统一处理
        $c = $db->get_one("SELECT mail AS email FROM {$table_company} WHERE userid=".intval($r['company_id']));
        if($c && !empty($c['email'])) {
            $company_email = $c['email'];
        }
    }

    // 管理员接收邮箱：从 member 表中 userid=1 的 email 读取
    $admin_email  = '';
    $table_member = $DT_PRE.'member';
    $m = $db->get_one("SELECT email FROM {$table_member} WHERE userid=1");
    if($m && !empty($m['email'])) {
        $admin_email = $m['email'];
    }

    $customer_name = $r['first_name'].$r['last_name'];
    $action_text   = ($confirm_action == 'confirm') ? '已接受本次报价' : '暂不接受本次报价';

    $subject = '【回执确认】'.$customer_name.' '.$action_text;

    // 使用上面刚算好的金额
    $content = "报价回执状态更新：\n\n".
        "报价ID：{$itemid}\n".
        "客户：{$customer_name}\n".
        "邮箱：{$r['email']}\n".
        "手机：{$r['mobile']}\n".
        "门店：".($company_name ? $company_name : '未选择')."\n".
        "当前操作：{$action_text}\n".
        "故障原价合计：".number_format($total_fault_amount, 2)." 円\n".
        "优惠后报价：".number_format($final_amount, 2)." 円\n".
        "用户确认时间：".timetodate($r['user_confirm_time'], 5)."\n\n".
        "请登录后台在报价管理中查看详细信息。";

    if(function_exists('send_mail')) {
        if($admin_email) {
            // 发给管理员
            send_mail($admin_email, $subject, nl2br($content));
        }
        if($company_email && $company_email != $admin_email) {
            // 发给门店
            send_mail($company_email, $subject, nl2br($content));
        }
    }
}



// 用户确认显示文案
$r['user_confirm_text'] = '您尚未在回执页进行操作';
if(!empty($r['user_confirm_status'])) {
    if($r['user_confirm_status'] == 1) {
        $r['user_confirm_text'] = '您已确认接受本次报价';
    } else if($r['user_confirm_status'] == 2) {
        $r['user_confirm_text'] = '您已标记为暂不接受本次报价';
    }
}

// 审核状态（这里只会是已通过）
$status_text  = '已通过';

// 把设备结构也丢给模板
$quote_devices = $devices;

$head_title = '报价回执 - '.$r['first_name'].$r['last_name'];

$item = $r;
include template('quote_view', $module);
