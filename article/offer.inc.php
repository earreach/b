<?php
defined('IN_DESTOON') or define('IN_DESTOON', true);
// 视你的安装目录结构而定，通常从 module 目录相对引用 root/common.inc.php
require DT_ROOT.'/module/'.$module.'/common.inc.php';

// 如果想抵抗直接 GET 请求也允许展示，可以不限制方法
// 这里我们不做任何数据处理，仅输出页面（可用模板或直接 echo）


$data = $_POST; // 不验证，仅传给模板

// 也可以将模板放在 template/default/article/offer.htm
$template = 'offer';
include template($template, 'article'); // 取决于你的 template() 函数签名
?>
