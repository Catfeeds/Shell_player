<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>

<div class="page-header">
	当前位置：<span class="text-primary"><?php  if(!empty($item)) { ?>编辑<?php  } else { ?>添加<?php  } ?>电子面单模版 <?php  if(!empty($item['esheetname'])) { ?>(<?php  echo $item['esheetname'];?>)<?php  } ?></span>
</div>

<div class="page-content">
	<form id="setform" action="" method="post" class="form-horizontal form-validate">
		<div class="form-group">
			<label class="col-lg control-label must">电子面单名称</label>
			<div class="col-sm-9 col-xs-12">
				<?php if( ce('exhelper.temp.esheet' ,$item) ) { ?>
				<input type='text' class='form-control' name='esheetname' value="<?php  echo $item['esheetname'];?>" data-rule-required='true' />
				<?php  } else { ?>
				<div class="form-control-static"><?php  echo $item['esheetname'];?></div>
				<?php  } ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-lg control-label must">快递公司</label>
			<div class="col-sm-9 col-xs-12">
				<?php if( ce('exhelper.temp.esheet' ,$item) ) { ?>
					<select class="form-control valid" name="esheetid" id="esheetid">
						<?php  if(is_array($esheet_list)) { foreach($esheet_list as $esheet) { ?>
						<option value="<?php  echo $esheet['id'];?>" <?php  if($item['esheetid']==$esheet['id']) { ?> selected=""<?php  } ?>  data-allsize="<?php  echo $esheet['datas'];?>"><?php  echo $esheet['name'];?></option>
						<?php  } } ?>
					</select>
				<?php  } else { ?>
					<?php  if(is_array($esheet_list)) { foreach($esheet_list as $esheet) { ?>
						<?php  if($item['esheetid']==$esheet['id']) { ?>
							<div class="form-control-static"><?php  echo $esheet['name'];?></div>
						<?php  } ?>
					<?php  } } ?>
				<?php  } ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-lg control-label">电子面单客户账号</label>
			<div class="col-sm-9 col-xs-12">
				<?php if( ce('exhelper.temp.esheet' ,$item) ) { ?>
					<input type='text' class='form-control' name='customername' value="<?php  echo $item['customername'];?>"  placeholder="与快递网点申请" />
				<?php  } else { ?>
				<div class="form-control-static"><?php  echo $item['customername'];?></div>
				<?php  } ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-lg control-label">电子面单密码</label>
			<div class="col-sm-9 col-xs-12">
				<?php if( ce('exhelper.temp.esheet' ,$item) ) { ?>
					<input type='text' class='form-control' name='customerpwd' value="<?php  echo $item['customerpwd'];?>" placeholder="与快递网点申请" />
				<?php  } else { ?>
				<div class="form-control-static"><?php  echo $item['customerpwd'];?></div>
				<?php  } ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-lg control-label">月结编码</label>
			<div class="col-sm-9 col-xs-12">
				<?php if( ce('exhelper.temp.esheet' ,$item) ) { ?>
					<input type='text' class='form-control' name='monthcode' value="<?php  echo $item['monthcode'];?>" placeholder="快递公司提供" />
				<?php  } else { ?>
				<div class="form-control-static"><?php  echo $item['monthcode'];?></div>
				<?php  } ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-lg control-label">收件网点标识</label>
			<div class="col-sm-9 col-xs-12">
				<?php if( ce('exhelper.temp.esheet' ,$item) ) { ?>
				<input type='text' class='form-control' name='sendsite' value="<?php  echo $item['sendsite'];?>" placeholder="快递公司提供" />
				<?php  } else { ?>
				<div class="form-control-static"><?php  echo $item['sendsite'];?></div>
				<?php  } ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-lg control-label must">邮费支付方式</label>
			<div class="col-sm-9 col-xs-12">
				<?php if( ce('exhelper.temp.esheet' ,$item) ) { ?>
				<select class="form-control" name="paytype">
					<option value="1" <?php  if($item['paytype']==1) { ?> selected <?php  } ?> ">现付</option>
					<option value="2" <?php  if($item['paytype']==2) { ?> selected <?php  } ?> ">到付</option>
					<option value="3" <?php  if($item['paytype']==3) { ?> selected <?php  } ?> ">月结</option>
				</select>
				<?php  } else { ?>
				<div class="form-control-static"><?php  if($item['paytype']==1) { ?> 现付 <?php  } ?><?php  if($item['paytype']==2) { ?> 到付 <?php  } ?><?php  if($item['paytype']==3) { ?> 月结 <?php  } ?></div>
				<?php  } ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-lg control-label must">模板样式</label>
			<div class="col-sm-9 col-xs-12">
				<?php if( ce('exhelper.temp.esheet' ,$item) ) { ?>
					<select class="form-control" name="templatesize" id="templatesize">
						<?php  if(is_array($esheettemp_list)) { foreach($esheettemp_list as $temp) { ?>

							<option value="<?php  if($temp['isdefault']==0) { ?><?php  echo $temp['size'];?><?php  } else { ?>0<?php  } ?>" <?php  if($item['templatesize']==$temp['size'] || ($temp['isdefault']==1 && empty($item['templatesize']))) { ?>selected<?php  } ?>><?php  echo $temp['style'];?><?php  echo $temp['spec'];?></option>
						<?php  } } ?>
					</select>
				<?php  } else { ?>
					<?php  if(is_array($esheettemp_list)) { foreach($esheettemp_list as $temp) { ?>
						<?php  if($item['templatesize']==$temp['size']) { ?>
							<div class="form-control-static"><?php  echo $temp['style'];?><?php  echo $temp['spec'];?></div>
						<?php  } ?>
					<?php  } } ?>
				<?php  } ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-lg control-label">通知快递员上门揽件</label>
			<div class="col-sm-9 col-xs-12">
				<?php if( ce('exhelper.temp.esheet' ,$item) ) { ?>
				<label class='radio-inline'>
					<input type='radio' name='isnotice' value='0' <?php  if($item['isnotice']==0) { ?>checked<?php  } ?> /> 是
				</label>
				<label class='radio-inline'>
					<input type='radio' name='isnotice' value='1' <?php  if($item['isnotice']==1) { ?>checked<?php  } ?> /> 否
				</label>
				<?php  } else { ?>
				<div class='form-control-static'><?php  if(empty($item['isnotice'])) { ?>是<?php  } else { ?>否<?php  } ?></div>
				<?php  } ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-lg control-label">是否自动修改发货状态</label>
			<div class="col-sm-9 col-xs-12">
				<?php if( ce('exhelper.temp.esheet' ,$item) ) { ?>
				<label class='radio-inline'>
					<input type='radio' name='issend' value='1' <?php  if($item['issend']==1) { ?>checked<?php  } ?> /> 是
				</label>
				<label class='radio-inline'>
					<input type='radio' name='issend' value='0' <?php  if($item['issend']==0) { ?>checked<?php  } ?> /> 否
				</label>
				<?php  } else { ?>
				<div class='form-control-static'><?php  if(empty($item['issend'])) { ?>否<?php  } else { ?>是<?php  } ?></div>
				<?php  } ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-lg control-label">是否默认</label>
			<div class="col-sm-9 col-xs-12">
				<?php if( ce('exhelper.temp.esheet' ,$item) ) { ?>
				<label class='radio-inline'>
					<input type='radio' name='isdefault' value='1' <?php  if($item['isdefault']==1) { ?>checked<?php  } ?> /> 是
				</label>
				<label class='radio-inline'>
					<input type='radio' name='isdefault' value='0' <?php  if($item['isdefault']==0) { ?>checked<?php  } ?> /> 否
				</label>
				<?php  } else { ?>
					<div class='form-control-static'><?php  if(empty($item['isdefault'])) { ?>否<?php  } else { ?>是<?php  } ?></div>
				<?php  } ?>
			</div>
		</div>

		<div class="form-group">
			<label class="col-lg control-label"></label>
			<div class="col-sm-9">
				<?php if( ce('exhelper.temp.esheet' ,$item) ) { ?>
				<input type="submit" value="提交" class="btn btn-primary" />
				<?php  } ?>
				<a class="btn btn-default" href="<?php  echo webUrl('exhelper/temp/esheet')?>">返回列表</a>
			</div>
		</div>
	</form>
</div>
<script>
$(".valid").change(function () {
    //查找选中的快递公司获取模版信息，将单引号替换成双引号并转换成对象
    var allsize =  $.parseJSON($("#esheetid").find("option:selected").data('allsize').replace(/'/g, '"'));

    $("#templatesize option").remove();

    $.each(allsize,function(idx,obj){
        if(obj.isdefault==1){
            $("#templatesize").append("<option value='' selected >"+obj.style+obj.spec+"</option>");
		}else{
            $("#templatesize").append("<option value='"+obj.size+"' >"+obj.style+obj.spec+"</option>");
		}
    });
})

</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>