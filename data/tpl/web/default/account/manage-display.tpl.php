<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<div class="clearfix ">
	<div class="search-box we7-margin-bottom">
		<select name="" class="we7-margin-right">
			<option data-url="<?php  echo filter_url('account_type:');?>" >帐号类型筛选</option>
			<?php  if(is_array($account_all_type_sign)) { foreach($account_all_type_sign as $type_sign => $type_sign_info) { ?>
				<option data-url="<?php  echo filter_url('account_type:' . $type_sign);?>" <?php  if($_GPC['account_type'] == $type_sign) { ?> selected<?php  } ?>><?php  echo $type_sign_info['title'];?></option>
			<?php  } } ?>
		</select>
		<select name="" id="" class="we7-margin-right">
			<option data-url="<?php  echo filter_url('order:asc');?>" >创建时间正序</option>
			<option data-url="<?php  echo filter_url('order:desc');?>" <?php  if($_GPC['order'] == 'desc') { ?> selected<?php  } ?>>创建时间倒序</option>
		</select>
		<select name="" id="" class="we7-margin-right">
			<option data-url="<?php  echo filter_url('type:all');?>" <?php  if($_GPC['type'] == 'all') { ?> selected<?php  } ?>>到期筛选</option>
			<option data-url="<?php  echo filter_url('type:expire');?>" <?php  if($_GPC['type'] == 'expire') { ?> selected<?php  } ?>>已到期</option>
			<option data-url="<?php  echo filter_url('type:unexpire');?>" <?php  if($_GPC['type'] == 'unexpire') { ?> selected<?php  } ?>>未到期</option>		
		</select>
		<form action="" class="search-form " method="get">
			<input type="hidden" name="c" value="account">
			<input type="hidden" name="a" value="manage">
			<div class="input-group" style="width: 400px;">
				<input type="text" name="keyword" value="<?php  echo $_GPC['keyword'];?>" class="form-control" placeholder="搜索关键字"/>
				<span class="input-group-btn"><button class="btn btn-default"><i class="wi wi-search"></i></button></span>
			</div>
		</form>
		
		<?php  if(!empty($account_info['account_limit']) || $_W['isfounder']) { ?>
		<a href="javascript:;" data-toggle="modal" data-target="#owner-modal" class="btn btn-primary we7-padding-horizontal">添加平台</a>
		<?php  } ?>
	</div>

</div>

<!-- 列表数据 start -->
<table class="table we7-table table-hover vertical-middle table-manage" id="js-system-account-display" ng-controller="SystemAccountDisplay" ng-cloak>
	<col width="100px" />
	<col width="400px"/>
	<col width=""/>
	<col width=""/>
	<col width="260px" />
	<tr>
		<th colspan="2" class="text-left">名称</th>
		<th>平台过期时间</th>
		<th>主管理员</th>
		<th class="text-right">操作</th>
	</tr>
	<tr class="color-gray" ng-repeat="list in lists" ng-show="list.current_user_role != 'clerk'">
		<td class="text-left td-link">
			<a href="javascript:;"><img src="{{list.logo}}" class="img-responsive account-img icon"></a>
		</td>
		<td class="text-left">
			<p class="color-dark" ng-bind="list.name"></p>
			<div ng-if="list.type_sign == 'account'">
				<span class="color-gray" ng-if="list.level == 1">类型：普通订阅号</span>
				<span class="color-gray" ng-if="list.level == 2">类型：普通服务号</span>
				<span class="color-gray" ng-if="list.level == 3">类型：认证订阅号</span>
				<span class="color-gray" ng-if="list.level == 4" title="认证服务号/认证媒体/政府订阅号">类型：认证服务号</span>
				<span class="color-red" ng-if="list.isconnect == 0" ><i class="wi wi-error-sign"></i>未接入</span>
				<span class="color-green" ng-if="list.isconnect == 1"><i class="wi wi-right-sign"></i>已接入</span>
			</div>
			<div ng-if="list.type_sign != 'account'">
				<span class="color-gray">类型：{{ list.type_name }}</span>
			</div>
		</td>
		<td>
			<p ng-bind="list.end"></p>
		</td>
		<td><p ng-bind="list.owner_name"></p></td>
		<td class="vertical-middle table-manage-td">
			<div class="link-group">
				<a ng-href="{{links.switch}}uniacid={{list.uniacid}}&type={{list.type}}"  ng-if="!list.support_version" class="">进入{{ list.type_name }}</a>
				
				
				<a ng-href="{{links.switch}}uniacid={{list.uniacid}}&version_id={{list.current_version.id}}&type={{list.type}}" ng-if="list.support_version" class="">进入{{ list.type_name }}</a>
				
				<a ng-href="{{links.post}}&acid={{list.acid}}&uniacid={{list.uniacid}}&account_type={{list.type}}" ng-if="list.manage_premission">管理设置</a>
			</div>

			<div class="manage-option text-right" ng-if="list.manage_premission">
				<a href="{{links.post}}&acid={{list.acid}}&uniacid={{list.uniacid}}&account_type={{list.type}}">基础信息</a>
				<a href="{{links.postUser}}&do=edit&uniacid={{list.uniacid}}&acid={{list.acid}}&account_type={{list.type}}">使用者管理</a>
				<a href="{{links.post}}&do=modules_tpl&uniacid={{list.uniacid}}&acid={{list.acid}}&account_type={{list.type}}">可用应用模板/模块</a>
				<a href="javascript:void(0);" ng-click="receiveAccount(links.del, list.acid, list.uniacid, '')" class="del">停用</a>
			</div>
		</td>
	</tr>
