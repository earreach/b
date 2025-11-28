<?php
defined('DT_ADMIN') or exit('Access Denied');
include tpl('header');
show_menu($menus);
load('moment.css');
load('moment.js');
load('player.js');
load('url2video.js');
load('webuploader.min.js');
?>
<form method="post" action="?" id="dform" onsubmit="return check();">
<input type="hidden" name="moduleid" value="<?php echo $moduleid;?>"/>
<input type="hidden" name="file" value="<?php echo $file;?>"/>
<input type="hidden" name="action" value="<?php echo $action;?>"/>
<input type="hidden" name="itemid" value="<?php echo $itemid;?>"/>
<input type="hidden" name="forward" value="<?php echo $forward;?>"/>
<table cellspacing="0" class="tb">
<?php if($history) { ?>
<tr>
<td class="tl" style="background:#FDE7E7;"><span class="f_red">*</span> 审核提示</td>
<td style="background:#FDE7E7;">该信息存在修改记录，<a href="javascript:;" onclick="Dwidget('?file=history&mid=<?php echo $moduleid;?>&itemid=<?php echo $itemid;?>', '修改详情');" class="t">点击查看</a> 修改详情</td>
</tr>
<?php } ?>
<?php if($quote) { ?>
<tr id="quote">
<td class="tl">转发<?php echo $MOD['name'];?></td>
<td>
<a href="<?php echo userurl($quote['username'], 'file=space&mid='.$mid);?>" class="t" target="_blank">@<?php echo $quote['passport'];?></a> &nbsp; &nbsp; <a href="javascript:;" onclick="if(confirm('确定要取消转发<?php echo $MOD['name'];?>吗？')){$('#quoteid').val(0);$('#quote').hide();}"><img src="<?php echo DT_STATIC;?>image/ico-del.png" width="11" height="11" title="取消转发"/></a>
<div class="lh20"><?php echo $quote['introduce'];?> <a href="<?php echo $MOD['linkurl'];?><?php echo $quote['linkurl'];?>" class="t" target="_blank">查看原文</a></div>
</td>
</tr>
<?php } ?>
<?php if($topic) { ?>
<tr id="topic">
<td class="tl">参与话题</td>
<td>
<a href="<?php echo $MOD['linkurl'];?><?php echo rewrite('topic.php?itemid='.$topicid);?>" class="t" target="_blank">#<?php echo $topic['title'];?>#</a> &nbsp; &nbsp; <a href="javascript:;" onclick="if(confirm('确定要取消参与话题吗？')){$('#topicid').val(0);$('#topic').hide();}"><img src="<?php echo DT_STATIC;?>image/ico-del.png" width="11" height="11" title="取消参与"/></a></td>
</tr>
<?php } ?>
<?php echo $FD ? fields_html('<td class="tl">', '<td>', $item) : '';?>
<tr>
<td class="tl"><span class="f_red">*</span> <?php echo $MOD['name'];?>内容</td>
<td>
<div class="mm-editor">
<textarea name="post[content]" id="content" placeholder="写下这一刻的想法"><?php echo $content;?></textarea>
<div>
	<img src="<?php echo DT_STATIC;?>image/me-image.png" id="me-image" title="上传图片"/>
	<img src="<?php echo DT_STATIC;?>image/me-video.png" id="me-video" title="上传视频"/>
	<img src="<?php echo DT_STATIC;?>image/me-face.png" id="me-face" title="表情"/>
	<img src="<?php echo DT_STATIC;?>image/me-at.png" id="me-at" title="提到某人"/>
	<img src="<?php echo DT_STATIC;?>image/me-hash.png" id="me-hash" title="话题"/>
	<img src="<?php echo DT_STATIC;?>image/me-time.png" id="me-time" title="定时发布"/>
</div>
</div><span id="dcontent" class="f_red"></span>
</td>
</tr>

<tr<?php echo $thumbs ? '' : ' class="dsn"';?> id="mt-image">
<td class="tl"><span class="f_hid">*</span> 上传图片</td>
<td><?php include template('upload-album', 'chip');?></td>
</tr>
<tbody<?php echo $video ? '' : ' class="dsn"';?> id="mt-video">
<tr>
<td class="tl"><span class="f_hid">*</span> 上传视频</td>
<td><?php include template('upload-video', 'chip');?></td>
</tr>
</tbody>

<tr class="dsn" id="mt-face">
<td class="tl"><span class="f_hid">*</span> 插入表情</td>
<td><div class="mm-faces"><?php if(is_array($faces)) { foreach($faces as $k => $v) { ?><img src="<?php echo DT_PATH;?>file/face/<?php echo $k;?>.png" title="<?php echo $v;?>" onclick="me_into('<?php echo $k;?>', this.title);"/><?php } } ?></div></td>
</tr>

