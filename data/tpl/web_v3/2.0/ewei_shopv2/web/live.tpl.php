<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>
<div class="page-header">
    当前位置：<span class="text-primary"><?php  echo $this->plugintitle?></span>
</div>

<div class="page-content">
    <div class="page-toolbar">
        <span class="">
            <a class="btn btn-default disabled btn-status btn-sm">通信服务检测中</a>
        </span>
    </div>
    <div class="alert alert-danger">
        <p><b>错误提示</b></p>
        <p>错误信息：通信新服务已停止或未开启，无法正常使用直播功能 <a class="btn-reconnect">点击重试</a></p>
        <p>解决方法：等待通讯服务自动重启或参照教程自行重启服务</p>
    </div>

    <div class="row">
        <div class="col-md-4 col-sm-4">
            <div class="summary_box">
                <div class="summary_title">
                    <a class="label label-primary pull-right" href="<?php  echo webUrl('live/room')?>" style="margin: 15px 30px 0 0px;">管理</a>
                    <span class="text-default title_inner">直播间</span>
                </div>
                <div class="summary flex">
                    <div class="flex1 flex column" style="border-right: 1px solid #efefef">
                        正在直播
                        <h2 class="totalcount"><?php  echo $livingnum;?></h2>
                    </div>
                    <div class="flex1 flex column">
                        全部直播间
                        <h2 class=""><?php  echo $livenum;?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 col-sm-8">
            <div class="summary_box">
                <div class="summary_title">
                    <a class="label label-success pull-right" href="<?php  echo webUrl('live/room')?>" style="margin: 15px 30px 0 0px;">更多</a>
                    <span class="text-default title_inner">直播商品统计</span>
                </div>
                <div class="summary flex">
                    <div class="flex1 flex column" style="border-right: 1px solid #efefef">
                        当天销售额
                        <h2 class="text-danger"><?php  echo $liveprice['7'];?></h2>
                    </div>
                    <div class="flex1 flex column" style="border-right: 1px solid #efefef">
                        七天内销售额
                        <h2 class="text-danger"><?php  echo $liveprice['7'];?></h2>
                    </div>
                    <div class="flex1 flex column">
                        三十天内销售额
                        <h2 class="text-danger"><?php  echo $liveprice['30'];?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    myrequire(['../../plugin/live/static/js/webindex'], function (modal) {
        modal.init({wsConfig: <?php  echo $wsConfig;?>});
    });
</script>

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>
<!--6Z2S5bKb5piT6IGU5LqS5Yqo572R57uc56eR5oqA5pyJ6ZmQ5YWs5Y+454mI5p2D5omA5pyJ-->