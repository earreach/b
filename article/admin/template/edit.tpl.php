<?php
defined('DT_ADMIN') or exit('Access Denied');
include tpl('header');
show_menu($menus);
?>
<form action="?" target="_blank" id="check_title">
<input type="hidden" name="moduleid" value="<?php echo $moduleid;?>"/>
<input type="hidden" name="file" value="<?php echo $file;?>"/>
<input type="hidden" name="kw" value="" id="kw"/>
</form>
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
<tr>
<td class="tl"><span class="f_red">*</span> 所属分类</td>
<td><?php echo $_admin == 1 ? category_select('post[catid]', '选择分类', $catid, $moduleid) : ajax_category_select('post[catid]', '选择分类', $catid, $moduleid);?>&nbsp;&nbsp;<label><input type="checkbox" name="post[islink]" value="1" id="islink" onclick="_islink();"<?php if($islink) echo ' checked';?>/> 外部链接</label> <span id="dcatid" class="f_red"></span></td>
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
<td class="tl"><span class="f_red">*</span> <?php echo $MOD['name'];?>标题</td>
<td><input name="post[title]" type="text" id="title" size="70" value="<?php echo $title;?>"/> <?php echo level_select('post[level]', '级别', $level, 'id="level"');?> <?php echo dstyle('post[style]', $style);?>&nbsp;&nbsp;<input type="button" value="标题检测" onclick="check_title();" class="btn"/> <span id="dtitle" class="f_red"></span></td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 标题图片</td>
<td>
<input type="hidden" name="post[thumb]" id="thumb" value="<?php echo $thumb;?>"/>
<div class="thumbu">
<div><img src="<?php if($thumb) { ?><?php echo $thumb;?><?php } else { ?><?php echo DT_STATIC;?>image/upload-image.png<?php } ?>" id="pthumb" onerror="this.src='<?php echo DT_STATIC;?>image/upload-image.png';Dd('thumb').value='';" onclick="if(this.src.indexOf('upload-image.png') == -1){_preview(this.src, 1);}else{Dthumb(<?php echo $moduleid;?>,Dd('level').value==2 ? 440 : <?php echo $MOD['thumb_width'];?>,Dd('level').value==2 ? 330 : <?php echo $MOD['thumb_height'];?>, Dd('thumb').value);}"/></div>
<p><img src="<?php echo DT_STATIC;?>image/ico-upl.png" width="11" height="11" title="上传" onclick="Dthumb(<?php echo $moduleid;?>,Dd('level').value==2 ? 440 : <?php echo $MOD['thumb_width'];?>,Dd('level').value==2 ? 330 : <?php echo $MOD['thumb_height'];?>, Dd('thumb').value);"/><img src="<?php echo DT_STATIC;?>image/ico-del.png" width="11" height="11" title="删除" onclick="Dd('thumb').value='';Dd('pthumb').src='<?php echo DT_STATIC;?>image/upload-image.png';"/></p>
</div><span id="dthumb" class="f_red"></span>
</td>
</tr>
<tr id="link" style="display:<?php echo $islink ? '' : 'none';?>;">
<td class="tl"><span class="f_red">*</span> 链接地址</td>
<td><input name="post[linkurl]" type="text" id="linkurl" size="70" value="<?php echo $linkurl;?>"/> &nbsp; <img src="<?php echo DT_STATIC;?>image/ico-link.png" width="11" height="11" title="打开链接" class="c_p" onclick="if(Dd('linkurl').value.length>10){window.open('<?php echo gourl('?url=');?>'+encodeURIComponent(Dd('linkurl').value));}else{Dmsg('请输入链接地址', 'linkurl');}"/> &nbsp; <span id="dlinkurl" class="f_red"></span></td>
</tr>
<tbody id="basic" style="display:<?php echo $islink ? 'none' : '';?>;">
<?php echo $FD ? fields_html('<td class="tl">', '<td>', $item) : '';?>
<tr>
<td class="tl"><span class="f_red">*</span> <?php echo $MOD['name'];?>内容m.a,a,e</td>
<td><textarea name="post[content]" id="content" class="dsn"><?php echo $content;?></textarea>
<?php echo deditor($moduleid, 'content', $MOD['editor'], '98%', 350);?><br/><span id="dcontent" class="f_red"></span>
</td>
</tr>
<tr>
<td class="tl" height="30"><span class="f_hid">*</span> 内容选项</td>
<td>
<a href="javascript:pagebreak();Ds('subtitle');"><img src="<?php echo DT_STATIC;?>admin/pagebreak.png" align="absmiddle"/> 插入分页符</a>&nbsp; &nbsp;
<label><input type="checkbox" name="post[save_remotepic]" value="1"<?php if($MOD['save_remotepic']) echo ' checked';?>/> 下载远程图片</label>&nbsp; &nbsp;
<label><input type="checkbox" name="post[clear_link]" value="1"<?php if($MOD['clear_link']) echo ' checked';?>/> 清除链接</label>&nbsp; &nbsp;
截取内容 <input name="post[introduce_length]" type="text" size="2" value="<?php echo $MOD['introduce_length']?>"/> 字符至简介&nbsp; &nbsp;
设置内容第 <input name="post[thumb_no]" type="text" size="2" value="1"/> 张图片为标题图&nbsp; &nbsp;
插入投票 <input name="post[voteid]" type="text" size="10" value="<?php echo $voteid;?>" id="voteid"/> &nbsp; <img src="<?php echo DT_STATIC;?>image/ico-sort.png" width="11" height="11" title="查看投票ID" class="c_p" onclick="Dwidget('?moduleid=3&file=vote&job=select', '投票列表');"/> &nbsp; <?php tips('请填写投票ID，多个ID请用空格隔开');?>
</td>
</tr>
<tbody id="subtitle" style="display:<?php echo $pagebreak ? '' : 'none';?>;">
<tr>
<td class="tl"><span class="f_hid">*</span> 分页标题</td>
<td>
<textarea name="post[subtitle]" style="width:90%;height:45px;"><?php echo $subtitle;?></textarea>
<br/>1行一个分页标题，按回车换行
</td>
</tr>
</tbody>
<tr>
<td class="tl"><span class="f_hid">*</span> <?php echo $MOD['name'];?>简介</td>
<td><textarea name="post[introduce]" style="width:90%;height:45px;"><?php echo $introduce;?></textarea></td>
</tr>
<tr height="30">
<td class="tl"><span class="f_hid">*</span> <?php echo $MOD['name'];?>作者</td>
<td><input type="text" size="10" name="post[author]" value="<?php echo $author;?>" id="author"/> &nbsp; <img src="<?php echo DT_STATIC;?>image/ico-user.png" width="16" height="16" title="选择常用作者" class="c_p" onclick="Dwidget('?moduleid=<?php echo $moduleid;?>&action=author', '选择作者');"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" size="12" name="post[copyfrom]" value="<?php echo $copyfrom;?>" id="copyfrom" placeholder="来源" title="来源"/>&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" size="25" name="post[fromurl]" value="<?php echo $fromurl;?>" id="fromurl" placeholder="链接" title="链接"/> &nbsp; <img src="<?php echo DT_STATIC;?>image/ico-link.png" width="11" height="11" title="选择常用来源" class="c_p" onclick="Dwidget('?moduleid=<?php echo $moduleid;?>&action=from', '选择来源');"/></td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 标签(Tag)</td>
<td>
<input name="post[tag]" type="text" size="80" value="<?php echo $tag;?>" id="tag"/>
<?php if($DT['split_appcode']) { ?> &nbsp; <a href="javascript:;" onclick="CloudSplit('title', 'tag');" class="t">[生成]</a> &nbsp; <?php } ?> 
<?php tips('多个标签请用空格隔开');?>
</td>
</tr>
<tr>
<td class="tl"><span class="f_red">*</span> 会员名</td>
<td><input name="post[username]" type="text"  size="20" value="<?php echo $username;?>" id="username"/> &nbsp; <img src="<?php echo DT_STATIC;?>image/ico-user.png" width="16" height="16" title="会员资料" class="c_p" onclick="_user(Dd('username').value);"/> &nbsp; <span id="dusername" class="f_red"></span></td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> <?php echo $MOD['name'];?>状态</td>
<td>
<label><input type="radio" name="post[status]" value="3" <?php if($status == 3) echo 'checked';?> id="status_3"/> 通过</label>
<label><input type="radio" name="post[status]" value="2" <?php if($status == 2) echo 'checked';?> id="status_2"/> 待审</label>
<label><input type="radio" name="post[status]" value="4" <?php if($status == 4) echo 'checked';?> id="status_4"/> 待发</label>
<label><input type="radio" name="post[status]" value="1" <?php if($status == 1) echo 'checked';?> onclick="if(this.checked) Dd('note').style.display='';" id="status_1"/> 拒绝</label>
<label><input type="radio" name="post[status]" value="0" <?php if($status == 0) echo 'checked';?> id="status_0"/> 删除</label>
</td>
</tr>
<tr id="note" style="display:<?php echo $status==1 ? '' : 'none';?>">
<td class="tl"><span class="f_red">*</span> 拒绝理由</td>
<td><input name="post[note]" type="text"  size="40" value="<?php echo $note;?>"/></td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 添加时间</td>
<td><?php echo dcalendar('post[addtime]', $addtime, '-', 1);?></td>
</tr>
<tr>
<td class="tl"><span class="f_hid">*</span> 所在地区</td>
<td><?php echo ajax_area_select('post[areaid]', '请选择', $areaid);?></td>
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
<input type="hidden" name="catid" value="<?php echo $catid;?>"/>
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
function check_title() {
	if(Dd('title').value.length < 2) {
		Dmsg('请填写标题', 'title');
	} else {
		Dwidget('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&kw='+encodeURIComponent(Dd('title').value), '标题检测');
	}
}
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
	if(Dd('islink').checked) {
		f = 'linkurl';
		l = Dd(f).value.length;
		if(l < 12) {
			Dmsg('请输入正确的链接地址', f);
			return false;
		}
	} else {
		f = 'content';
		l = EditorLen();
		if(l < 5) {
			Dmsg('内容最少5字，当前已输入'+l+'字', f);
			return false;
		}
	}
	<?php echo $FD ? fields_js() : '';?>
	<?php echo $CP ? property_js() : '';?>
	return true;
}
</script>
<script type="text/javascript">Menuon(<?php echo $menuid;?>);</script>
<?php include tpl('footer');?>