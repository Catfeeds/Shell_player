<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>

<div class="page-header">
	当前位置：<span class="text-primary">打印机设置</span>
</div>

<div class="page-content">

	<form id="setform" action="" method="post" class="form-horizontal form-validate">


		<div id="normal">
			<div class="form-group-title">打印机设置</div>
			<div class="alert alert-primary">注意: 1. 请将打印机连接至本机。2. 在本机上安装打印控件。3. 将打印控件中的打印端口下面的打印端口设为相同。</div>
			<div class="form-group">
				<label class="col-lg control-label">IP端口</label>
				<div class="col-sm-9 col-xs-12">
					<?php if(cv('exhelper.printset.edit')) { ?>
						<input type='number' class='form-control' name='port' value="<?php  echo $sys['port'];?>" data-rule-required='true' />
					<?php  } else { ?>
						<div class='form-control-static'><?php  echo $sys['port'];?></div>
					<?php  } ?>
				</div>
			</div>


			<div class="form-group-title">电子面单设置</div>
			<div class="alert alert-primary">注意: 1. 注册<a href="http://www.kdniao.com/reg ">快递鸟</a>ID。2. 实名认证。3. 申请电子面单API：在快递鸟用户管理后台--申请API页面，点击申请电子面单API。4. 将<a href="http://www.kdniao.com/reg ">快递鸟</a>用户ID以及API key填入以下表单</div>
			<div class="form-group">
				<label class="col-lg control-label">用户ID</label>
				<div class="col-sm-9 col-xs-12">
					<?php if(cv('exhelper.printset.edit')) { ?>
					<input type='number' class='form-control' name='ebusiness' value="<?php  echo $sys['ebusiness'];?>"  />
					<?php  } else { ?>
					<div class='form-control-static'><?php  echo $sys['ebusiness'];?></div>
					<?php  } ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg control-label">API key</label>
				<div class="col-sm-9 col-xs-12">
					<?php if(cv('exhelper.printset.edit')) { ?>
					<input type='text' class='form-control' name='apikey' value="<?php  echo $sys['apikey'];?>" />
					<?php  } else { ?>
					<div class='form-control-static'><?php  echo $sys['apikey'];?></div>
					<?php  } ?>
				</div>
			</div>




			<div class="form-group">
				<label class="col-lg control-label"></label>
				<div class="col-sm-9">
					<?php if(cv('exhelper.printset.edit')) { ?>
					<input type="submit" value="提交" class="btn btn-primary" />
					<?php  } ?>
				</div>
			</div>

		</div>
	</form>

</div>

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>
<!--913702023503242914-->