<tr class="dsn" id="mt-at">
<td class="tl"><span class="f_hid">*</span> 提到某人</td>
<td><input type="text" id="at" size="40" placeholder="输入会员名或昵称"/><img src="<?php echo DT_STATIC;?>image/ico-fadd.png" title="选择好友" class="jc" onclick="Dwidget(AJPath+'?moduleid=2&action=choose&job=friend&from=moment&fid=at&key=passport', '选择好友', 980, 600);"/><input type="button" value="插入" class="btn-b mm-insert" style="width:56px;height:30px;" onclick="me_into(Dd('at').value, 'at');"/></td>
</tr>

<tr class="dsn" id="mt-hash">
<td class="tl"><span class="f_hid">*</span> 插入话题</td>
<td><input type="text" id="hash" size="40" placeholder="输入话题"/><img src="<?php echo DT_STATIC;?>image/ico-new.png" title="选择话题" class="jc" onclick="Dwidget(AJPath+'?moduleid=<?php echo $moduleid;?>&action=choose&job=topic&from=moment&fid=hash', '选择话题', 980, 600);"/><input type="button" value="插入" class="btn-b mm-insert" style="width:56px;height:30px;" onclick="me_into(Dd('hash').value, 'hash');"/></td>
</tr>

<tr class="dsn" id="mt-time">
<td class="tl"><span class="f_hid">*</span> 发布时间</td>
<td><?php echo dcalendar('post[addtime]', $addtime, '-', 1);?></td>
</tr>

