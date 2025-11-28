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

// =============== 用户确认（接受 / 暂不接受） ===============
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = isset($_POST['do']) ? trim($_POST['do']) : '';
    if($do == 'confirm' || $do == 'reject') {
        // 只允许尚未确认的记录修改一次
        if(empty($r['user_confirm_status'])) {
            $new_status = ($do == 'confirm') ? 1 : 2; // 1=已接受, 2=暂不接受

            $extra_sql = '';

            // 如果是“暂不接受”，尝试保存用户拒绝理由（按设备）
            if($do == 'reject') {
                $reject_reason = isset($_POST['reject_reason']) && is_array($_POST['reject_reason']) ? $_POST['reject_reason'] : array();
                if($reject_reason) {
                    $devices_for_update = array();
                    if(!empty($r['devices'])) {
                        $tmp = json_decode($r['devices'], true);
                        if($tmp && is_array($tmp)) $devices_for_update = $tmp;
                    }
                    if($devices_for_update) {
                        foreach($reject_reason as $cid => $text) {
                            $text = trim($text);
                            if($text === '') continue;
                            if(isset($devices_for_update[$cid])) {
                                // 按设备记录拒绝理由
                                $devices_for_update[$cid]['reject_reason'] = $text;
                            }
                        }
                        $devices_json = json_encode($devices_for_update);
                        if($devices_json !== false) {
                            if(function_exists('daddslashes')) {
                                $devices_sql = daddslashes($devices_json);
                            } else {
                                $devices_sql = addslashes($devices_json);
                            }
                            $extra_sql = ",devices='{$devices_sql}'";
                            // 当前页面使用更新后的 devices
                            $r['devices'] = $devices_json;
                        }
                    }
                }
            }

            $db->query("UPDATE {$table}
                        SET user_confirm_status={$new_status},
                            user_confirm_time={$DT_TIME}{$extra_sql}
                        WHERE itemid={$itemid}");

            $r['user_confirm_status'] = $new_status;
            $r['user_confirm_time']   = $DT_TIME;
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
