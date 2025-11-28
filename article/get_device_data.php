<?php
// O:\phpstudy_pro\WWW\bcom\api\get_device_data.php
define('IN_DESTOON', true);
require_once '../include/common.inc.php';

// 引入文章模块的函数
require_once '../module/article/common.inc.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// 获取设备数据 - 使用您已有的方法
function get_device_data() {
    $moduleid = 21; // 固定模块ID
    
    // 获取一级分类8,20,22的所有二级分类并合并
    $ab = get_cattoarr(8, $moduleid);
    $cd = get_cattoarr(20, $moduleid);
    $ef = get_cattoarr(22, $moduleid);
    $aa = array_merge($ab, $cd, $ef);
    
    return $aa;
}

// 处理GET请求
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $data = get_device_data();
        
        echo json_encode(array(
            'success' => true,
            'data' => $data,
            'count' => count($data),
            'timestamp' => time()
        ), JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        echo json_encode(array(
            'success' => false,
            'message' => '获取数据失败: ' . $e->getMessage()
        ));
    }
} else {
    echo json_encode(array(
        'success' => false,
        'message' => '只支持GET请求'
    ));
}
?>