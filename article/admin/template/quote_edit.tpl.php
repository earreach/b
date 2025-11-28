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
            <td class="tr"><?php echo timetodate($item['addtime'], 6);?></td>
        </tr>
        <tr>
            <td class="tl">客户姓名</td>
            <td class="tr">
                <?php echo $item['first_name'];?> <?php echo $item['last_name'];?>
            </td>
        </tr>
        <tr>
            <td class="tl">邮箱</td>
            <td class="tr"><?php echo $item['email'];?></td>
        </tr>
        <tr>
            <td class="tl">电话</td>
            <td class="tr"><?php echo $item['mobile'] ? $item['mobile'] : '<span class="f_gray">未填写</span>';?></td>
        </tr>
        <tr>
            <td class="tl">维修门店</td>
            <td class="tr">
                <?php if(isset($item['company_name']) && $item['company_name']) { ?>
                    <?php echo $item['company_name'];?>
                <?php } else { ?>
                    <span class="f_gray">未选择门店（company_id=<?php echo $item['company_id'];?>）</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td class="tl">优惠码</td>
            <td class="tr">
                <?php if($item['discount_code']) { ?>
                    <?php echo $item['discount_code'];?>
                <?php } else { ?>
                    <span class="f_gray">未使用优惠码</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td class="tl">联系方式验证</td>
            <td class="tr">
                <?php if($item['verify_status']) { ?>
                    <span class="f_green">已验证</span>
                <?php } else { ?>
                    <span class="f_red">未验证</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td class="tl">当前状态</td>
            <td class="tr">
                <?php if($item['status'] == 0) { ?>
                    <span class="f_red">待审核</span>
                <?php } else if($item['status'] == 1) { ?>
                    <span class="f_green">已通过</span>
                <?php } else if($item['status'] == 2) { ?>
                    <span class="f_gray">已拒绝</span>
                <?php } else { ?>
                    <span class="f_gray">未知</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td class="tl">预约到店时间</td>
            <td class="tr">
                <?php if(!empty($item['appoint_time'])) { ?>
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
                <a href="<?php echo $receipt_url;?>" target="_blank"><?php echo $receipt_url;?></a>
            </td>
        </tr>
        <tr>
            <td class="tl">用户确认状态</td>
            <td class="tr">
                <?php if($item['user_confirm_status'] == 1) { ?>
                    <span class="f_green">已接受报价</span>
                    <?php if($item['user_confirm_time']) { ?>
                        （<?php echo timetodate($item['user_confirm_time'], 5);?>）
                    <?php } ?>
                <?php } else if($item['user_confirm_status'] == 2) { ?>
                    <span class="f_red">已标记为暂不接受</span>
                    <?php if($item['user_confirm_time']) { ?>
                        （<?php echo timetodate($item['user_confirm_time'], 5);?>）
                    <?php } ?>
                <?php } else { ?>
                    <span class="f_gray">用户尚未在回执页确认</span>
                <?php } ?>
            </td>
        </tr>

    </table>

    <div class="tt">设备 & 故障明细</div>
    <table cellpadding="2" cellspacing="1" class="tb">
        <tr>
            <th width="160">设备名称</th>
            <th width="120">设备颜色</th>
            <th>故障列表</th>
        </tr>
        <?php
        // 解码 devices JSON
        $devices = array();
        if($item['devices']) {
            $tmp = json_decode($item['devices'], true);
            if($tmp && is_array($tmp)) $devices = $tmp;
        }

        if($devices) {
            foreach($devices as $catid => $d) {
                $device_name = isset($d['name']) ? $d['name'] : '';
                $faults = isset($d['faults']) && is_array($d['faults']) ? $d['faults'] : array();
                ?>
                <tr>
                    <td><?php echo $device_name;?></td>
                    <td>
                        <?php
                        // 设备颜色：优先用 devices 里的 color，没有则退回整单的 color 字段
                        $device_color = '';
                        if(isset($d['color']) && $d['color'] !== '') {
                            $device_color = $d['color'];
                        } else if(!empty($item['color'])) {
                            $device_color = $item['color'];
                        }
                        echo $device_color ? $device_color : '-';
                        ?>
                    </td>


                    <td>
                        <?php if($faults) { ?>
                            <?php foreach($faults as $f) {
                                $num   = isset($f['num']) ? $f['num'] : '';
                                $name  = isset($f['name']) ? $f['name'] : '';
                                $price = isset($f['price']) ? floatval($f['price']) : 0;
                                ?>
                                故障编号：<?php echo $num;?> ，名称：<?php echo $name;?> ，参考价格：<?php echo $price;?> 円<br/>
                            <?php } ?>
                        <?php } else { ?>
                            <span class="f_gray">无故障数据</span>
                        <?php } ?>
                        <?php if($item['user_confirm_status'] == 2 && !empty($d['reject_reason'])) { ?>
                            <div style="margin-top:4px;color:#c00;">
                                用户拒绝理由：<?php echo nl2br(htmlspecialchars($d['reject_reason']));?>
                            </div>
                        <?php } ?>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="3" class="f_gray" style="text-align:center;">暂无设备数据</td>
            </tr>
            <?php
        }
        ?>
    </table>

    <?php
    // 金额汇总：故障原价合计 / 优惠码抵扣金额 / 优惠金额 / 最终报价金额
    // total_fault_amount = 故障原价合计
    // quote_amount       = 只减优惠码后的金额
    // discount_amount    = 人工优惠金额（后台填写）
    // final_amount       = 最终报价金额 = 原价合计 - 优惠码抵扣金额 - 优惠金额

    // 金额统一做一次清洗，防止数据里已经带千分位逗号
    $total_fault_amount = 0.00;
    if (isset($item['total_fault_amount'])) {
        $tmp = str_replace(',', '', (string)$item['total_fault_amount']);
        $total_fault_amount = floatval($tmp);
    }

    $quote_amount = 0.00;
    if (isset($item['quote_amount'])) {
        $tmp = str_replace(',', '', (string)$item['quote_amount']);
        $quote_amount = floatval($tmp);
    }

    $manual_discount = 0.00;
    if (isset($item['discount_amount'])) {
        $tmp = str_replace(',', '', (string)$item['discount_amount']);
        $manual_discount = floatval($tmp);
    }


    // 优惠码抵扣金额：总价 - quote_amount（quote_amount 只减优惠码）
    $coupon_amount = 0.00;
    if($total_fault_amount > 0 && $quote_amount >= 0 && $total_fault_amount >= $quote_amount) {
        $coupon_amount = $total_fault_amount - $quote_amount;
    }



    // 最终报价金额 = 故障原价合计 - 优惠码抵扣金额 - 优惠金额（人工优惠）
    $final_amount = $total_fault_amount - $coupon_amount - $manual_discount;
    if($final_amount < 0) $final_amount = 0.00;
    ?>
    <div style="margin-top:10px;padding:8px 10px;border:1px solid #f0f0f0;background:#fcfcfc;">
        <div style="margin-bottom:4px;">
            <strong>故障补充说明：</strong>
            <?php if($item['fault_desc']) { ?>
                <?php echo nl2br($item['fault_desc']);?>
            <?php } else { ?>
                <span class="f_gray">用户未填写</span>
            <?php } ?>
        </div>

        <strong>故障原价合计：</strong>


        <span id="total_fault_amount" data-value="<?php echo $total_fault_amount;?>">
            <?php echo number_format($total_fault_amount, 2);?> 円
        </span>
        &nbsp;&nbsp;

        <strong>优惠码抵扣金额：</strong>
        <span id="coupon_amount" data-value="<?php echo $coupon_amount;?>">
            <?php echo number_format($coupon_amount, 2);?> 円
        </span>
        &nbsp;&nbsp;

        <strong>优惠金额：</strong>
        <input type="text" id="admin_discount" name="post[discount_amount]" size="10"
               value="<?php echo $manual_discount > 0 ? $manual_discount : '0';?>"
               oninput="this.value=this.value.replace(/[^0-9.]/g,'');updateFinalAmount();"/>
        円
        &nbsp;&nbsp;

        <strong>最终报价金额：</strong>
        <span id="final_amount" class="f_red">






            <?php echo number_format($final_amount, 2);?> 円
        </span>
        <!-- 隐藏字段：提交时保存最终报价金额 -->
        <input type="hidden" name="post[final_amount]" id="final_amount_input"
               value="<?php echo number_format($final_amount, 2, '.', ''); ?>"/>
        <span class="f_gray">（最终报价 = 故障原价合计 - 优惠码抵扣金额 - 优惠金额）</span>
    </div>

    <script type="text/javascript">
        function updateFinalAmount() {
            var totalSpan  = document.getElementById('total_fault_amount');
            var couponSpan = document.getElementById('coupon_amount');
            var input      = document.getElementById('admin_discount');
            if(!totalSpan || !couponSpan || !input) return;

            var total  = parseFloat(totalSpan.getAttribute('data-value') || '0');
            var coupon = parseFloat(couponSpan.getAttribute('data-value') || '0');
            var manual = parseFloat(input.value || '0');
            if (isNaN(manual)) manual = 0;

            var finalVal = total - coupon - manual;
            if(finalVal < 0) finalVal = 0;

            var finalSpan = document.getElementById('final_amount');
            if(finalSpan) finalSpan.innerHTML = finalVal.toFixed(2) + ' 円';

            var finalInput = document.getElementById('final_amount_input');
            if(finalInput) finalInput.value = finalVal.toFixed(2);
        }

        if(typeof window !== 'undefined') {
            window.setTimeout(updateFinalAmount, 0);
        }
    </script>



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
                    <?php echo nl2br($item['fault_desc']);?>
                <?php } else { ?>
                    <span class="f_gray">用户未填写</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td class="tl">上传图片</td>
            <td class="tr">
                <?php
                if($item['images']) {
                    $imgs = explode(',', $item['images']);
                    foreach($imgs as $img) {
                        $img = trim($img);
                        if(!$img) continue;
                        ?>
                        <a href="<?php echo $img;?>" target="_blank">
                            <img src="<?php echo $img;?>" style="max-width:120px;max-height:120px;margin-right:6px;border:1px solid #ddd;padding:2px;"/>
                        </a>
                        <?php
                    }
                } else {
                    ?>
                    <span class="f_gray">用户未上传图片</span>
                    <?php
                }
                ?>
            </td>
        </tr>
    </table>
    <div class="tt">维修进度 & 实收金额</div>
    <table cellpadding="2" cellspacing="1" class="tb">
        <tr>
            <td class="tl">维修状态</td>
            <td class="tr">
                <select name="post[repair_status]">
                    <option value="0"<?php if($item['repair_status']==0) echo ' selected';?>>未设置</option>
                    <option value="1"<?php if($item['repair_status']==1) echo ' selected';?>>已预约</option>
                    <option value="2"<?php if($item['repair_status']==2) echo ' selected';?>>维修中</option>
                    <option value="3"<?php if($item['repair_status']==3) echo ' selected';?>>已完修</option>
                    <option value="4"<?php if($item['repair_status']==4) echo ' selected';?>>已取消</option>
                </select>
                <span class="f_gray">仅后台使用，用于标记维修进度。</span>
            </td>
        </tr>
        <tr>
            <td class="tl">实收金额</td>
            <td class="tr">
                <input type="text" name="post[repair_amount]" size="10"
                       value="<?php echo $item['repair_amount'] ? $item['repair_amount'] : '0.00';?>"/> 円
                <span class="f_gray">客户实际支付金额，可为 0。</span>
            </td>
        </tr>
        <tr>
            <td class="tl">完修时间</td>
            <td class="tr">
                <?php if(!empty($item['repair_time'])) { ?>
                    <?php echo timetodate($item['repair_time'], 5);?>
                <?php } else { ?>
                    <span class="f_gray">当状态第一次设为“已完修”时自动记录。</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td class="tl">维修备注</td>
            <td class="tr">
                <textarea name="post[repair_note]" style="width:400px;height:60px;"><?php echo $item['repair_note'];?></textarea>
            </td>
        </tr>
    </table>
    <?php
    // === 报价邮件默认文案（仅用于审核页显示） ===

    // 已经发送过几次报价邮件（尽量用 receipt_send_count，缺少就退回用 notify_sent）
    $send_times = 0;
    if(isset($item['receipt_send_count'])) {
        $send_times = intval($item['receipt_send_count']);
    } else if(isset($item['notify_sent']) && $item['notify_sent']) {
        $send_times = 1;
    }

    // 邮件类型提示：首次 / 调整后第 N 次
    if($send_times > 0) {
        $mail_type_text = '调整后报价邮件（第 '.($send_times + 1).' 次发送）';
    } else {
        $mail_type_text = '首次报价邮件';
    }

    $customer_name    = $item['first_name'].$item['last_name'];
    $appoint_str      = $item['appoint_time'] ? timetodate($item['appoint_time'], 5) : '未填写';

    $final_amount_str = number_format($item['final_amount']);
//    var_dump($final_amount_str);
//    die();
    $last_confirm     = isset($item['user_confirm_status']) ? intval($item['user_confirm_status']) : 0;

    // 默认邮件标题
    if($send_times > 0) {
        $mail_subject_default = '调整后的维修报价通知 - '.$customer_name;
    } else {
        $mail_subject_default = '维修报价通知 - '.$customer_name;
    }

    // 默认邮件正文（纯文本，用换行，真正发送时再 nl2br）
    $body_lines   = array();
    $body_lines[] = '您好，'.$customer_name.'：';

    if($send_times > 0) {
        if($last_confirm == 2) {
            // 上一次明确“暂不接受”
            $body_lines[] = '根据您上次“暂不接受”的反馈，我们对报价做了调整。';
            $body_lines[] = '当前新的报价金额为：'.$final_amount_str.' 円。';
        } else {
            // 只是长时间没回复或其他情况
            $body_lines[] = '我们对您的报价信息进行了更新。';
            $body_lines[] = '当前报价金额为：'.$final_amount_str.' 円。';
        }
    } else {
        // 第一次发送
        $body_lines[] = '感谢您提交维修报价请求，我们已为您的设备完成评估。';
        $body_lines[] = '当前报价金额为：'.$final_amount_str.' 円。';
    }

    $body_lines[] = '预约到店时间：'.$appoint_str;
    $body_lines[] = '详细报价内容请通过下方链接查看。';

    $mail_body_default = implode("\n\n", $body_lines);
    ?>

    <div class="tt">管理员备注 & 审核操作</div>
    <table cellpadding="2" cellspacing="1" class="tb">
        <tr>
            <td class="tl">管理员备注</td>
            <td class="tr">
                <textarea name="post[admin_note]" style="width:400px;height:80px;"><?php echo $item['admin_note'];?></textarea>
                <br/><span class="f_gray">将随审核通过邮件一并发给客户。为空时邮件中不显示“管理员备注”一行。</span>
            </td>
        </tr>
        <tr>
            <td class="tl">审核状态</td>
            <td class="tr">
                <select name="temp_status" id="temp-status" onchange="document.getElementById('quote-status').value=this.value;">
                    <option value="0" <?php if($item['status']==0) echo 'selected';?>>待审核</option>
                    <option value="1" <?php if($item['status']==1) echo 'selected';?>>通过</option>
                    <option value="2" <?php if($item['status']==2) echo 'selected';?>>拒绝</option>
                </select>
                <span class="f_gray">上面的按钮（确认/拒绝）会自动设置状态。</span>
            </td>
        </tr>

        <!-- 邮件类型（只读提示） -->
        <tr>
            <td class="tl">邮件类型</td>
            <td class="tr">
                <span class="f_gray"><?php echo $mail_type_text;?></span>
            </td>
        </tr>

        <!-- 邮件标题（本次发送可编辑） -->
        <tr>
            <td class="tl">邮件标题</td>
            <td class="tr">
                <input type="text" name="mail_subject" style="width:400px;"
                       value="<?php echo htmlspecialchars($mail_subject_default, ENT_QUOTES, 'UTF-8');?>"/>
                <br/><span class="f_gray">默认根据当前报价生成，如需更正式或个性化，可以在这里修改本次发送的标题。</span>
            </td>
        </tr>

        <!-- 邮件正文（本次发送可编辑，纯文本，发送时自动换行） -->
        <tr>
            <td class="tl">邮件正文</td>
            <td class="tr">
                <textarea name="mail_body" style="width:400px;height:150px;"><?php echo htmlspecialchars($mail_body_default, ENT_QUOTES, 'UTF-8');?></textarea>
                <br/><span class="f_gray">
                默认会包含“报价金额 / 预约时间”等信息，发送时系统会自动在邮件末尾附上报价回执链接。这里可以按需补充或修改本次发送给客户的正文内容。
            </span>
            </td>
        </tr>
    </table>




    <div class="sbt">
        <!-- 确认（通过并发送邮件） -->
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
