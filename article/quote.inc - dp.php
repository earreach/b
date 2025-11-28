<?php
defined('IN_DESTOON') or exit('Access Denied');
// 包含common.inc.php
require DT_ROOT.'/module/'.$module.'/common.inc.php';

// 获取设备数据
$devices_json = isset($_POST['devices']) ? stripslashes($_POST['devices']) : '';
$devices_json = isset($_GET['devices']) ? $_GET['devices'] : $devices_json;

// 如果没有设备数据，跳回list-4
if(!$devices_json) {
    dheader($MOD['linkurl'].'list-4.htm');
}

// 解析设备数据
$devices_data = json_decode($devices_json, true);
if(!$devices_data) {
    message('设备数据格式错误，请重新选择设备故障');
}

// 获取action
$action = isset($_GET['action']) ? $_GET['action'] : '';
$action = isset($_POST['action']) ? $_POST['action'] : $action;

// 处理表单提交
if(isset($_POST['submit'])) {
    // 验证必填字段
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';

    if(!$first_name) message('请填写姓');
    if(!$last_name) message('请填写名');

    // 验证联系方式
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';

    if(!$email && !$mobile) {
        message('请至少填写邮箱或手机号中的一项');
    }

    // 验证公司选择
    $company_id = isset($_POST['company_id']) ? intval($_POST['company_id']) : 0;
    if(!$company_id) message('请选择服务公司');

    // 自动注册会员（如果未登录）
    $userid = 0;
    if(!$_userid) {
        require DT_ROOT.'/module/member/member.class.php';
        $member_do = new member();

        $username = 'quote_'.date('YmdHis').mt_rand(100, 999);
        $password = $member_do->get_pwd();

        $member_data = array(
            'username' => $username,
            'passport' => $username,
            'password' => $password,
            'cpassword' => $password,
            'email' => $email,
            'mobile' => $mobile,
            'truename' => $first_name.$last_name,
            'groupid' => 5,
            'regid' => 5,
            'areaid' => 0
        );

        if($member_do->pass($member_data, 1)) {
            $userid = $member_do->add($member_data);
            if($userid) {
                $member_do->login($username, $password, 0, 'auto-register');
            }
        }
    } else {
        $userid = $_userid;
    }

    // 处理图片上传
    $image_paths = array();
    if(isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        require DT_ROOT.'/include/upload.class.php';
        $uploaddir = 'file/upload/'.timetodate($DT_TIME, $DT['uploaddir']).'/';
        is_dir(DT_ROOT.'/'.$uploaddir) or dir_create(DT_ROOT.'/'.$uploaddir);

        foreach($_FILES['images']['name'] as $key => $name) {
            if($_FILES['images']['error'][$key] == 0 && $_FILES['images']['size'][$key] > 0) {
                $file_data = array(
                    'name' => $name,
                    'type' => $_FILES['images']['type'][$key],
                    'tmp_name' => $_FILES['images']['tmp_name'][$key],
                    'error' => $_FILES['images']['error'][$key],
                    'size' => $_FILES['images']['size'][$key]
                );

                $upload = new upload(array($file_data), $uploaddir);
                if($upload->save()) {
                    $image_paths[] = $upload->saveto;
                }
            }
        }
    }

    // 获取其他数据
    $color = isset($_POST['color']) ? trim($_POST['color']) : '';
    $fault_desc = isset($_POST['fault_desc']) ? trim($_POST['fault_desc']) : '';
    $discount_code = isset($_POST['discount_code']) ? trim($_POST['discount_code']) : '';
    $verify_code = isset($_POST['verify_code']) ? trim($_POST['verify_code']) : '';

    // 保存报价数据
    $quote_data = array(
        'devices' => $devices_json,
        'color' => $color,
        'fault_desc' => $fault_desc,
        'images' => implode(',', $image_paths),
        'company_id' => $company_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'mobile' => $mobile,
        'discount_code' => $discount_code,
        'quote_amount' => 0.00,
        'discount_amount' => 0.00,
        'verify_status' => ($email || $mobile) ? 1 : 0,
        'status' => 0,
        'addtime' => DT_TIME,
        'edittime' => DT_TIME,
        'ip' => DT_IP,
        'userid' => $userid
    );

    $db->query("INSERT INTO {$DT_PRE}article_quote_21 ".arr2sql($quote_data, 0));
    $quote_id = $db->insert_id();

    if($quote_id) {
        // 提交成功
        dheader($MOD['linkurl'].'quote.inc.php?action=success&id='.$quote_id);
    } else {
        message('提交失败，请重试');
    }
}

// 显示报价表单
// 获取地区数据
$AREA = cache_read('area.php');

// 获取公司数据
$companies = array();
$result = $db->query("SELECT userid, company, areaid FROM {$DT_PRE}company WHERE validated=1 AND groupid IN (6,7)");
while($r = $db->fetch_array($result)) {
    $companies[$r['areaid']][] = $r;
}

// 获取颜色选项
$color_options = array();
if($devices_data) {
    $first_device = reset($devices_data);
    $catid = key($devices_data);
    $cat_info = get_cat($catid);
    if($cat_info && $cat_info['color']) {
        $color_options = explode(',', $cat_info['color']);
    }
}

$head_title = '设备故障报价 - '.$MOD['name'];
include template('quote-form', $module);
?>