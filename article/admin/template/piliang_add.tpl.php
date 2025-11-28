<?php
defined('DT_ADMIN') or exit('Access Denied');
// var_dump(tpl('header'));die();
include tpl('header');
show_menu($menus);
?>
<?php load('webuploader.min.js');?>

<form method="post" action="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>" onsubmit="return Dcheck();" enctype="multipart/form-data">
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
            <td>
                请选父级属性id的txt文件
            </td>
            <td>
                <input type="file" name="fu" id="fileToUpload1" >
            </td>
        </tr>
        <tr>
            <td>
                请选择新增属性的txt文件
            </td>
            <td>
                <input type="file" name="zi" id="fileToUpload2" >
            </td>
        </tr>


        <tr>
            <td>
                <input type="submit" value="上传文件" name="submit" onclick="yanzheng()">
            </td>
        </tr>

<!--    这一大堆干啥的？-->

        <tr>
            <td class="tl"><span class="f_red">*</span> 文件地址</td>
            <td><input name="post[fileurl]" id="fileurl" type="text" size="70" value="<?php echo $fileurl;?>"/>
                <span class="upl">
<span id="file-picker"><img src="<?php echo DT_STATIC;?>image/ico-upl.png" title="上传"/></span>
<img src="<?php echo DT_STATIC;?>image/ico-view.png" title="预览" onclick="_preview(Dd('fileurl').value);"/>
<img src="<?php echo DT_STATIC;?>image/ico-del.png" title="删除" onclick="Dd('fileurl').value='';$('#file-progress').html('');"/>
</span>
                <span id="file-progress" class="f_gray"></span> <span id="dfileurl" class="f_red"></span>
                <script type="text/javascript">
                    // alert (UPPath)
                    <?php if(strpos($DT_MBS, 'IE') === false) { ?>
                    var fileu;
                    $(function(){
                        fileu = WebUploader.create({
                            auto: true,
                            server: UPPath+'?moduleid=<?php echo $moduleid;?>&action=webuploader&from=file',
                            pick: '#file-picker',
                            accept: {
                                title: 'Files',
                                extensions: 'txt',
                                mimeTypes: '*/*'
                            },
                            fileNumLimit: 1,
                            resize: false
                        });
                        fileu.on('beforeFileQueued', function(file) {
                            var exts = fileu.options.accept[0].extensions;
                            if((','+exts).indexOf(','+ext(file.name)) == -1) {
                                alert(L['upload_ext']+ext(file.name)+' '+L['upload_allow']+exts);
                                return false;
                            }
                        });
                        fileu.on('fileQueued', function(file) {
                            $('#file-progress').html('0%');
                        });
                        fileu.on('uploadProgress', function(file, percentage) {
                            var p = parseInt(percentage * 100);
                            if(p >= 100) p = 100;
                            $('#file-progress').html(p+'%');
                        });
                        fileu.on( 'uploadSuccess', function(file, data) {
                            if(data.error) {
                                Dmsg(data.message, 'fileurl6666666');
                            } else {
                                $('#file-progress').html('100%');
                                $('#fileurl').val(data.url);
                                initd(data.size);
                            }
                        });
                        fileu.on( 'uploadError', function(file, data) {
                            Dmsg(data.message, 'fileurl333333333');
                        });
                        fileu.on('uploadComplete', function(file) {
                            $('#file-progress').html('100%');
                            window.setTimeout(function() {$('#file-progress').html('');}, 1000);
                        });
                        window.setTimeout(function() {fileu.refresh();}, 1000);
                        window.setTimeout(function() {fileu.refresh();}, 2000);
                    });
                    <?php } else { ?>
                    $(function(){
                        $('#file-picker').click(function() {
                            Dfile(<?php echo $moduleid;?>, Dd('fileurl').value, 'fileurl', '<?php echo $MOD['upload'];?>');
                        });
                    });
                    <?php } ?>
                </script>
            </td>
        </tr>


        <!--    这一大堆干啥的？-->





        <!--<tr>-->
        <!--<td class="tl"><span class="f_hid">*</span> 分类名称</td>-->
        <!--<td><textarea name="shuxing[shuxingname]"  id="shuxingname" style="width:200px;height:100px;overflow:visible;"></textarea><?php tips('允许批量添加，一行一个，点回车换行');?></td>-->
        <!--</tr>-->





    </table>

    <!--<div class="sbt"><input type="submit" name="submit" value="添 加" class="btn-g"/>&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="取 消" class="btn" onclick="Go('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&parentid=<?php echo $parentid;?>');"/>-->
    <!--</div>-->

</form>
<script>
    //   function yanzheng(){
    //       alert  $('#fileToUpload1').value
    //     }

</script>