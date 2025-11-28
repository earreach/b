<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/include/module.func.php';
require DT_ROOT.'/module/'.$module.'/global.func.php';
$table = $DT_PRE.$module.'_'.$moduleid;
$table_topic = $DT_PRE.$module.'_topic_'.$moduleid;
$table_data = $DT_PRE.$module.'_data_'.$moduleid;
(isset($type) && in_array($type, array('follow', 'new', 'hot'))) or $type = '';
?>