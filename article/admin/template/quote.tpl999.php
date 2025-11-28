<?php
defined('IN_DESTOON') or exit('Access Denied');
include tpl('header');
?>
<div class="tt">报价管理</div>
<?php if(isset($menus) && $menus) { show_menu($menus); } ?>

<form action="?" method="get">
    <input type="hidden" name="moduleid" value="<?php echo $moduleid;?>"/>
    <input type="hidden" name="file" value="<?php echo $file;?>"/>
    <div class="sbox">
        <table cellpadding="2" cellspacing="1">
            <tr>
                <td>
                    状态：
                    <select name="status">
                        <option value="-1"<?php if($status==-1) echo ' selected';?>>全部</option>
                        <option value="0"<?php if($status==0)  echo ' selected';?>>待审核</option>
                        <option value="1"<?php if($status==1)  echo ' selected';?>>已通过</option>
                        <option value="2"<?php if($status==2)  echo ' selected';?>>已拒绝</option>
                    </select>
                    &nbsp;
                    门店：
                    <select name="company_id">
                        <option value="0">全部门店</option>
                        <?php if(!empty($companys)) { foreach($companys as $c) { ?>
                            <option value="<?php echo $c['userid'];?>"<?php if($company_id==$c['userid']) echo ' selected';?>><?php echo $c['company'];?></option>
                        <?php } } ?>
                    </select>
                    &nbsp;
                    关键词：
                    <input type="text" size="20" name="keyword" value="<?php echo htmlspecialchars($keyword);?>" placeholder="姓名/邮箱/电话"/>
                    &nbsp;
                    提交日期：
                    <input type="text" name="fromdate" id="fromdate" value="<?php echo $fromdate;?>" size="10" onfocus="ca_show(this, this, '');" readonly/> 至
                    <input type="text" name="todate" id="todate" value="<?php echo $todate;?>" size="10" onfocus="ca_show(this, this, '');" readonly/>
                    &nbsp;
                    预约日期：
                    <input type="text" name="appointdate" id="appointdate" value="<?php echo $appointdate;?>" size="10" onfocus="ca_show(this, this, '');" readonly/>
                    &nbsp;
                    <input type="submit" value="搜 索" class="btn"/>
                    &nbsp;
                    <input type="button" value="重 置" class="btn" onclick="Go('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>');"/>
                    &nbsp;
                    <?php if($items) { ?>
                        <input type="button" value="导出当前结果为 Excel" class="btn" onclick="export_quote();"/>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </div>
</form>

<script type="text/javascript">
    function export_quote() {
        var url = '?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=export';
        url += '&status='      + encodeURIComponent(document.getElementsByName('status')[0].value);
        url += '&company_id='  + encodeURIComponent(document.getElementsByName('company_id')[0].value);
        url += '&keyword='     + encodeURIComponent(document.getElementsByName('keyword')[0].value);
        url += '&fromdate='    + encodeURIComponent(document.getElementById('fromdate').value);
        url += '&todate='      + encodeURIComponent(document.getElementById('todate').value);
        url += '&appointdate=' + encodeURIComponent(document.getElementById('appointdate').value);
        Go(url);
    }
</script>

<table cellpadding="2" cellspacing="1" class="tb">
    <tr>
        <th width="40">ID</th>
        <th width="120">提交时间</th>
        <th width="120">客户姓名</th>
        <th width="160">邮箱</th>
        <th width="120">电话</th>
        <th width="160">维修门店</th>
        <th width="100">原价合计</th>
        <th width="110">优惠码抵扣</th>
        <th width="110">优惠金额（人工）</th>
        <th width="110">最终金额</th>
        <th width="80">状态</th>
        <th width="120">预约时间</th>
        <th width="80">操作</th>
    </tr>
    <?php if(!empty($lists)) { ?>
        <?php foreach($lists as $k => $v) { ?>
            <tr align="center">
                <td><?php echo $v['itemid'];?></td>
                <td><?php echo $v['addtime_str'];?></td>
                <td><?php echo $v['customer_name'];?></td>
                <td><?php echo $v['email'];?></td>
                <td><?php echo $v['mobile'];?></td>
                <td>
                    <?php if(!empty($v['company_name'])) { ?>
                        <?php echo $v['company_name'];?>
                    <?php } else { ?>
                        <span class="f_gray">未选择</span>
                    <?php } ?>
                </td>
                <td><?php echo $v['total_fault_amount_str'];?></td>
                <td><?php echo $v['coupon_amount_str'];?></td>
                <td><?php echo $v['discount_amount_str'];?></td>
                <td><span class="f_red"><?php echo $v['final_amount_str'];?></span></td>
                <td>
                    <?php if($v['status'] == 0) { ?>
                        <span class="f_red">待审核</span>
                    <?php } else if($v['status'] == 1) { ?>
                        <span class="f_green">已通过</span>
                    <?php } else if($v['status'] == 2) { ?>
                        <span class="f_gray">已拒绝</span>
                    <?php } else { ?>
                        <span class="f_gray">未知</span>
                    <?php } ?>
                </td>
                <td>
                    <?php if($v['appointtime_str']) { ?>
                        <?php echo $v['appointtime_str'];?>
                    <?php } else { ?>
                        <span class="f_gray">未填</span>
                    <?php } ?>
                </td>
                <td>
                    <a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=edit&itemid=<?php echo $v['itemid'];?>">审核/查看</a>
                </td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td colspan="13" align="center">暂时没有报价记录</td>
        </tr>
    <?php } ?>
</table>

<div class="pages"><?php echo $pages;?></div>

<?php include tpl('footer');?>
