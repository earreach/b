<?php
// module/article/quote.inc.php
// 设备故障报价页面：
// - 第一次（从 list-4 提交）只带 devices，用于展示确认页
// - 第二次（从报价表单提交）带 devices_detail + 其它字段，用于入库

defined('IN_DESTOON') or exit('Access Denied');

global $db, $DT_PRE, $DT_TIME, $MODULE;

// ----------------- 模块与表 -----------------
$moduleid = 21; // 文章模块
$MOD = isset($MODULE[$moduleid]) ? $MODULE[$moduleid] : cache_read('module-'.$moduleid.'.php');

$table_quote   = $DT_PRE.'article_quote_'.$moduleid; // 报价表 dt_article_quote_21
$table_cat     = $DT_PRE.'category';
$table_company = $DT_PRE.'company';

// ===================================================
// 一、提交分支：从 quote-form 提交回来，入库
// 条件：POST 且存在非空 devices_detail
// ===================================================
if($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['devices_detail'])
    && $_POST['devices_detail'] !== '') {


    // -------- 1）接收字段 --------
    $devices_json   = stripslashes(trim($_POST['devices_detail']));
    $model_catids   = trim(isset($_POST['model_catids']) ? $_POST['model_catids'] : '');

    // 颜色：支持 color[catid] 多设备颜色，也兼容旧版单一 color
    $color_raw  = isset($_POST['color']) ? $_POST['color'] : '';
    $color      = '';
    $color_map  = array(); // 每个设备 catid => 颜色

    if(is_array($color_raw)) {
        foreach($color_raw as $cid => $c) {
            $c = trim($c);
            if($c === '') continue;
            $cid = intval($cid);
            if($cid) $color_map[$cid] = $c;
        }
        if($color_map) {
            // 写入报价表 color 字段：多个颜色用逗号隔开
            $color = implode(',', array_values($color_map));
        }
    } else {
        $color = trim($color_raw);
    }

    $fault_desc     = trim(isset($_POST['fault_desc']) ? $_POST['fault_desc'] : '');


    $images_arr     = isset($_POST['images']) && is_array($_POST['images']) ? $_POST['images'] : array();

    $company_id     = intval(isset($_POST['company_id']) ? $_POST['company_id'] : 0);

    $appoint_date   = trim(isset($_POST['appoint_date']) ? $_POST['appoint_date'] : '');
    $appoint_hour   = trim(isset($_POST['appoint_hour']) ? $_POST['appoint_hour'] : '');
//    var_dump($appoint_date);
//    var_dump($appoint_hour);
//    die();


    $discount_code  = trim(isset($_POST['discount_code']) ? $_POST['discount_code'] : '');

    $first_name     = trim(isset($_POST['first_name']) ? $_POST['first_name'] : '');
    $last_name      = trim(isset($_POST['last_name']) ? $_POST['last_name'] : '');

    $mobile         = trim(isset($_POST['mobile']) ? $_POST['mobile'] : '');
    $email          = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $email_code     = trim(isset($_POST['email_code']) ? $_POST['email_code'] : '');

    // captcha 模板里的 name 一般是 captcha 或 verify
    $captcha        = '';
    if(isset($_POST['captcha'])) {
        $captcha = trim($_POST['captcha']);
    } else if(isset($_POST['verify'])) {
        $captcha = trim($_POST['verify']);
    }

    // -------- 2）基础校验 --------
    if($devices_json == '') {
        message('设备故障数据丢失，请返回重新选择');
    }

    $devices = json_decode($devices_json, true);
    if(!$devices || !is_array($devices)) {
        message('设备故障数据解析失败，请返回重新选择');
    }
    // 把颜色写回 devices 结构，便于后续保存和回执页展示
    if(!empty($color_map)) {
        foreach($color_map as $cid => $c) {
            if(isset($devices[$cid])) {
                $devices[$cid]['color'] = $c;
            }
        }
    } elseif($color !== '') {
        // 兼容旧数据：只有一个颜色时，所有设备共用
        foreach($devices as $cid => &$d) {
            $d['color'] = $color;
        }
        unset($d);
    }
    if(!$company_id) {
        message('请选择维修门店');
    }

    if($first_name == '' || $last_name == '') {
        message('请填写姓和名');
    }

    if($email == '') {
        message('请填写邮箱地址');
    }

    // 邮箱验证码必填
    if($email_code == '') {
        message('请填写邮箱验证码');
    }

    // 预约日期和时间必填
    if($appoint_date == '' || $appoint_hour === '') {
        message('请选择预约日期和时间');
    }

    // 邮箱格式简单判断
    if($email != '') {
        if(function_exists('is_email')) {
            if(!is_email($email)) message('邮箱格式不正确');
        } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            message('邮箱格式不正确');
        }
    }

    // 手机简单判断（可选填）
    if($mobile != '') {
        $m = preg_replace('/[\\s\\-\\+]/', '', $mobile);
        if(!preg_match('/^\\d{6,20}$/', $m)) {
            message('电话号码格式不正确');
        }
    }

    // -------- 3）图片验证码 --------
    if($captcha == '') {
        message('请填写图片验证码');
    }
    if(function_exists('captcha')) {
        // 你那边已经确认：captcha() 返回空字符串表示正确
        if(captcha($captcha) !== '') {
            message('图片验证码不正确');
        }
    }

    // -------- 4）邮箱验证码校验（quote_verify 表）--------
    $verify_status = 0;
    $table_verify  = $DT_PRE.'quote_verify';
    $scene         = 'quote';

    // 防 SQL 注入
    if(function_exists('daddslashes')) {
        $email_sql = daddslashes($email);
        $code_sql  = daddslashes($email_code);
    } else {
        $email_sql = addslashes($email);
        $code_sql  = addslashes($email_code);
    }

    // 取最近一条未使用的验证码记录
    $r = $db->get_one("SELECT * FROM {$table_verify}
                   WHERE contact='$email_sql'
                     AND type='email'
                     AND scene='$scene'
                     AND code='$code_sql'
                     AND status=0
                   ORDER BY id DESC");
    if(!$r) {
        message('邮箱验证码错误或不存在，请重新获取');
    }

    if($r['expiretime'] < $DT_TIME) {
        message('邮箱验证码已过期，请重新获取');
    }

    // 标记验证码已使用
    $db->query("UPDATE {$table_verify} SET status=1 WHERE id='{$r['id']}'");

    // 联系方式已验证
    $verify_status = 1;

    // -------- 5）处理图片路径 --------
    $images_clean = array();
    if($images_arr) {
        foreach($images_arr as $p) {
            $p = trim($p);
            if($p == '') continue;
            // 简单防目录穿越
            if(strpos($p, '..') !== false) continue;
            $images_clean[] = $p;
        }
    }
    $images_str = $images_clean ? implode(',', $images_clean) : '';

    // 6) 计算金额（故障原价合计 = 所有选中故障的 price 之和）
    $total_fault_amount = 0.00;
    foreach($devices as $catid => $d) {
        if(!isset($d['faults']) || !is_array($d['faults'])) continue;
        foreach($d['faults'] as $f) {
            $p = isset($f['price']) ? floatval($f['price']) : 0;
            $total_fault_amount += $p;
        }
    }

    // 7) 优惠码逻辑：优惠码抵扣金额是固定的，绑定在优惠码上
    //    这里先简单写死：只要填写了优惠码，就减 1000（之后可以改成查优惠码表）
//    暂时不减
    $coupon_amount = 0.00;
    if($discount_code !== '') {
        $coupon_amount = 0.00;
    }
    if($coupon_amount < 0) $coupon_amount = 0;
    if($coupon_amount > $total_fault_amount) $coupon_amount = $total_fault_amount;

    // 7.1 报价金额（quote_amount）：只减“优惠码抵扣金额”之后的金额
    $quote_amount = $total_fault_amount - $coupon_amount;

    // 7.2 人工优惠金额（discount_amount）：后台审核时填写，这里先置 0
    $manual_discount = 0.00;

    // 7.3 最终报价金额 = 故障原价合计 - 优惠码抵扣金额 - 人工优惠金额
    $final_amount = $total_fault_amount - $coupon_amount - $manual_discount;
//    var_dump($final_amount);
//    var_dump(9999999999);
//    die();
    if($final_amount < 0) $final_amount = 0;

    // 8) 预约时间（现在仍然只做时间戳，不入库，后面要存的话表里加字段）
    $appoint_time = 0;
    if($appoint_date && $appoint_hour !== '') {
        $appoint_time = strtotime($appoint_date.' '.$appoint_hour.':00:00');
        // 这里不强制校验是否早于当前时间，避免影响测试
    }

    // 9) 组装并入库 dt_article_quote_21
    $devices_save  = daddslashes(json_encode($devices));
    $color         = daddslashes($color);
    $fault_desc    = daddslashes($fault_desc);
    $discount_code = daddslashes($discount_code);
    $first_name    = daddslashes($first_name);
    $last_name     = daddslashes($last_name);
    $email         = daddslashes($email);
    $mobile        = daddslashes($mobile);

    $company_id    = intval($company_id);
    $addtime       = $DT_TIME;
    $edittime      = $DT_TIME;
    $status        = 0; // 0=待审核

    $sql = "INSERT INTO {$table_quote}
        (`devices`,`color`,`fault_desc`,`images`,
         `company_id`,
         `first_name`,`last_name`,
         `email`,`mobile`,
         `discount_code`,`quote_amount`,`discount_amount`,
         `total_fault_amount`,`final_amount`,
         `verify_status`,`status`,
         `addtime`,`edittime`
        ) VALUES (
         '$devices_save','$color','$fault_desc','$images_str',
         '$company_id',
         '$first_name','$last_name',
         '$email','$mobile',
         '$discount_code',
         '".number_format($quote_amount,      2, '.', '')."',  -- 只减优惠码后的金额
         '".number_format($manual_discount,   2, '.', '')."',  -- 人工优惠金额，初始 0
         '".number_format($total_fault_amount,2, '.', '')."',  -- 故障原价合计
         '".number_format($final_amount,      2, '.', '')."',  -- 最终报价金额
         '$verify_status','$status',
         '$addtime','$edittime'
        )";

    $ok = $db->query($sql);

// 统一判断是否为 AJAX 请求（前端会加 X-Requested-With 头）
    $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if(!$ok) {
        if($is_ajax) {
            // AJAX：返回 JSON，不跳转
            echo json_encode(array(
                'success' => false,
                'message' => '保存报价失败：'.$db->errno.' '.$db->error,
            ));
            exit;
        } else {
            // 普通表单：保持原有 message 行为
            message('保存报价失败：'.$db->errno.' '.$db->error);
        }
    }

// 成功
    if($is_ajax) {
        // AJAX：返回 JSON，前端自行弹窗+倒计时+跳转
        echo json_encode(array(
            'success' => true,
            'message' => '提交成功，我们会在2个小时内联系您。',
        ));
        exit;
    } else {
        // 兜底：如果不是 AJAX，保持原来的 message 行为
        message('提交成功！我们会在2个小时内通过邮箱发送报价确认信息。');
    }

}

