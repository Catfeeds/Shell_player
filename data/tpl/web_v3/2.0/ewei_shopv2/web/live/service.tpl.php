<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>

<div class="page-header">
    当前位置：<span class="text-primary">通信服务</span>
</div>

<div class="page-content">
        
    <form id="dataform" action="" method="post" class="form-horizontal form">

        <div class="alert alert-danger" style="display: none;">
            <p><b>错误提示</b></p>
            <p>错误信息：通信新服务已停止或未开启，无法正常使用直播功能 <a class="btn-reconnect">点击重试</a></p>
            <p>解决方法：等待通讯服务自动重启或参照教程自行重启服务</p>
        </div>

        <div class="form-group">
            <label class="col-lg control-label">重启服务</label>
            <div class="col-sm-9">
                <div class="btn btn-default btn-status" id="btn-reload">通信服务检测中</div>
            </div>
        </div>

    </form>
</div>

<script type="text/javascript">
    myrequire(['../../plugin/live/static/js/webindex'], function (modal) {
        modal.init({wsConfig: <?php  echo $wsConfig;?>, type: 1});
    });
</script>


<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>
<!--913702023503242914-->