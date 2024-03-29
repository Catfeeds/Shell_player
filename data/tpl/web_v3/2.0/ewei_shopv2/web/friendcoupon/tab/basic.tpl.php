<?php defined('IN_IA') or exit('Access Denied');?>
<div class="form-group">
    <label class="col-lg control-label must">活动名称</label>
    <div class="col-sm-9 col-xs-12 ">
        <div class="input-group col-sm-12 col-xs-12">
            <input type="text"  name="title"  class="form-control" value="<?php  echo $activity['title'];?>" data-rule-required="true" />
        </div>
        <span class='help-block'>活动名称的长度请控制在15字以内</span>
    </div>
</div>

<div class="form-group">
    <label class="col-lg control-label must">瓜分内容</label>
    <div class="col-sm-4 col-xs-5">
        <div class="input-group">
            <input type="text" name="people_count" class="form-control" value="<?php  echo $activity['people_count'];?>" <?php  if($activity['id'] && !$this->isCopyLink()) { ?>readonly="true"<?php  } ?>/>
            <span class="input-group-addon">人 瓜分</span>
            <input type="text" name="coupon_money" class="form-control" value="<?php  echo $activity['coupon_money'];?>" <?php  if($activity['id'] && !$this->isCopyLink()) { ?>readonly="true"<?php  } ?>/>
            <span class="input-group-addon">元</span>
        </div>
        <span class='help-block'>指定人数瓜分此券</span>
    </div>
</div>

<div class="form-group">
    <label class="col-lg control-label must">瓜分时长</label>
    <div class="col-sm-9 col-xs-12">
        <div class="input-group">
            <input type="text" id="poster_banner" value="<?php  echo $activity['duration'];?>" class="form-control" name="duration">
            <span class="input-group-addon">小时</span>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="col-lg control-label">瓜分方式</label>
    <div class="col-sm-9 col-xs-12">
        <label class="radio-inline">
            <input type="radio" id="allocate_random" name="allocate" value=0 <?php  if($activity['allocate']==0) { ?>checked<?php  } ?> <?php  if($activity['id'] && !$this->isCopyLink()) { ?>disabled="true"<?php  } ?> /> 随机金额
        </label>
        <label class="radio-inline">
            <input type="radio" id="allocate_avg" name="allocate" value=1 <?php  if($activity['allocate']==1) { ?>checked<?php  } ?> <?php  if($activity['id'] && !$this->isCopyLink()) { ?>disabled="true"<?php  } ?>/> 平均分配
        </label>
        <span class='help-block'>随机金额：随机分配</span>
        <span class='help-block'>平均分配：将瓜分券总额平均分配给参与者</span>
        <div id="upper_limit" <?php  if($activity['allocate'] == 1) { ?>style="display:none"<?php  } ?>>
            <div class="input-group" >
                <input type="text" class="form-control" name="upper_limit" <?php  if($activity['id'] && !$this->isCopyLink()) { ?>readonly="true"<?php  } ?> value="<?php  echo $activity['upper_limit'];?>">
                <span class="input-group-addon">元</span>
            </div>
            <span class='help-block'>瓜分券金额最小值，默认为0.01</span>

        </div>
    </div>
</div>

<div class="form-group">
    <label class="col-lg control-label must">可发起次数</label>
    <div class="col-sm-9 col-xs-12">
        <input type="number" name="launches_limit" class="form-control" value="<?php  echo $activity['launches_limit'];?>" <?php  if($activity['id'] && !$this->isCopyLink()) { ?>readonly="true"<?php  } ?> data-rule-required="true"  />
        <span class='help-block'>指活动共可被发起的总次数，一个账号只能参与瓜分1次</span>
    </div>
</div>

<div class="form-group">
    <label class="col-lg control-label must">活动时间</label>
    <div class="col-sm-9 col-xs-12">
        <?php  echo tpl_form_field_eweishop_daterange('activity_time', array('starttime'=>substr($activity['activity_start_time'],0,-3),'endtime' => substr($activity['activity_end_time'], 0, -3)), true);?>
        <span class='help-block'>可领取任务的时间段</span>
    </div>
</div>

<div class="form-group">
    <label class="col-lg control-label">活动说明</label>

    <div class="col-sm-9 col-xs-12">
        <textarea name="desc" class="form-control" rows="5"><?php  echo $activity['desc'];?></textarea>
        <span class="help-block">活动说明的长度请控制在100字以内</span>
        <span class="help-block">不填使用 <div href="#" style="color:#55B5FF;display: inline;cursor: pointer;" id="default_desc">默认活动说明</div> ，活动规则自动读取活动设置，可编辑瓜分步骤说明</span>
    </div>

</div>

<script>
    $(function () {
        $('input[name=allocate]').on('click', function () {
            var $upperLimitInput = $('#upper_limit');
            console.log($(this).val())
            $(this).val() == 0 ? $upperLimitInput.show() : $upperLimitInput.hide()
        })

        var tips_content = "" +
            "<div style='color: #000;'>" +
            "<p align='center' style='font-weight: bold;'>活动规则</p>" +
            "<b>活动规则</b><br>" +
            "<b>活动时间</b>：<br>" +
            "2018-10-10  10:00:00 <br>至 <br>2020-10-10  10:00:00<br>" +
            "<b>活动时长：</b>24小时<br>" +
            "<b>瓜分人数</b>：4人<br>"+
            "<b>瓜分步骤</b>：<br>" +
            "1.领取活动<br>" +
            "2.在规定时间内邀请指定人数一起瓜分红包<br>" +
            "3.满足条件后开奖瓜分<br>"+
            "</div>"

        $('#default_desc').popover({
            trigger: 'click',
            delay: { "show": 500, "hide": 100 },
            title: "活动说明",
            content: tips_content,
            placement: 'top',
            title: false,
            animation: true,
            html: 'true',
        });
    })
</script>