<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>
<div class="page-header">
    <span class="text-primary">任务概述</span>
</div>
<div class="page-content">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="ibox float-e-margins" style="border: 1px solid #e7eaec">
                <div class="ibox-title">
                    主动任务
                    <a class="pull-right" href="<?php  echo webUrl('task.record')?>">更多统计 »</a>
                </div>
                <div class="ibox-content">

                    <div class="row">
                        <div class="col-md-3 text-center">
                            已领取
                            <h2 class="no-margins"><span class="today-price small"><?php  echo $taskS['0'];?></span></h2>
                        </div>
                        <div class="col-md-3 text-center">
                            已完成
                            <h2 class="no-margins"><span class="today-price small"><?php  echo $taskS['1'];?></span></h2>
                        </div>
                        <div class="col-md-3 text-center">
                            已失败
                            <h2 class="no-margins"><span class="today-price small"><?php  echo $taskS['2'];?></span></h2>
                        </div>
                        <div class="col-md-3 text-center">
                            待完成
                            <h2 class="no-margins"><span class="today-price small"><?php  echo $taskS['3'];?></span></h2>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-12 col-sm-12">
            <div class="ibox float-e-margins" style="border: 1px solid #e7eaec">
                <div class="ibox-title">
                    奖励发放
                    <a class="pull-right" href="<?php  echo webUrl('task.reward')?>">更多统计 »</a>
                    <h5></h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            积分
                            <h2 class="no-margins"><span class="month-price small" style="color: #f0ad4e"><?php  echo $rewardS['0'];?></span></h2>
                        </div>
                        <div class="col-md-3 text-center">
                            红包
                            <h2 class="no-margins"><span class="month-price small" style="color: #ed5565"><?php  echo $rewardS['1'];?></span></h2>
                        </div>
                        <div class="col-md-3 text-center">
                            余额
                            <h2 class="no-margins"><span class="month-price text-success small" style="color: #00A388"><?php  echo $rewardS['2'];?></span></h2>
                        </div>
                        <div class="col-md-3 text-center">
                            优惠券
                            <h2 class="no-margins"><span class="month-price small" style="color: #1c84c6"><?php  echo $rewardS['3'];?></span></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>