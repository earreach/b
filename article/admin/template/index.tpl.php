<?php
defined('DT_ADMIN') or exit('Access Denied');
include tpl('header');
show_menu($menus);
?>
<form action="?" id="search">
<input type="hidden" name="moduleid" value="<?php echo $moduleid;?>"/>
<input type="hidden" name="action" value="<?php echo $action;?>"/>
<table cellspacing="0" class="tb">
<tr>
<td>
&nbsp;<?php echo $fields_select;?>&nbsp;
<input type="text" size="30" name="kw" value="<?php echo $kw;?>" placeholder="请输入关键词" title="请输入关键词"/>&nbsp;
<span data-hide-1200="1"><?php echo $level_select;?>&nbsp;</span>
<?php echo $order_select;?>&nbsp;
<input type="text" name="username" value="<?php echo $username;?>" size="10" placeholder="会员名" title="会员名 双击显示会员资料" ondblclick="if(this.value){_user(this.value);}"/>&nbsp;
<input type="text" size="10" name="itemid" value="<?php echo $itemid;?>" placeholder="<?php echo $MOD['name'];?>ID" title="<?php echo $MOD['name'];?>ID"/>&nbsp;
<input type="text" name="psize" value="<?php echo $pagesize;?>" size="2" class="t_c" placeholder="条/页" title="条/页"/>&nbsp;
<input type="submit" value="搜 索" class="btn"/>&nbsp;
<input type="button" value="重 置" class="btn" onclick="Go('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=<?php echo $action;?>');"/>
</td>
</tr>
<tr>
<td>
&nbsp;<select name="datetype">
<option value="edittime"<?php if($datetype == 'edittime') echo ' selected';?>>更新时间</option>
<option value="addtime"<?php if($datetype == 'addtime') echo ' selected';?>>发布时间</option>
</select>&nbsp;
<?php echo dcalendar('fromdate', $fromdate, '-', 1);?> 至 <?php echo dcalendar('todate', $todate, '-', 1);?>&nbsp;
<?php echo $_admin == 1 ? category_select('catid', '不限分类', $catid, $moduleid) : ajax_category_select('catid', '不限分类', $catid, $moduleid);?>&nbsp;
<?php echo ajax_area_select('areaid', '不限地区', $areaid);?>&nbsp;
<label><input type="checkbox" name="thumb" value="1"<?php echo $thumb ? ' checked' : '';?>/> 图片&nbsp;</label>
<label><input type="checkbox" name="link" value="1"<?php echo $link ? ' checked' : '';?>/> 外链&nbsp;</label>
<label><input type="checkbox" name="tags" value="1"<?php echo $tags ? ' checked' : '';?>/> 标签&nbsp;</label>
<label><input type="checkbox" name="guest" value="1"<?php echo $guest ? ' checked' : '';?>/> 游客&nbsp;</label>
</td>
</tr>
</table>
</form>
<form method="post">
<table cellspacing="0" class="tb ls">
<tr>
<th width="20"><input type="checkbox" onclick="checkall(this.form);" title="全选/反选"/></th>
<th>分类</th>
<th width="16"><a href="javascript:;" onclick="Dq('order','<?php echo $order == 5 ? 6 : 5;?>');"><img src="<?php echo DT_STATIC;?>image/ico-<?php echo $order == 6 ? 'asc' : ($order == 5 ? 'dsc' : 'ord');?>.png" width="11" height="11"/></a></th>
<th width="60"><a href="javascript:;" onclick="Dq('thumb',<?php echo $thumb ? 0 : 1;?>);">图片</a></th>
<th>标题</th>
<th>会员</th>
<th><a href="javascript:;" onclick="Dq('order','<?php echo $order == 7 ? 8 : 7;?>');">浏览 <img src="<?php echo DT_STATIC;?>image/ico-<?php echo $order == 8 ? 'asc' : ($order == 7 ? 'dsc' : 'ord');?>.png" width="11" height="11"/></a></th>
<th><a href="javascript:;" onclick="Dq('order','<?php echo $order == 9 ? 10 : 9;?>');">点赞 <img src="<?php echo DT_STATIC;?>image/ico-<?php echo $order == 10 ? 'asc' : ($order == 9 ? 'dsc' : 'ord');?>.png" width="11" height="11"/></a></th>
<?php if($order == 11 || $order == 12) { ?><th><a href="javascript:;" onclick="Dq('order','<?php echo $order == 11 ? 12 : 11;?>');">反对 <img src="<?php echo DT_STATIC;?>image/ico-<?php echo $order == 12 ? 'asc' : ($order == 11 ? 'dsc' : 'ord');?>.png" width="11" height="11"/></a></th><?php } ?>
<?php if($order == 13 || $order == 14) { ?><th><a href="javascript:;" onclick="Dq('order','<?php echo $order == 13 ? 14 : 13;?>');">收藏 <img src="<?php echo DT_STATIC;?>image/ico-<?php echo $order == 14 ? 'asc' : ($order == 13 ? 'dsc' : 'ord');?>.png" width="11" height="11"/></a></th><?php } ?>
<th><a href="javascript:;" onclick="Dq('order','<?php echo $order == 15 ? 16 : 15;?>');">打赏 <img src="<?php echo DT_STATIC;?>image/ico-<?php echo $order == 16 ? 'asc' : ($order == 15 ? 'dsc' : 'ord');?>.png" width="11" height="11"/></a></th>
<?php if($order == 17 || $order == 18) { ?><th><a href="javascript:;" onclick="Dq('order','<?php echo $order == 17 ? 18 : 17;?>');">赏金 <img src="<?php echo DT_STATIC;?>image/ico-<?php echo $order == 18 ? 'asc' : ($order == 17 ? 'dsc' : 'ord');?>.png" width="11" height="11"/></a></th><?php } ?>
<?php if($order == 19 || $order == 20) { ?><th><a href="javascript:;" onclick="Dq('order','<?php echo $order == 19 ? 20 : 19;?>');">分享 <img src="<?php echo DT_STATIC;?>image/ico-<?php echo $order == 20 ? 'asc' : ($order == 19 ? 'dsc' : 'ord');?>.png" width="11" height="11"/></a></th><?php } ?>
<?php if($order == 21 || $order == 22) { ?><th><a href="javascript:;" onclick="Dq('order','<?php echo $order == 21 ? 22 : 21;?>');">举报 <img src="<?php echo DT_STATIC;?>image/ico-<?php echo $order == 22 ? 'asc' : ($order == 21 ? 'dsc' : 'ord');?>.png" width="11" height="11"/></a></th><?php } ?>
<th><a href="javascript:;" onclick="Dq('order','<?php echo $order == 23 ? 24 : 23;?>');">评论 <img src="<?php echo DT_STATIC;?>image/ico-<?php echo $order == 24 ? 'asc' : ($order == 23 ? 'dsc' : 'ord');?>.png" width="11" height="11"/></a></th>
<th width="40">修改</th>
</tr>
<?php foreach($lists as $k=>$v) {?>
<tr align="center">
<td><input type="checkbox" name="itemid[]" value="<?php echo $v['itemid'];?>"/></td>
<td><a href="<?php echo $v['caturl'];?>" target="_blank"><?php echo $v['catname'];?></a></td>
<td><?php if($v['level']) {?><a href="javascript:;" onclick="Dq('level','<?php echo $v['level'];?>');"><img src="<?php echo DT_STATIC;?>admin/level_<?php echo $v['level'];?>.gif" title="<?php echo $v['level'];?>级" alt=""/></a><?php } ?></td>
<td><a href="javascript:;" onclick="_preview('<?php echo $v['thumb'];?>');"><img src="<?php echo $v['thumb'] ? $v['thumb'] : DT_STATIC.'image/nopic60.png';?>" width="60" class="thumb"/></a></td>
<td>
<div class="lt">
<?php if($v['status'] == 3) {?>
<a href="<?php echo $v['linkurl'];?>" target="_blank" class="t"><?php echo $v['title'];?></a>
<?php } else { ?>
<a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=edit&itemid=<?php echo $v['itemid'];?>" class="t"><?php echo $v['title'];?></a>
<?php } ?>
<div>
更新:<span class="c_p" onclick="Dq('datetype','edittime',0);Dq('date',this.innerHTML);"><?php echo timetodate($v['edittime'], 6);?></span><br/>
添加:<span class="c_p" onclick="Dq('datetype','addtime',0);Dq('date',this.innerHTML);"><?php echo timetodate($v['addtime'], 6);?></span>
</div>
</div>
</td>
<td title="编辑:<?php echo $v['editor'];?>">
<?php if($v['username']) { ?>
	<a href="javascript:;" onclick="_user(this.innerHTML);"><?php echo $v['username'];?></a>
<?php } else { ?>
	<a href="javascript:;" onclick="_ip(this.innerHTML);" title="游客"><?php echo $v['ip'];?></a>
<?php } ?>
</td>
<td><a href="javascript:;" onclick="Dwidget('?file=stats&action=pv&mid=<?php echo $moduleid;?>&catid=<?php echo $v['catid'];?>&itemid=<?php echo $v['itemid'];?>', '[<?php echo $v['alt'];?>] 浏览记录');"><?php echo $v['hits'];?></a></td>
<td><a href="javascript:;" onclick="Dwidget('?file=like&action=like&mid=<?php echo $moduleid;?>&tid=<?php echo $v['itemid'];?>', '点赞记录');"><?php echo $v['likes'];?></a></td>
<?php if($order == 11 || $order == 12) { ?><td><a href="javascript:;" onclick="Dwidget('?file=like&action=hate&mid=<?php echo $moduleid;?>&tid=<?php echo $v['itemid'];?>', '反对记录');"><?php echo $v['hates'];?></a></td><?php } ?>
<?php if($order == 13 || $order == 14) { ?><td><a href="javascript:;" onclick="Dwidget('?moduleid=2&file=favorite&mid=<?php echo $moduleid;?>&tid=<?php echo $v['itemid'];?>', '[<?php echo $v['alt'];?>] 收藏记录');"><?php echo $v['favorites'];?></a></td><?php } ?>
<td><a href="javascript:;" onclick="Dwidget('?moduleid=2&file=award&mid=<?php echo $moduleid;?>&tid=<?php echo $v['itemid'];?>', '[<?php echo $v['alt'];?>] 打赏记录');"><?php echo $v['awards'];?></a></td>
<?php if($order == 17 || $order == 18) { ?><td><a href="javascript:;" onclick="Dwidget('?moduleid=2&file=award&mid=<?php echo $moduleid;?>&tid=<?php echo $v['itemid'];?>', '[<?php echo $v['alt'];?>] 打赏记录');"><?php echo $v['award'];?></a></td><?php } ?>
<?php if($order == 19 || $order == 20) { ?><td><a href="javascript:;" onclick="Dwidget('?file=stats&action=pv&mid=<?php echo $moduleid;?>&itemid=<?php echo $v['itemid'];?>&kw=share.php', '[<?php echo $v['alt'];?>] 分享记录');"><?php echo $v['shares'];?></a></td><?php } ?>
<?php if($order == 21 || $order == 22) { ?><td><a href="javascript:;" onclick="Dwidget('?moduleid=3&file=guestbook&mid=<?php echo $moduleid;?>&tid=<?php echo $v['itemid'];?>', '举报记录');"><?php echo $v['reports'];?></a></td><?php } ?>
<td><a href="javascript:;" onclick="Dwidget('?moduleid=3&file=comment&mid=<?php echo $moduleid;?>&itemid=<?php echo $v['itemid'];?>', '[<?php echo $v['alt'];?>] 评论列表');"><?php echo $v['comments'];?></a></td>
<td><a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=edit&itemid=<?php echo $v['itemid'];?>"><img src="<?php echo DT_STATIC;?>admin/edit.png" width="16" height="16" title="修改" alt=""/></a></td>
</tr>
<?php }?>
</table>
<?php include tpl('notice_chip');?>
<div class="btns">
<label><input type="checkbox" onclick="checkall(this.form);" title="全选/反选"/></label>
<?php if($action == 'check') { ?>

<input type="submit" value="通过审核" class="btn-g" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=check';"/>&nbsp;
<input type="submit" value="拒 绝" class="btn-r" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=reject';"/>&nbsp;
<input type="submit" value="移动分类" class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=move';"/>&nbsp;
<input type="submit" value="回收站" class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete&recycle=1';"/>&nbsp;
<input type="submit" value="彻底删除" class="btn-r" onclick="if(confirm('确定要删除选中<?php echo $MOD['name'];?>吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete'}else{return false;}"/>&nbsp;

<?php } else if($action == 'expire') { ?>
<input type="submit" value="刷新待发" class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=expire&refresh=1';"/>&nbsp;
<input type="submit" value="回收站" class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete&recycle=1';"/>&nbsp;
<input type="submit" value="彻底删除" class="btn-r" onclick="if(confirm('确定要删除选中<?php echo $MOD['name'];?>吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete'}else{return false;}"/>&nbsp;

<?php } else if($action == 'reject') { ?>

<input type="submit" value="回收站" class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete&recycle=1';"/>&nbsp;
<input type="submit" value="彻底删除" class="btn-r" onclick="if(confirm('确定要删除选中<?php echo $MOD['name'];?>吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete'}else{return false;}"/>&nbsp;

<?php } else if($action == 'recycle') { ?>

<input type="submit" value="彻底删除" class="btn-r" onclick="if(confirm('确定要删除选中<?php echo $MOD['name'];?>吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete'}else{return false;}"/>&nbsp;
<input type="submit" value="还 原" class="btn" onclick="if(confirm('确定要还原选中<?php echo $MOD['name'];?>吗？状态将被设置为已通过')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=restore'}else{return false;}"/>&nbsp;
<input type="submit" value="清 空" class="btn-r" onclick="if(confirm('确定要清空回收站吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=clear';}else{return false;}"/>&nbsp;

<?php } else { ?>

<input type="submit" value="更新信息" class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=update';"/>&nbsp;
<?php if($MOD['show_html']) { ?><input type="submit" value=" 生成网页 " class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=tohtml';"/>&nbsp; <?php } ?>
<input type="submit" value="回收站" class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete&recycle=1';"/>&nbsp;
<input type="submit" value="彻底删除" class="btn-r" onclick="if(confirm('确定要删除选中<?php echo $MOD['name'];?>吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete'}else{return false;}"/>&nbsp;
<input type="submit" value="移动分类" class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=move';"/>&nbsp;
<?php echo level_select('level', '设置级别为</option><option value="0">取消', 0, 'onchange="this.form.action=\'?moduleid='.$moduleid.'&file='.$file.'&action=level\';this.form.submit();"');?>

<?php } ?>

</div>
</form>
<?php echo $pages ? '<div class="pages">'.$pages.'</div>' : '';?>
<br/>
<script type="text/javascript">
$(function(){
	Menuon(<?php echo $menuid;?>);
	$('.thumb').on('error', function(e) {
		 $(this).attr('src', '<?php echo DT_STATIC;?>image/nopic60.png');
	});
});
</script>
<?php include tpl('footer');?>