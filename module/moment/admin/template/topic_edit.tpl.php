<?php
defined('DT_ADMIN') or exit('Access Denied');
include tpl('header');
show_menu($menus);
?>
<form method="post" action="?" id="dform" onsubmit="return check();">
<input type="hidden" name="moduleid" value="<?php echo $moduleid;?>"/>
<input type="hidden" name="file" value="<?php echo $file;?>"/>
<input type="hidden" name="action" value="<?php echo $action;?>"/>
<input type="hidden" name="itemid" value="<?php echo $itemid;?>"/>
<input type="hidden" name="forward" value="<?php echo $forward;?>"/>
<table cellspacing="0" class="tb">
<tr>
<td class="tl"><span class="f_red">*</span> 所属分类</td>
<td><?php echo $_admin == 1 ? category_select('post[catid]', '选择分类', $catid, $moduleid) : ajax_category_select('post[catid]', '选择分类', $catid, $moduleid);?> <span id="dcatid" class="f_red"></span></td>
</tr>
<tr>
<td class="tl"><span class="f_red">*</span> 话题标题</td>
<td><input name="post[title]" type="text" id="title" size="70" value="<?php echo $title;?>"/> <?php echo level_select('post[level]', '级别', $level);?> <?php echo dstyle('post[style]', $style);?> <span id="dtitle" class="f_red"></span></td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 话题简介</td>
<td><textarea name="post[content]" id="content" class="dsn"><?php echo $content;?></textarea>
<?php echo deditor($moduleid, 'content', 'Destoon', '100%', 350);?><br/><span id="dcontent" class="f_red"></span>
</td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> SEO标题</td>
<td><input name="post[seo_title]" type="text" size="70" value="<?php echo $seo_title;?>"/></td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> SEO关键词</td>
<td><input name="post[seo_keywords]" type="text" size="70" value="<?php echo $seo_keywords;?>"/></td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> SEO描述</td>
<td><input name="post[seo_description]" type="text" size="70" value="<?php echo $seo_description;?>"/></td>
</tr>
<tr>
<td class="tl"><span class="f_red">*</span> 主持人</td>
<td><input name="post[username]" type="text" size="20" value="<?php echo $username;?>" id="username"/> &nbsp; <img src="<?php echo DT_STATIC;?>image/ico-user.png" width="16" height="16" title="会员资料" class="c_p" onclick="_user(Dd('username').value);"/> &nbsp; <span id="dusername" class="f_red"></span></td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 话题状态</td>
<td>
<label><input type="radio" name="post[status]" value="3" <?php if($status == 3) echo 'checked';?>/> 通过</label>
<label><input type="radio" name="post[status]" value="2" <?php if($status == 2) echo 'checked';?>/> 待审</label>
<label><input type="radio" name="post[status]" value="1" <?php if($status == 1) echo 'checked';?>/> 拒绝</label>
<label><input type="radio" name="post[status]" value="0" <?php if($status == 0) echo 'checked';?>/> 删除</label>
</td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 添加时间</td>
<td><?php echo dcalendar('post[addtime]', $addtime, '-', 1);?></td>
</tr>
</table>
<div class="sbt"><input type="submit" name="submit" value="<?php echo $action == 'edit' ? '修 改' : '添 加';?>" class="btn-g"/>&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="<?php echo $action == 'edit' ? '返 回' : '取 消';?>" class="btn" onclick="Go('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>');"/></div>
</form>
<?php load('clear.js'); ?>
<script type="text/javascript">
function check() {
	var l;
	var f;
	f = 'catid_1';
	if(Dd(f).value == 0) {
		Dmsg('请选择所属分类', 'catid', 1);
		return false;
	}
	f = 'title';
	l = Dd(f).value.length;
	if(l < 2) {
		Dmsg('标题最少2字，当前已输入'+l+'字', f);
		return false;
	}
	f = 'username';
	l = Dd(f).value.length;
	if(l < 2) {
		Dmsg('请填写主持人', f);
		return false;
	}
	return true;
}
</script>
<script type="text/javascript">Menuon(<?php echo $menuid;?>);</script>
<?php include tpl('footer');?>