<tr>
<td class="tl"><span class="f_hid">*</span> 转发ID</td>
<td>
<input name="post[quoteid]" type="text" id="quoteid" size="10" value="<?php echo $quoteid;?>"/> &nbsp; 
话题ID <input name="post[topicid]" type="text" id="topicid" size="10" value="<?php echo $topicid;?>"/> &nbsp; 
模块ID <input name="post[mid]" type="text" id="mid" size="10" value="<?php echo $mid;?>"/> &nbsp; 
信息ID <input name="post[tid]" type="text" id="tid" size="10" value="<?php echo $tid;?>"/> &nbsp; 
</td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 外部链接</td>
<td><input name="post[linkto]" type="text" id="linkto" size="70" value="<?php echo $linkto;?>"/> <span id="dlinkto" class="f_red"></span></td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 所属分类</td>
<td><?php echo $_admin == 1 ? category_select('post[catid]', '选择分类', $catid, $moduleid) : ajax_category_select('post[catid]', '选择分类', $catid, $moduleid);?> <span id="dcatid" class="f_red"></span></td>
</tr>
<?php if($CP) { ?>
<script type="text/javascript">
var property_catid = <?php echo $catid;?>;
var property_itemid = <?php echo $itemid;?>;
var property_admin = 1;
</script>
<?php load('property.js');?>
<tbody id="load_property" style="display:none;">
<tr><td></td><td></td></tr>
</tbody>
<?php } ?>
<tr>
<td class="tl"><span class="f_hid">*</span> <?php echo $MOD['name'];?>标题</td>
<td><input name="post[title]" type="text" id="title" size="70" value="<?php echo $title;?>"/> <?php echo level_select('post[level]', '级别', $level);?> <?php echo dstyle('post[style]', $style);?> <span id="dtitle" class="f_red"></span></td>
</tr>
<tr>
<td class="tl"><span class="f_red">*</span> 会员名</td>
<td><input name="post[username]" type="text" size="20" value="<?php echo $username;?>" id="username"/> &nbsp; <img src="<?php echo DT_STATIC;?>image/ico-user.png" width="16" height="16" title="会员资料" class="c_p" onclick="_user(Dd('username').value);"/> &nbsp; <span id="dusername" class="f_red"></span></td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> <?php echo $MOD['name'];?>状态</td>
<td>
<label><input type="radio" name="post[status]" value="3" <?php if($status == 3) echo 'checked';?>/> 通过</label>
<label><input type="radio" name="post[status]" value="2" <?php if($status == 2) echo 'checked';?>/> 待审</label>
<label><input type="radio" name="post[status]" value="4" <?php if($status == 4) echo 'checked';?>/> 待发</label>
<label><input type="radio" name="post[status]" value="1" <?php if($status == 1) echo 'checked';?> onclick="if(this.checked) Dd('note').style.display='';"/> 拒绝</label>
<label><input type="radio" name="post[status]" value="0" <?php if($status == 0) echo 'checked';?>/> 删除</label>
</td>
</tr>
<tr id="note" style="display:<?php echo $status==1 ? '' : 'none';?>">
<td class="tl"><span class="f_red">*</span> 拒绝理由</td>
<td><input name="post[note]" type="text"  size="40" value="<?php echo $note;?>"/></td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 公开程度</td>
<td>
<select name="post[open]">
<option value="1"<?php if($open == 1) echo ' selected';?>>公开</option>
<option value="2"<?php if($open == 2) echo ' selected';?>>粉丝可见</option>
<option value="3"<?php if($open == 3) echo ' selected';?>>好友可见</option>
<option value="0"<?php if($open == 0) echo ' selected';?>>自己可见</option>
</select>&nbsp;
</td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 评论功能</td>
<td>
<select name="post[comment]">
<option value="1"<?php if($comment == 1) echo ' selected';?>>开放</option>
<option value="2"<?php if($comment == 2) echo ' selected';?>>筛选</option>
<option value="0"<?php if($comment == 0) echo ' selected';?>>关闭</option>
</select>&nbsp;
</td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 浏览次数</td>
<td><input name="post[hits]" type="text" size="10" value="<?php echo $hits;?>"/></td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 内容收费</td>
<td><input name="post[fee]" type="text" size="10" value="<?php echo $fee;?>"/><?php tips('不填或填0表示继承模块设置价格，-1表示不收费<br/>大于0的数字表示具体收费价格');?>
</td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 内容模板</td>
<td><?php echo tpl_select('show', $module, 'post[template]', '默认模板', $template, 'id="template"');?><?php tips('如果没有特殊需要，一般不需要选择<br/>系统会自动继承分类或模块设置');?></td>
</tr>
<?php if($MOD['show_html']) { ?>
<tr>
<td class="tl"><span class="f_hid">*</span> 自定义文件路径</td>
<td><input type="text" size="70" name="post[filepath]" value="<?php echo $filepath;?>" id="filepath"/>&nbsp;<input type="button" value="重名检测" onclick="ckpath(<?php echo $moduleid;?>, <?php echo $itemid;?>);" class="btn"/>&nbsp;<?php tips('可以包含目录和文件 例如 destoon/about.html<br/>请确保目录和文件名合法且可写入，否则可能生成失败');?>&nbsp; <span id="dfilepath" class="f_red"></span></td>
</tr>
<?php } ?>
</table>
<div class="sbt"><input type="submit" name="submit" value="<?php echo $action == 'edit' ? '修 改' : '添 加';?>" class="btn-g"/>&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="<?php echo $action == 'edit' ? '返 回' : '取 消';?>" class="btn" onclick="Go('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>');"/></div>
</form>
<?php load('clear.js'); ?>
<?php if($action == 'add' && in_array($moduleid, explode(',', $DT['fetch_module']))) { ?>
<form method="post" action="?">
<input type="hidden" name="moduleid" value="<?php echo $moduleid;?>"/>
<input type="hidden" name="file" value="<?php echo $file;?>"/>
<input type="hidden" name="action" value="<?php echo $action;?>"/>
<div class="tt">单页采编</div>
<table cellspacing="0" class="tb">
<tr>
<td class="tl"><span class="f_hid">*</span> 目标网址</td>
<td><input name="url" type="text" size="80" value="<?php echo $url;?>"/>&nbsp;&nbsp;<input type="submit" value=" 获 取 " class="btn"/>&nbsp;&nbsp;<input type="button" value=" 管理规则 " class="btn" onclick="Dwidget('?file=fetch', '管理规则');"/></td>
</tr>
</table>
</form>
<?php } ?>
<script type="text/javascript">
function check() {
	var l;
	var f;
	f = 'content';
	l = Dd(f).value.length;
	if(l < 1) {
		Dmsg('请填写内容', f);
		return false;
	}
	var v = Dd(f).value;
	if(v.indexOf('#') != -1 && substr_count(v, '#')%2 != 0) {
		Dmsg('<?php echo $L['pass_ht'];?>', f);
		return false;
	}
	/*
	if(v.indexOf('##') != -1) {
		Dmsg('<?php echo $L['pass_ht2'];?>', f);
		return false;
	}
	*/
	if(v.indexOf('@ ') != -1) {
		Dmsg('<?php echo $L['pass_at1'];?>', f);
		return false;
	}
	if(v.indexOf('@@') != -1) {
		Dmsg('<?php echo $L['pass_at2'];?>', f);
		return false;
	}
	if(v.indexOf('@') != -1 && substr_count(v, '@') > substr_count(v, ' ')) {
		Dmsg('<?php echo $L['pass_at'];?>', f);
		return false;
	}
	<?php echo $FD ? fields_js() : '';?>
	<?php echo $CP ? property_js() : '';?>
	return true;
}
$(function(){
	me_init();
	/*
	$('#topic-kw').on('keyup paste blur', function(e) {
		mm_list_topic(<?php echo $moduleid;?>);
	});
	*/
});
</script>
<script type="text/javascript">Menuon(<?php echo $menuid;?>);</script>
<?php include tpl('footer');?>