</table>
<!-- 列表数据 end -->

<div class="text-right">
	<?php  echo $pager;?>
</div>

<!-- 添加帐号弹窗 start -->
<div class="modal fade modal-type" tabindex="-1" role="dialog" id="owner-modal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header clearfix">
				新建
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="type-list">
					<?php  if(!empty($account_info['account_limit']) && (!empty($account_info['founder_account_limit']) && $_W['user']['owner_uid'] || empty($_W['user']['owner_uid'])) || $_W['isfounder'] && !user_is_vice_founder()) { ?>
					<a class="item" href="./index.php?c=account&a=post-step">
						<i class="wi wi-wx-circle"></i>
						<div class="name">新建公众号</div>
						<div class="mark">
							去新建
						</div>
					</a>
					<?php  } ?>

					<?php  if(!empty($account_info['wxapp_limit']) && (!empty($account_info['founder_wxapp_limit']) && $_W['user']['owner_uid'] || empty($_W['user']['owner_uid'])) || $_W['isfounder'] && !user_is_vice_founder()) { ?>
					<a href="<?php  echo url('wxapp/post/design_method')?>" class="item">
						<i class="wi wi-wxapp"></i>
						<div class="name">新建微信小程序</div>
						<div class="mark">
							去新建
						</div>
					</a>
					<?php  } ?>

					<?php  if(!empty($account_info['webapp_limit']) && (!empty($account_info['founder_webapp_limit']) && $_W['user']['owner_uid'] || empty($_W['user']['owner_uid'])) || $_W['isfounder'] && !user_is_vice_founder()) { ?>
					<a href="<?php  echo url('account/create', array('sign' => 'webapp'))?>" class="item">
						<i class="wi wi-pc-circle"></i>
						<div class="name">新建PC</div>
						<div class="mark">
							去新建
						</div>
					</a>
					<?php  } ?>

					<?php  if(!empty($account_info['phoneapp_limit']) && (!empty($account_info['founder_phoneapp_limit']) && $_W['user']['owner_uid'] || empty($_W['user']['owner_uid'])) || $_W['isfounder'] && !user_is_vice_founder()) { ?>
					<a href="<?php  echo url('account/create', array('sign' => 'phoneapp'))?>" class="item">
						<i class="wi wi-app"></i>
						<div class="name">新建APP</div>
						<div class="mark">
							去新建
						</div>
					</a>
					<?php  } ?>

					<?php  if(!empty($account_info['xzapp_limit']) && (!empty($account_info['founder_xzapp_limit']) && $_W['user']['owner_uid'] || empty($_W['user']['owner_uid'])) || $_W['isfounder'] && !user_is_vice_founder()) { ?>
					<a href="<?php  echo url('xzapp/post-step')?>" class="item">
						<i class="wi wi-xzapp"></i>
						<div class="name">新建熊掌号</div>
						<div class="mark">
							去新建
						</div>
					</a>
					<?php  } ?>

					<?php  if(!empty($account_info['aliapp_limit']) && (!empty($account_info['founder_aliapp_limit']) && $_W['user']['owner_uid'] || empty($_W['user']['owner_uid'])) || $_W['isfounder'] && !user_is_vice_founder()) { ?>
					<a href="<?php  echo url('account/create', array('sign' => 'aliapp'))?>" class="item">
						<i class="wi wi-aliapp"></i>
						<div class="name">新建支付宝小程序</div>
						<div class="mark">
							去新建
						</div>
					</a>
					<?php  } ?>

					<?php  if(!empty($account_info['baiduapp_limit']) && (!empty($account_info['founder_baiduapp_limit']) && $_W['user']['owner_uid'] || empty($_W['user']['owner_uid'])) || $_W['isfounder'] && !user_is_vice_founder()) { ?>
					<a href="<?php  echo url('account/create', array('sign' => 'baiduapp'))?>" class="item">
						<i class="wi wi-baiduapp"></i>
						<div class="name">新建百度小程序</div>
						<div class="mark">
							去新建
						</div>
					</a>
					<?php  } ?>

					<?php  if(!empty($account_info['toutiaoapp_limit']) && (!empty($account_info['founder_toutiaoapp_limit']) && $_W['user']['owner_uid'] || empty($_W['user']['owner_uid'])) || $_W['isfounder'] && !user_is_vice_founder()) { ?>
					<a href="<?php  echo url('account/create', array('sign' => 'toutiaoapp'))?>" class="item">
						<i class="wi wi-toutiaoapp"></i>
						<div class="name">新建头条小程序</div>
						<div class="mark">
							去新建
						</div>
					</a>
					<?php  } ?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default"  data-dismiss="modal">取消</button>
			</div>
		</div>
	</div>
</div>
<!-- 添加帐号弹窗 end -->

<script>
	$(function(){
		$('[data-toggle="tooltip"]').tooltip();
	});
	angular.module('accountApp').value('config', {
		lists: <?php echo !empty($list) ? json_encode($list) : 'null'?>,
		links: {
			switch: "<?php  echo url('account/display/switch')?>",
			getAccountDetailInfo: "<?php  echo url('account/manage/account_detailinfo')?>",
			post: "<?php  echo url('account/post')?>",
			postUser: "<?php  echo url('account/post-user')?>",
			del: "<?php  echo url('account/manage/delete')?>",
		}
	});
	angular.bootstrap($('#js-system-account-display'), ['accountApp']);

</script>

<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>