// ===================================================
// 二、展示报价表单：从 list-4 跳转过来
// 条件：POST 且存在非空 devices
// ===================================================
if(isset($_POST['devices']) && $_POST['devices'] !== '') {

    $devices_raw = stripslashes(trim($_POST['devices']));

    if($devices_raw == '') {
        message('设备数据为空，请返回重新选择');
    }

    $input_devices = json_decode($devices_raw, true);
    if(!$input_devices || !is_array($input_devices)) {
        message('设备数据格式错误，请返回重新选择');
    }

    // 预期结构：
    // $input_devices = [
    //   "205" => ["name" => "iPhone 16", "faults" => ["1","2","3"]],
    //   "210" => ["name" => "iPhone 15", "faults" => ["1","3"]]
    // ];

    // 1）收集 catid，并查询分类表（带 color & fua）
    $catids_int = array();
    foreach($input_devices as $catid_key => $d) {
        $cid = intval($catid_key);
        if($cid) $catids_int[] = $cid;
    }
    $catids_int = array_unique($catids_int);
    if(!$catids_int) {
        message('设备数据格式错误，请返回重新选择');
    }
    $catids_str = implode(',', $catids_int);

    // 取分类：型号名 catname + 颜色 color + 故障信息 fua
    $cats = array();
    $result = $db->query("SELECT catid,catname,color,fua FROM {$table_cat} WHERE catid IN($catids_str)");
    while($r = $db->fetch_array($result)) {
        $cats[$r['catid']] = $r;
    }

    // 2）重新组装 devices 结构：把 list-4 选中的故障 num，映射到 fua 里的 name+price
    $devices        = array();
    $color_options  = array();
    $model_catids   = array(); // 后面隐藏字段用

    foreach($input_devices as $catid_key => $d) {
        $catid = intval($catid_key);
        if(!$catid || !isset($cats[$catid])) continue;
        $cat = $cats[$catid];

        $device_name = $cat['catname'];

        // 颜色选项：dt_category.color 逗号分隔
        $color_arr = array();
        if(!empty($cat['color'])) {
            $tmp = explode(',', $cat['color']);
            foreach($tmp as $c) {
                $c = trim($c);
                if($c !== '') $color_arr[] = $c;
            }
        }
        if($color_arr) $color_options[$catid] = $color_arr;

        // 故障信息：dt_category.fua 是 JSON，结构类似：
        // [
        //   {"bi":"543534","num":"4","ming":"%E5%A4%A7%E6%96%B9"},
        //   ...
        // ]
        $fault_map = array();
        if(!empty($cat['fua'])) {
            $fua_arr = json_decode($cat['fua'], true);
            if($fua_arr && is_array($fua_arr)) {
                foreach($fua_arr as $row) {
                    $num  = isset($row['num']) ? (string)$row['num'] : '';
                    if($num === '') continue;
                    $ming_raw = isset($row['ming']) ? $row['ming'] : '';
                    $ming     = urldecode($ming_raw);
                    $price    = isset($row['bi']) ? floatval($row['bi']) : 0;

                    $fault_map[$num] = array(
                        'name'  => $ming,
                        'price' => $price,
                    );
                }
            }
        }

        // 用户在 list-4 选中的 num 列表
        $fault_nums = (isset($d['faults']) && is_array($d['faults'])) ? $d['faults'] : array();
        $faults     = array();
        foreach($fault_nums as $num) {
            $num = (string)$num;
            if(isset($fault_map[$num])) {
                $faults[] = array(
                    'num'   => $num,
                    'name'  => $fault_map[$num]['name'],
                    'price' => $fault_map[$num]['price'],
                );
            }
        }

        if(!$faults) continue; // 如果这个型号没有有效故障，就跳过

        $devices[$catid] = array(
            'name'   => $device_name,
            'faults' => $faults,
        );
        $model_catids[] = $catid;
    }

    if(!$devices) {
        message('没有有效的设备故障数据，请返回重新选择');
    }

    // 3）准备给模板的变量
    $devices_detail_json = json_encode($devices);        // 给隐藏字段 devices_detail
    $model_catids_str    = implode(',', $model_catids);  // 给隐藏字段 model_catids

    // 4）读取公司列表（userid/company/areaid/thumb/linkurl/validated）
    $companies = array();
    $result = $db->query("SELECT userid,company,areaid,thumb,linkurl,validated FROM {$table_company} WHERE company<>'' ORDER BY userid DESC");
    while($r = $db->fetch_array($result)) {
        $companies[] = $r;
    }

    // 5）进入模板
    // 模板里会用到：
    // - $devices             设备+故障明细（展示）
    // - $color_options       每个设备可选颜色
    // - $companies           公司列表 + 地区
    // - $devices_detail_json 隐藏字段，二次提交用
    // - $model_catids_str    隐藏字段，后续扩展用
    $title = '设备故障报价';
    include template('quote-form', 'article');
    exit;
}

// 既没有 devices_detail，也没有 devices，说明入口不对
message('参数错误，请从设备选择页面重新进入');
