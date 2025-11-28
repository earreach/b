<?php
defined('DT_ADMIN') or exit('Access Denied');
include tpl('header');
show_menu($menus);
?>
    <form method="post" action="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&parentid=<?php echo $parentid;?>" onsubmit="return Dcheck();">
        <input type="hidden" name="file" value="<?php echo $file;?>"/>
        <input type="hidden" name="action" value="<?php echo $action;?>"/>
        <input type="hidden" name="shuxing[parentid]" value="<?php echo $parentid;?>"/>
        <table cellspacing="0" class="tb">
            <?php if($parentid) {?>
                <tr>
                    <td class="tl"><span class="f_hid">*</span> 上级地区</td>
                    <td><?php echo $SHUXING[$parentid]['shuxingname'];?></td>
                </tr>
            <?php }?>
            <tr>
                <td class="tl"><span class="f_hid">*</span> 分类名称</td>
                <td><textarea name="shuxing[shuxingname]"  id="shuxingname" style="width:200px;height:100px;overflow:visible;"></textarea><?php tips('允许批量添加，一行一个，点回车换行');?></td>
            </tr>
        </table>
        <div class="sbt"><input type="submit" name="submit" value="添 加" class="btn-g"/>&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="取 消" class="btn" onclick="Go('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&parentid=<?php echo $parentid;?>');"/></div>
    </form>
    <script type="text/javascript">
        function Dcheck() {
            if(Dd('shuxing').value == '') {
                Dtip('请填写分类名称。允许批量添加，一行一个，点回车换行');
                Dd('shuxing').focus();
                return false;
            }
            return true;
        }
    </script>
    <script type="text/javascript">Menuon(0);</script>
<?php include tpl('footer');?>