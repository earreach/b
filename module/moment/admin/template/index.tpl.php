<?php
defined('DT_ADMIN') or exit('Access Denied');
include tpl('header');
show_menu($menus);
load('moment.css');
load('moment.js');
load('player.js');
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
<?php echo category_select('catid', '所属分类', $catid, $moduleid);?>&nbsp;
<?php echo $order_select;?>&nbsp;
<input type="text" name="psize" value="<?php echo $pagesize;?>" size="2" class="t_c" placeholder="条/页" title="条/页"/>&nbsp;
<input type="submit" value="搜 索" class="btn"/>&nbsp;
<input type="button" value="重 置" class="btn" onclick="Go('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=<?php echo $action;?>');"/>
</td>
</tr>
<tr>
<td>
&nbsp;
<?php echo $module_select;?>&nbsp;
<input type="text" size="10" name="tid" value="<?php echo $tid;?>" placeholder="信息ID" title="信息ID"/>&nbsp;
<input type="text" size="10" name="itemid" value="<?php echo $itemid;?>" placeholder="<?php echo $MOD['name'];?>ID" title="<?php echo $MOD['name'];?>ID"/>&nbsp;
<input type="text" size="10" name="quoteid" value="<?php echo $quoteid;?>" placeholder="转发ID" title="转发ID"/>&nbsp;
<input type="text" size="10" name="topicid" value="<?php echo $topicid;?>" placeholder="话题ID" title="话题ID"/>&nbsp;
<label><input type="checkbox" name="thumb" value="1"<?php echo $thumb ? ' checked' : '';?>/>图片&nbsp;</label>
<label><input type="checkbox" name="video" value="1"<?php echo $video ? ' checked' : '';?>/>视频&nbsp;</label>
<label><input type="checkbox" name="link" value="1"<?php echo $link ? ' checked' : '';?>/>外链&nbsp;</label>
<label><input type="checkbox" name="topic" value="1"<?php echo $topic ? ' checked' : '';?>/>话题&nbsp;</label>
<label><input type="checkbox" name="quote" value="1"<?php echo $quote ? ' checked' : '';?>/>转发&nbsp;</label>
</td>
</tr>
<tr>
<td>
&nbsp;<select name="datetype">
<option value="edittime"<?php if($datetype == 'edittime') echo ' selected';?>>更新时间</option>
<option value="addtime"<?php if($datetype == 'addtime') echo ' selected';?>>发布时间</option>
</select>&nbsp;
<?php echo dcalendar('fromdate', $fromdate, '-', 1);?> 至 <?php echo dcalendar('todate', $todate, '-', 1);?>&nbsp;
<select name="open">
<option value="-1"<?php if($open == -1) echo ' selected';?>>公开程度</option>
<option value="1"<?php if($open == 1) echo ' selected';?>>公开</option>
<option value="2"<?php if($open == 2) echo ' selected';?>>粉丝可见</option>
<option value="3"<?php if($open == 3) echo ' selected';?>>好友可见</option>
<option value="0"<?php if($open == 0) echo ' selected';?>>自己可见</option>
</select>&nbsp;
<select name="comment">
<option value="-1"<?php if($comment == -1) echo ' selected';?>>评论</option>
<option value="1"<?php if($comment == 1) echo ' selected';?>>开放</option>
<option value="2"<?php if($comment == 2) echo ' selected';?>>筛选</option>
<option value="0"<?php if($comment == 0) echo ' selected';?>>关闭</option>
</select>&nbsp;
<input type="text" name="username" value="<?php echo $username;?>" size="10" placeholder="会员名" title="会员名 双击显示会员资料" ondblclick="if(this.value){_user(this.value);}"/>&nbsp;
<input type="text" size="10" name="passport" value="<?php echo $passport;?>" placeholder="会员昵称" title="会员昵称"/>&nbsp;
</td>
</tr>
</table>
</form>
<form method="post">
<table cellspacing="0" class="tb">
<tr>
<th width="20"><input type="checkbox" onclick="checkall(this.form);" title="全选/反选"/></th>
<th width="16"><a href="javascript:;" onclick="Dq('order','<?php echo $order == 5 ? 6 : 5;?>');"><img src="<?php echo DT_STATIC;?>image/ico-<?php echo $order == 6 ? 'asc' : ($order == 5 ? 'dsc' : 'ord');?>.png" width="11" height="11"/></a></th>
<th width="100">会员</th>
<th>内容</th>
<th width="40">修改</th>
</tr>
<?php foreach($lists as $k=>$v) {?>
<tr align="center">
<td><input type="checkbox" name="itemid[]" value="<?php echo $v['itemid'];?>"/></td>
<td><?php if($v['level']) {?><a href="javascript:;" onclick="Dq('level','<?php echo $v['level'];?>');"><img src="<?php echo DT_STATIC;?>admin/level_<?php echo $v['level'];?>.gif" title="<?php echo $v['level'];?>级" alt=""/></a><?php } ?></td>
<td valign="top">
<img src="<?php echo useravatar($v['username']);?>" width="64" height="64" class="avatar c_p" onclick="_user('<?php echo $v['username'];?>');"/>
<div style="line-height:24px;padding-top:6px;">
<a href="javascript:Dq('username','<?php echo $v['username'];?>');"><?php echo $v['passport'] ? $v['passport'] : $v['username'];?></a> 
</div>
</td>
<td valign="top" align="left">
<div class="mm-info">
<span class="f_r c_p">
<span onclick="Dwidget('?file=like&action=like&mid=<?php echo $moduleid;?>&tid=<?php echo $v['itemid'];?>', '点赞记录');">点赞 (<?php echo $v['likes'];?>)</span> &nbsp;|&nbsp; 
<span onclick="Dwidget('?moduleid=3&file=comment&mid=<?php echo $moduleid;?>&itemid=<?php echo $v['itemid'];?>', '[<?php echo $v['alt'];?>] 评论列表');">评论 (<?php echo $v['comments'];?>)</span> &nbsp;|&nbsp; 
<span onclick="Dwidget('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&quoteid=<?php echo $v['itemid'];?>', '[<?php echo $v['alt'];?>] 转发记录');">转发 (<?php echo $v['quotes'];?>)</span> &nbsp;|&nbsp; 
<span onclick="Dwidget('?moduleid=2&file=favorite&mid=<?php echo $moduleid;?>&tid=<?php echo $v['itemid'];?>', '[<?php echo $v['alt'];?>] 收藏记录');">收藏 (<?php echo $v['favorites'];?>)</span> &nbsp;|&nbsp; 
<span onclick="Dwidget('?moduleid=3&file=guestbook&mid=<?php echo $moduleid;?>&tid=<?php echo $v['itemid'];?>', '举报记录');">举报 (<?php echo $v['reports'];?>)</span> &nbsp;|&nbsp; 
<a href="<?php echo $v['linkurl'];?>" target="_blank"><span class="f_gray">原文</span></a> &nbsp; 
</span>
<span onclick="Dq('datetype','addtime',0);Dq('date',this.title);" class="c_p" title="<?php echo timetodate($v['addtime'], 5);?>"><?php echo timetoread($v['addtime'], 5);?></span>
 &nbsp; <a href="javascript:Dq('catid',<?php echo $v['catid'];?>);"><span class="f_gray"><?php echo $v['catname'];?></span></a>
</div>
<?php if($v['topic']) { ?><div class="mm-topic"><a href="javascript:Dq('topicid','<?php echo $v['topicid'];?>');"><b>#<?php echo $v['topic'];?>#</b></a></div><?php } ?>
<div class="mm-text" id="content-<?php echo $v['itemid'];?>"><?php echo $v['introduce'];?><?php if($v['more']) { ?><i onclick="mm_more(<?php echo $moduleid;?>, <?php echo $v['itemid'];?>);">全文</i><?php } ?></div>
<?php if($v['linkto']) { ?><div class="mm-link"><a href="<?php echo gourl($v['linkto']);?>" target="_blank"><i>网页链接</i></a></div><?php } ?>
<?php if($v['pics'] || $v['video']) { ?>
	<div class="mm-pics">
	<ul id="thumbs-<?php echo $v['itemid'];?>">
	<?php if($v['video']) { ?><li><img src="<?php echo DT_STATIC;?>image/play.gif" onclick="mm_pic_show('<?php echo $v['itemid'];?>', this);" data-video="<?php echo $v['video'];?>"/></li><?php } ?>
	<?php if($v['pics']) { foreach($v['pics'] as $t) { ?>
	<?php if($t) { ?><li><img src="<?php echo $t;?>" onclick="mm_pic_show('<?php echo $v['itemid'];?>', this);"/></li><?php } ?>
	<?php } } ?>
	</ul>
	<p id="thumbshow-<?php echo $v['itemid'];?>" onclick="mm_pic_next('<?php echo $v['itemid'];?>');"></p>
	</div>
<?php } ?>

<?php if($v['quoteid']) { ?>
	<?php $q = get_quote($v['quoteid']);?>
	<?php if($q) { ?>
		<?php if($q['open']==1) { ?>
			<div class="mm-quote">
			<div class="mm-user"><a href="<?php echo $MODULE[$moduleid]['linkurl'];?><?php echo $q['linkurl'];?>" target="_blank" title="原文"><span></span></a><a href="<?php echo userurl($q['username'], 'file=space&mid='.$moduleid);?>" target="_blank"><b>@<?php if($q['passport']) { ?><?php echo $q['passport'];?><?php } else { ?><?php echo $q['username'];?><?php } ?></b></a></div>
			<div class="mm-time"><span title="<?php echo timetodate($q['addtime'], 5);?>"><?php echo timetoread($q['addtime'], 5);?></span></div>
			<?php if($q['topic']) { ?><div class="mm-topic"><a href="<?php echo $MOD['linkurl'];?><?php echo rewrite('topic.php?itemid='.$q['topicid']);?>" target="_blank"><b>#<?php echo $q['topic'];?>#</b></a></div><?php } ?>
			<div class="mm-text" id="content-<?php echo $q['itemid'];?>-<?php echo $v['itemid'];?>"><?php echo $q['introduce'];?><?php if($q['more']) { ?><i onclick="mm_more(<?php echo $moduleid;?>, <?php echo $q['itemid'];?>, <?php echo $v['itemid'];?>);">全文</i><?php } ?></div>
			<?php if($q['linkto']) { ?><div class="mm-link"><a href="<?php echo gourl($q['linkto']);?>" target="_blank"><i>网页链接</i></a></div><?php } ?>
			<?php if($q['pics'] || $q['video']) { ?>
			<div class="mm-pics">
			<ul id="thumbs-<?php echo $v['itemid'];?>-<?php echo $q['itemid'];?>">
			<?php if($q['video']) { ?><li><img src="<?php echo DT_STATIC;?>image/play.gif" onclick="mm_pic_show('<?php echo $v['itemid'];?>-<?php echo $q['itemid'];?>', this);" data-video="<?php echo $q['video'];?>"/></li><?php } ?>
			<?php if($q['pics']) { ?>
			<?php if(is_array($q['pics'])) { foreach($q['pics'] as $p) { ?>
			<?php if($p) { ?><li><img src="<?php echo $p;?>" onclick="mm_pic_show('<?php echo $v['itemid'];?>-<?php echo $q['itemid'];?>', this);"/></li><?php } ?>
			<?php } } ?>
			<?php } ?>
			</ul>
			<p id="thumbshow-<?php echo $v['itemid'];?>-<?php echo $q['itemid'];?>" onclick="mm_pic_next('<?php echo $v['itemid'];?>-<?php echo $q['itemid'];?>');"></p>
			</div>
			<?php } ?>
			</div>
		<?php } else { ?>
			<div class="mm-lost">原文已隐藏</div>
		<?php } ?>
	<?php } else { ?>
		<div class="mm-lost">原文已删除</div>
	<?php } ?>
<?php } ?>
</td>
<td valign="top"><a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=edit&itemid=<?php echo $v['itemid'];?>"><img src="<?php echo DT_STATIC;?>admin/edit.png" width="16" height="16" title="修改" alt=""/></a></td>
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
<input type="submit" value="彻底删除" class="btn-r" onclick="if(confirm('确定要删除选中<?php echo $MOD['name'];?>吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete'}else{return false;}"/>

<?php } else if($action == 'expire') { ?>
<input type="submit" value="刷新待发" class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=expire&refresh=1';"/>&nbsp;
<input type="submit" value="回收站" class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete&recycle=1';"/>&nbsp;
<input type="submit" value="彻底删除" class="btn-r" onclick="if(confirm('确定要删除选中<?php echo $MOD['name'];?>吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete'}else{return false;}"/>&nbsp;

<?php } else if($action == 'reject') { ?>

<input type="submit" value="回收站" class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete&recycle=1';"/>&nbsp;
<input type="submit" value="彻底删除" class="btn-r" onclick="if(confirm('确定要删除选中供应吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete'}else{return false;}"/>

<?php } else if($action == 'recycle') { ?>

<input type="submit" value="彻底删除" class="btn-r" onclick="if(confirm('确定要删除选中供应吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete'}else{return false;}"/>&nbsp;
<input type="submit" value="还 原" class="btn" onclick="if(confirm('确定要还原选中<?php echo $MOD['name'];?>吗？状态将被设置为已通过')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=restore'}else{return false;}"/>&nbsp;
<input type="submit" value="清 空" class="btn-r" onclick="if(confirm('确定要清空回收站吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=clear';}else{return false;}"/>

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
<script type="text/javascript">Menuon(<?php echo $menuid;?>);</script>
<?php include tpl('footer');?>