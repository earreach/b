<?php
defined('IN_DESTOON') or exit('Access Denied');
include tpl('header');
if(!isset($MOD) || !$MOD) $MOD = cache_read('module-'.$moduleid.'.php');
?>
<div class="tt">报价统计</div>
<?php if(isset($menus) && $menus) { show_menu($menus); } ?>

<form action="?" method="get">
    <input type="hidden" name="moduleid" value="<?php echo $moduleid;?>"/>
    <input type="hidden" name="file" value="<?php echo $file;?>"/>
    <input type="hidden" name="action" value="stats"/>
    <div class="sbox">
        <table cellpadding="2" cellspacing="1">
            <tr>
                <td>
                    门店：
                    <select name="company_id">
                        <option value="0">全部门店</option>
                        <?php if(!empty($companys)) { foreach($companys as $c) { ?>
                        <option value="<?php echo $c['userid'];?>"<?php if($company_id==$c['userid']) echo ' selected';?>><?php echo $c['company'];?></option>
                        <?php } } ?>
                    </select>
                    &nbsp;
                                提交日期：
                    <input type="date"
                           name="fromdate"
                           id="fromdate"
                           value="<?php echo $fromdate;?>"
                           style="width:140px;"/>
                     至
                    <input type="date"
                           name="todate"
                           id="todate"
                           value="<?php echo $todate;?>"
                           style="width:140px;"/>
                    &nbsp;
                    维修状态：
                    <select name="repair_status">
                        <option value="-1"<?php if($repair_status==-1) echo ' selected';?>>全部</option>
                        <option value="0"<?php if($repair_status==0)  echo ' selected';?>>未设置</option>
                        <option value="1"<?php if($repair_status==1)  echo ' selected';?>>已预约</option>
                        <option value="2"<?php if($repair_status==2)  echo ' selected';?>>维修中</option>
                        <option value="3"<?php if($repair_status==3)  echo ' selected';?>>已完修</option>
                        <option value="4"<?php if($repair_status==4)  echo ' selected';?>>已取消</option>
                    </select>
                    &nbsp;
                    <input type="submit" value="统 计" class="btn"/>
                </td>
            </tr>
        </table>
    </div>
</form>

<table cellpadding="2" cellspacing="1" class="tb">
    <tr align="center">
        <th>门店</th>
        <th>报价数量</th>
        <th>已通过</th>
        <th>用户已接受</th>
        <th>暂不接受</th>
        <th>已完修</th>
        <th>报价金额合计</th>
        <th>实收金额合计</th>
        <th>接受率</th>
        <th>完修率</th>
    </tr>
    <?php if(!empty($stats)) { ?>
        <?php foreach($stats as $v) { ?>
        <tr align="right">
            <td align="left"><?php echo $v['company_name'];?></td>
            <td><?php echo $v['total_quotes'];?></td>
            <td><?php echo $v['passed_count'];?></td>
            <td><?php echo $v['accept_count'];?></td>
            <td><?php echo $v['reject_count'];?></td>
            <td><?php echo $v['finished_count'];?></td>
            <td><?php echo $v['total_final_amount_str'];?> 円</td>
            <td><?php echo $v['total_repair_amount_str'];?> 円</td>
            <td><?php echo $v['accept_rate'];?></td>
            <td><?php echo $v['finish_rate'];?></td>
        </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td colspan="10" align="center">暂无统计数据</td>
        </tr>
    <?php } ?>

    <?php if(!empty($total_row) && $total_row['total_quotes'] > 0) { ?>
    <tr align="right">
        <td align="left"><strong>合计</strong></td>
        <td><strong><?php echo $total_row['total_quotes'];?></strong></td>
        <td><strong><?php echo $total_row['passed_count'];?></strong></td>
        <td><strong><?php echo $total_row['accept_count'];?></strong></td>
        <td><strong><?php echo $total_row['reject_count'];?></strong></td>
        <td><strong><?php echo $total_row['finished_count'];?></strong></td>
        <td><strong><?php echo $total_row['total_final_amount_str'];?> 円</strong></td>
        <td><strong><?php echo $total_row['total_repair_amount_str'];?> 円</strong></td>
        <td><strong><?php echo $total_row['accept_rate'];?></strong></td>
        <td><strong><?php echo $total_row['finish_rate'];?></strong></td>
    </tr>
    <?php } ?>
</table>

<br/>
<div class="tt">说明</div>
<div class="sbox">
    <ul>
        <li>“报价数量”：符合当前筛选条件的报价总数。</li>
        <li>“已通过”：后台审核状态为“已通过”的数量。</li>
        <li>“用户已接受/暂不接受”：来自用户回执页面的确认结果。</li>
        <li>“已完修”：维修状态为“已完修”的数量。</li>
        <li>“接受率” = 用户已接受 ÷ 已通过；“完修率” = 已完修 ÷ 报价数量。</li>
    </ul>
</div>

<?php include tpl('footer'); ?>
