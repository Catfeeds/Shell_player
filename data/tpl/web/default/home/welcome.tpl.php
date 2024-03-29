<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<div class="welcome-container"  id="js-home-welcome" ng-controller="WelcomeCtrl" ng-cloak>
    <?php  if(permission_check_account_user('statistics_fans', false)) { ?>
    <div class="panel we7-panel account-stat">
        <div class="panel-heading">
            <h4>今日/昨日</h4>
        </div>
        <div class="panel-body we7-padding-vertical">
            <div class="col-sm-3 text-center">
                <div class="title">新关注</div>
                <div>
                    <span class="today" ng-init="0" ng-bind="fans_kpi.today.new"></span>
                    <span class="pipe">/</span>
                    <span class="yesterday" ng-init="0" ng-bind="fans_kpi.yesterday.new"></span>
                </div>
            </div>
            <div class="col-sm-3 text-center">
                <div class="title">取消关注</div>
                <div>
                    <span class="today" ng-init="0" ng-bind="fans_kpi.today.cancel"></span>
                    <span class="pipe">/</span>
                    <span class="yesterday" ng-init="0" ng-bind="fans_kpi.yesterday.cancel"></span>
                </div>
            </div>
            <div class="col-sm-3 text-center">
                <div class="title">净增关注</div>
                <div>
                    <span class="today" ng-init="0" ng-bind="fans_kpi.today.jing_num"></span>
                    <span class="pipe">/</span>
                    <span class="yesterday" ng-init="0" ng-bind="fans_kpi.yesterday.jing_num"></span>
                </div>
            </div>
            <div class="col-sm-3 text-center">
                <div class="title">累计关注</div>
                <div>
                    <span class="today" ng-init="0" ng-bind="fans_kpi.all"></span>
                </div>
            </div>
        </div>
    </div>
    <?php  } ?>

    <!-- 公告 start -->
    <div class="panel we7-panel">
        <div class="panel-heading">
            <h4>公告</h4>
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active">
                    <a href="#notice" aria-controls="notice" role="tab" data-toggle="tab" >系统公告</a>
                </li>
                
            </ul>
            <a href="./index.php?c=article&a=notice-show" class="color-default more">更多</a>
            <?php  if(permission_check_account_user('see_notice_post')) { ?><a href="./index.php?c=article&a=notice&do=post" class="color-default more">+新建</a><?php  } ?>
        </div>
        <div class="panel-body">
            <div class="tab-content" >
                <div class="tab-pane active" id="notice">
                    <ul class="list-group notice-statistics" >
                        <li class="list-group-item" ng-repeat="notice in notices" ng-if="notices && notices.length">
                            <a ng-href="{{notice.url}}" class="text-over" target="_blank" ng-style="{'color': notice.style.color, 'font-weight': notice.style.bold ? 'bold' : 'normal'}" ng-bind="notice.title"></a>
                            <span class="pull-right color-gray" ng-bind="notice.createtime"></span>
                        </li>
                        <div class="we7-empty-block" ng-if="!notices.length"> 
                            暂无公告
                        </div>
                    </ul>
                </div>
                
            </div>
        </div>
    </div>
    <!-- 公告 end -->
    
</div>
<script>
	angular.module('homeApp').value('config', {
        family: "<?php  echo IMS_FAMILY?>",
		notices: <?php echo !empty($notices) ? json_encode($notices) : 'null'?>,
        'apiLink': '//api.w7.cc',
	});
	angular.bootstrap($('#js-home-welcome'), ['homeApp']);
	$(function(){
		$('[data-toggle="tooltip"]').tooltip();
		var $topic = $('.welcome-container .notice .menu .topic');
		var $ul = $('.welcome-container .notice ul');

		$topic.mouseover(function(){
			var $this = $(this);
			var $index = $this.index();
			if ($this.is('.we7notice')) {
				$this.parent().prev().hide();
			} else {
				$this.parent().prev().show();
			}
			$topic.removeClass('active');
			$this.addClass('active');
			$ul.removeClass('active');
			$ul.eq($index).addClass('active');
		})
	})
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>