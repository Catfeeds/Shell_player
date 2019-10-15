<?php defined('IN_IA') or exit('Access Denied');?><div class="form-group">
	<label class="col-lg control-label">排序</label>
	<div class="col-sm-9 col-xs-12">
		<?php if( ce('sale.coupon' ,$item) ) { ?>
		<input type="text" name="displayorder" class="form-control" value="<?php  echo $item['displayorder'];?>"  />
		<span class='help-block'>数字越大越靠前</span>
		<?php  } else { ?>
		<div class='form-control-static'><?php  echo $item['displayorder'];?></div>
		<?php  } ?>
	</div>
</div>

<div class="form-group">
	<label class="col-lg control-label must"> 优惠券名称</label>
	<div class="col-sm-9 col-xs-12">
		<?php if( ce('sale.coupon' ,$item) ) { ?>
		<input type="text" name="couponname" class="form-control" value="<?php  echo $item['couponname'];?>" data-rule-required="true"  />
		<?php  } else { ?>
		<div class='form-control-static'><?php  echo $item['couponname'];?></div>
		<?php  } ?>
	</div>
</div>


<!-- <div class="form-group">
	<label class="col-lg control-label">分类</label>
	<div class="col-sm-9 col-xs-12">
		<?php if( ce('sale.coupon' ,$item) ) { ?>
		<select name='catid' class='form-control select2'>
			<option value=''></option>
			<?php  if(is_array($category)) { foreach($category as $k => $c) { ?>
			<option value='<?php  echo $k;?>' <?php  if($item['catid']==$k) { ?>selected<?php  } ?>><?php  echo $c['name'];?></option>
			<?php  } } ?>
		</select>
		<?php  } else { ?>
		<div class='form-control-static'><?php  if(empty($item['catid'])) { ?>暂时无分类<?php  } else { ?> <?php  echo $category[$item['catid']]['name'];?><?php  } ?></div>
		<?php  } ?>
	</div>
</div> -->

<div class="form-group">
	<!-- <label class="col-lg control-label">使用条件</label> -->
	<label class="col-lg control-label">商家</label>
	<div class="col-sm-9 col-xs-12">
		<?php if( ce('sale.coupon' ,$item) ) { ?>
		<input type="text" name="enough" class="form-control" value="<?php  echo $item['enough'];?>"  />
		<!-- <span class='help-block' ><?php  if(empty($type)|| $type==2) { ?>消费<?php  } else { ?>充值<?php  } ?>满多少可用, 空或0 不限制</span> -->
		<?php  } else { ?>
		<div class='form-control-static'><?php  if($item['enough']>0) { ?>满 <?php  echo $item['enough'];?> 可用 <?php  } else { ?>不限制<?php  } ?></div>
		<?php  } ?>
	</div>
</div>


<?php if( ce('sale.coupon' ,$item) ) { ?>

<div class="form-group">
	<label class="col-lg control-label">使用时间限制</label>

	
	<div class="col-sm-7">
		<div class='input-group'>
			<span class='input-group-addon'>
				<label class="radio-inline" style='margin-top:-5px;' ><input type="radio" name="timelimit" value="0" <?php  if($item['timelimit']==0) { ?>checked<?php  } ?>>获得后</label>
			</span>

			<input type='text' class='form-control' name='timedays' value="<?php  echo $item['timedays'];?>" />
			<span class='input-group-addon'>天内有效(空为不限时间使用)</span>
		</div>
	</div>
 
</div>

<div class="form-group">
	<label class="col-lg control-label"></label>
	<div class="col-sm-3">
		<div class='input-group'>
			<span class='input-group-addon'>
				<label class="radio-inline" style='margin-top:-5px;' ><input type="radio" name="timelimit" value="1" <?php  if($item['timelimit']==1) { ?>checked<?php  } ?>>在日期</label>
			</span>
			<?php  echo tpl_form_field_eweishop_daterange('time', array('starttime'=>date('Y-m-d', $starttime),'endtime'=>date('Y-m-d', $endtime)));?>
			<span class='input-group-addon'>内有效</span>
		</div>
	</div>
	 

</div>
<?php  } else { ?>
<div class="form-group">
	<label class="col-lg control-label">使用时间限制</label>
 
	<div class="col-sm-9 col-xs-12">
		<div class='form-control-static'>
			<?php  if($item['timelimit']==0) { ?>
			<?php  if(!empty($item['timedays'])) { ?>获得后 <?php  echo $item['timedays'];?> 天内有效<?php  } else { ?>不限时间<?php  } ?>
			<?php  } else { ?>
			<?php  echo date('Y-m-d',$starttime)?> - <?php  echo date('Y-m-d',$endtime)?>  范围内有效
			<?php  } ?></div>
	</div>
</div>
<?php  } ?>



 <div class="form-group">
	<label class="col-lg control-label">发放总数</label>
	<div class="col-sm-9 col-xs-12">
		<?php if( ce('sale.coupon' ,$item) ) { ?>
		<input type="text" name="total" class="form-control" value="<?php  echo $item['total'];?>"  />
		<span class='help-block' >优惠券总数量，没有不能领取或发放,-1 为不限制张数</span>
		<?php  } else { ?>
		<div class='form-control-static'><?php  if($item['total']==-1) { ?>无限数量<?php  } else { ?>剩余 <?php  echo $item['total'];?> 张<?php  } ?></div>
		<?php  } ?>
	</div>
</div>
<?php  if(!empty($item['id'])) { ?>
<div class="form-group">
	<label class="col-lg control-label">剩余数量</label>
	<div class="col-sm-9 col-xs-12">
		<?php if( ce('sale.coupon' ,$item) ) { ?>
		<input type="text" name="xxxxxx" class="form-control" value="<?php  echo $left_count;?>" readonly />
		<span class='help-block' >优惠券剩余可以领取或发放张数</span>
		<?php  } else { ?>
		<div class='form-control-static'><?php  echo $left_count;?></div>
		<?php  } ?>
	</div>
</div>
<?php  } ?>