<?php
defined('IN_DESTOON') or exit('Access Denied');
include tpl('header');
?>
<div class="tt">报价审核</div>
<?php if(isset($menus) && $menus) { show_menu($menus); } ?>

<form method="post" action="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=edit&itemid=<?php echo $item['itemid'];?>">
    <input type="hidden" name="submit" value="1"/>
    <input type="hidden" name="post[status]" id="quote-status" value="<?php echo $item['status'];?>"/>

    <div class="tt">基本信息</div>
    <table cellpadding="2" cellspacing="1" class="tb">
        <tr>
            <td class="tl">报价ID</td>
            <td class="tr"><?php echo $item['itemid'];?></td>
        </tr>
        <tr>
            <td class="tl">提交时间</td>
            <td class="tr"><?php echo timetodate($item['addtime'], 5);?></td>
        </tr>
        <tr>
            <td class="tl">当前状态</td>
            <td class="tr">
                <?php if($item['status'] == 0) { ?>
                    <span class="f_red">待审核</span>
                <?php } elseif($item['status'] == 1) { ?>
                    <span class="f_green">已通过</span>
                <?php } elseif($item['status'] == 2) { ?>
                    <span class="f_gray">已拒绝</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td class="tl">预约到店时间</td>
            <td class="tr">
                <?php if($item['appoint_time']) { ?>
                    <?php echo timetodate($item['appoint_time'], 5);?>
                <?php } else { ?>
                    <span class="f_gray">用户未填写</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td class="tl">回执链接</td>
            <td class="tr">
                <?php
                if(empty($MOD)) $MOD = cache_read('module-'.$moduleid.'.php');
                $receipt_url = $MOD['linkurl'].'quote_view.php?itemid='.$item['itemid'].'&token='.$item['receipt_token'];
                ?>
                <?php if($item['receipt_token']) { ?>
                    <input type="text" style="width:80%;" value="<?php echo $receipt_url;?>" onclick="this.select();" readonly/>
                    <span class="f_gray">（点击输入框即可全选复制发送给客户）</span>
                <?php } else { ?>
                    <span class="f_gray">尚未生成，请先点击下方按钮保存一次</span>
                <?php } ?>
            </td>
        </tr>
    </table>

    <div class="tt">客户 & 联系方式</div>
    <table cellpadding="2" cellspacing="1" class="tb">
        <tr>
            <td class="tl">客户姓名</td>
            <td class="tr"><?php echo $item['first_name'];?> <?php echo $item['last_name'];?></td>
        </tr>
        <tr>
            <td class="tl">邮箱</td>
            <td class="tr"><?php echo $item['email'];?></td>
        </tr>
        <tr>
            <td class="tl">手机号</td>
            <td class="tr">
                <?php if($item['mobile']) { ?>
                    <?php echo $item['mobile'];?>
                <?php } else { ?>
                    <span class="f_gray">未填写</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td class="tl">联系方式验证状态</td>
            <td class="tr">
                <?php if($item['verify_status']) { ?>
                    <span class="f_green">已通过</span>
                <?php } else { ?>
                    <span class="f_red">未验证</span>
                <?php } ?>
            </td>
        </tr>
    </table>

    <div class="tt">维修门店</div>
    <table cellpadding="2" cellspacing="1" class="tb">
        <tr>
            <td class="tl">门店名称</td>
            <td class="tr">
                <?php if($item['company_name']) { ?>
                    <?php echo $item['company_name'];?>
                <?php } else { ?>
                    <span class="f_gray">未选择门店</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td class="tl">门店地区</td>
            <td class="tr">
                <?php if($item['areaid']) { ?>
                    <?php echo area_pos($item['areaid'], ' / ');?>
                <?php } else { ?>
                    <span class="f_gray">无</span>
                <?php } ?>
            </td>
        </tr>
    </table>

    <div class="tt">设备 & 故障明细</div>
    <table cellpadding="2" cellspacing="1" class="tb">
        <tr>
            <th width="160">设备名称</th>
            <th width="100">catid</th>
            <th>故障列表</th>
        </tr>
        <?php if(!empty($devices)) { ?>
            <?php foreach($devices as $catid => $dev) { ?>
                <tr>
                    <td><?php echo $dev['name'];?></td>
                    <td><?php echo $catid;?></td>
                    <td>
                        <?php if(!empty($dev['faults'])) { ?>
                            <ul style="margin:0;padding-left:18px;">
                                <?php foreach($dev['faults'] as $f) { ?>
                                    <li>
                                        故障编号：<?php echo $f['num'];?>
                                        <?php if(!empty($f['name'])) { ?>，名称：<?php echo $f['name'];?><?php } ?>
                                        <?php if(isset($f['price'])) { ?>，参考价格：<?php echo $f['price'];?> 円<?php } ?>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } else { ?>
                            <span class="f_gray">无故障数据</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="3" align="center">设备数据解析失败</td>
            </tr>
        <?php } ?>
    </table>

    <div class="tt">颜色 & 故障描述 & 图片</div>
    <table cellpadding="2" cellspacing="1" class="tb">
        <tr>
            <td class="tl">设备颜色</td>
            <td class="tr">
                <?php if($item['color']) { ?>
                    <?php echo $item['color'];?>
                <?php } else { ?>
                    <span class="f_gray">未选择</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td class="tl">故障补充说明</td>
            <td class="tr">
                <?php if($item['fault_desc']) { ?>
                    <div style="max-height:200px;overflow:auto;"><?php echo nl2br($item['fault_desc']);?></div>
                <?php } else { ?>
                    <span class="f_gray">用户未填写</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td class="tl">设备图片</td>
            <td class="tr">
                <?php if(!empty($images)) { ?>
                    <?php foreach($images as $img) { ?>
                        <a href="<?php echo $img;?>" target="_blank">
                            <img src="<?php echo $img;?>" style="max-width:120px;max-height:120px;margin-right:5px;margin-bottom:5px;border:1px solid #ddd;padding:2px;"/>
                        </a>
                    <?php } ?>
                <?php } else { ?>
                    <span class="f_gray">未上传图片</span>
                <?php } ?>
            </td>
        </tr>
    </table>

    <div class="tt">金额 & 审核</div>
    <table cellpadding="2" cellspacing="1" class="tb">
        <tr>
            <td class="tl">故障原价合计</td>
            <td class="tr">
                <input type="text" size="10" value="<?php echo $item['total_fault_amount'];?>" readonly/>
                <span class="f_gray">系统根据故障配置计算，只读</span>
            </td>
        </tr>
        <tr>
            <td class="tl">优惠金额</td>
            <td class="tr">
                <input type="text" size="10" name="post[discount_amount]" value="<?php echo $item['discount_amount'];?>"/>
                <span class="f_gray">单位：円，可手动调整</span>
            </td>
        </tr>
        <tr>
            <td class="tl">最终报价金额</td>
            <td class="tr">
                <input type="text" size="10" name="post[final_amount]" value="<?php echo $item['final_amount'];?>"/>
                <span class="f_red">单位：円，用户最终看到的金额</span>
            </td>
        </tr>
        <tr>
            <td class="tl">管理员备注</td>
            <td class="tr">
                <textarea name="post[admin_note]" style="width:400px;height:80px;"><?php echo $item['admin_note'];?></textarea>
                <br/><span class="f_gray">备注内容会一并发到用户邮箱</span>
            </td>
        </tr>
    </table>

    <div class="sbt">
        <!-- 通过并发送邮件 -->
        <input type="submit" value="确认（通过并发送邮件）" class="btn"
               onclick="document.getElementById('quote-status').value='1';"/>
        &nbsp;&nbsp;
        <!-- 拒绝 -->
        <input type="submit" value="拒 绝" class="btn"
               onclick="if(confirm('确认将此报价标记为“拒绝”？')){document.getElementById('quote-status').value='2';}else{return false;}"/>
        &nbsp;&nbsp;
        <!-- 取消 -->
        <input type="button" value="取 消" class="btn" onclick="history.back(-1);"/>
    </div>

</form>

<?php include tpl('footer');?>
