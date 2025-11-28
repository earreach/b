<?php
defined('DT_ADMIN') or exit('Access Denied');
include tpl('header');
show_menu($menus);
?>
    <form method="post" action="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>">
        <input type="hidden" name="file" value="<?php echo $file;?>"/>
        <div class="sbox">
            <input type="text" size="30" name="kw" value="<?php echo $kw;?>" placeholder="请输入关键词" title="请输入关键词"/>&nbsp;
            <input type="submit" name="submit" value="搜 索11" class="btn"/>&nbsp;
            <input type="button" value="重 搜" class="btn" onclick="Go('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>');"/>&nbsp;
        </div>
    </form>
    <form method="post">
        <input type="hidden" name="forward" value="<?php echo $forward;?>"/>
        <?php if($parentid) {?>
            <div class="tt"><a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&parentid=<?php echo $SHUXING[$parentid]['parentid'];?>" title="返回上级"><?php echo $SHUXING[$parentid]['shuxingname'];?></a></div>
        <?php }?>
        <table cellspacing="0" class="tb ls">
            <tr>
                <th width="20"><input type="checkbox" onclick="checkall(this.form);" title="全选/反选"/></th>
                <th width="100">排序</th>
                <th width="100">ID</th>
                <th width="100">上级ID</th>
                <th width="200">类名</th>
                <th width="80">属性</th>
                <th width="40">新增</th>
                <th width="40">管理</th>
                <th></th>
            </tr>
            <?php foreach($DSHUXING as $k=>$v) {?>
                <tr align="center">
                    <td><input type="checkbox" name="shuxingids[]" value="<?php echo $v['shuxingid'];?>"/></td>
                    <td><input name="shuxing[<?php echo $v['shuxingid'];?>][listorder]" type="text" size="5" value="<?php echo $v['listorder'];?>"/></td>
                    <td>&nbsp;<?php echo $v['shuxingid'];?></td>
                    <td><input name="shuxing[<?php echo $v['shuxingid'];?>][parentid]" type="text" size="10" value="<?php echo $v['parentid'];?>"/></td>
                    <td><input name="shuxing[<?php echo $v['shuxingid'];?>][shuxingname]" type="text" size="20" value="<?php echo $v['shuxingname'];?>"/></td>
                    <td>&nbsp;<a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&parentid=<?php echo $v['shuxingid'];?>"><?php echo $v['childs'];?></a></td>
                    <td><a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=add&parentid=<?php echo $v['shuxingid'];?>"><img src="<?php echo DT_STATIC;?>admin/add.png" width="16" height="16" title="添加子属性" alt=""/></a>
                    </td>
                    <td><a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&parentid=<?php echo $v['shuxingid'];?>"><img src="<?php echo DT_STATIC;?>admin/child.png" width="16" height="16" title="管理属性，当前有<?php echo $v['childs'];?>个子属性" alt=""/></a></td>
                    <td></td>
                </tr>
            <?php }?>
            <tr>
                <td><input type="checkbox" onclick="checkall(this.form);" title="全选/反选"/></td>
                <td colspan="8">
                    <input type="submit" name="submit" value="保存修改" class="btn-g" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&parentid=<?php echo $parentid;?>&action=update'"/>&nbsp;&nbsp;
                    <input type="submit" value="删除选中" class="btn-r" onclick="if(confirm('确定要删除选中属性吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&parentid=<?php echo $parentid;?>&action=delete'}else{return false;}"/>&nbsp;&nbsp;
                    <?php if($parentid) {?>
                        <input type="botton" value="返回上级" class="btn" onclick="Go('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&parentid=<?php echo $SHUXING[$parentid]['parentid'];?>');"/>&nbsp;&nbsp;
                    <?php }?>
                    &nbsp;&nbsp;
                    分类总数:<strong class="f_red"><?php echo count($SHUXING);?></strong>&nbsp;&nbsp;
                    当前目录:<strong class="f_blue"><?php echo count($DSHUXING);?></strong>&nbsp;&nbsp;
                </td>
            </tr>
        </table>
    </form>
    <form method="post" action="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>">
        <div class="tt">属性快捷操作</div>
        <table cellspacing="0" class="tb">
            <tr align="center">
                <td>
                    <div style="float:left;padding:10px;">
                        <?php echo ajax_shuxing_select('shuxingid', '选择属性2211', $parentid, 'size="2" style="width:200px;height:160px;font-size:14px;"');?>
                        <?php echo 1;?>
                    </div>
                    <div style="float:left;">
                        <table class="ctb">
                            <tr>
                                <td><input type="submit" value="管理属性" class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&parentid='+Dd('shuxingid_1').value;"/></td>
                            </tr>
                            <tr>
                                <td><input type="submit" value="添加属性" class="btn" onclick="this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=add&parentid='+Dd('shuxingid_1').value;"/></td>
                            </tr>
                            <tr>
                                <td><input type="submit" value="删除属性" class="btn-r" onclick="if(confirm('确定要删除选中属性吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete&shuxingid='+Dd('shuxingid_1').value;}else{return false;}"/></td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
        </div>
    </form>
    <script type="text/javascript">Menuon(1);</script>
<?php include tpl('footer');?>