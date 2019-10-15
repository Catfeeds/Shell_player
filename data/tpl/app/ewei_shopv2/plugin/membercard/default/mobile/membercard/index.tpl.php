<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>
<link rel="stylesheet" type="text/css" href="../addons/ewei_shopv2/plugin/membercard/static/css/index.css?123">
<div class='fui-page creditshop-index-page'>
	<div class="fui-header">
		<div class="fui-header-left">
			<a class="back"></a>
		</div>
		<div class="title">会员卡中心</div>
		<div class="fui-header-right"></div>
	</div>

	<div class='fui-content navbar' style="bottom: 0rem">
		<div class='tab'>
			<div data-cate="all" class='item active'>全部(<?php  echo count($list)?>)</div>
			<div data-cate="my" class='item'>我的会员卡 (<?php  echo count($list_my)?>)</div>
		</div>
		<div class='card-list'>

			<div class="all-card">
				<?php  if($list) { ?>
				<?php  if(is_array($list)) { foreach($list as $i => $row) { ?>
				<div class='card-list-item <?php  echo $row['card_style'];?>'>
					<div class='shadow'></div>

						<?php  if($row['kaitong']) { ?>
				<div class="buybtn" data-cartid="<?php  echo $row['id'];?>" ><?php  if($row['chongxin_kaitong']) { ?>重新购买<?php  } else { ?>立即开通<?php  } ?></div>
						<?php  } else { ?>
						<?php  if($row['expire_time']!='-1') { ?>
				<div class="buybtn" data-cartid="<?php  echo $row['id'];?>" >续费</div>
						<?php  } ?>
						<?php  } ?>


					<a class='cardindex content <?php  echo $row['card_style'];?>'  data-index=<?php  echo $i;?>  href="<?php  echo mobileUrl('membercard/detail',array('id'=>$row['id'],'k'=>$i,'type'=>all))?>">
						<i class="iconbg icon icon-huangguan-line"></i>
						<div class='content-inner'>
							<div class="content-title">
								<div class="title-l">
									<i class="icon icon-huangguan"></i><?php  echo $row['name'];?></div>
								<!--<div class="title-r buybtn" data-cartid="<?php  echo $row['id'];?>" >立即开通</div>-->
							</div>
							<div class="price"><?php  echo floatval($row['price'])?>元</div>
							<div class="date">有效期：<?php  if($row['validate_count']==-1) { ?>永久有效<?php  } else { ?><?php  echo $row['validate_count'];?>个月<?php  } ?></div>
							<div class="equity"><?php  if($row['shipping']) { ?>免费包邮·<?php  } ?><?php  if($row['discount_rate']) { ?><?php  echo $row['discount_rate'];?>折优惠·<?php  } ?><?php  if($row['is_card_coupon']) { ?>开卡送券·<?php  } ?><?php  if($row['is_month_coupon']) { ?>每月领券·<?php  } ?>更多特权</div>
						</div>
					</a>
				</div>
				<?php  } } ?>

			<div class="fui-loading empty" style="top:0.4rem">
				<div class="text">没有更多了</div>
			</div>
			<?php  } ?>

			<?php  if(empty($list)) { ?>
			<div class='card-blank' style="display: block">
				<div class='shadow'>
					<i style="font-size: 5rem;color: #f3f3f3;" class="icon icon-queshengye"></i>
				</div>
				<div class='text'>暂无可用会员卡</div>
			</div>
			<?php  } ?>
			</div>
			<div class="my-card" style="display: none">
				<?php  if($list_my) { ?>
				<?php  if(is_array($list_my)) { foreach($list_my as $index => $row) { ?>
				<div class='card-list-item <?php  echo $row['card_style'];?>'>
					<div class='shadow'></div>
					<?php  if($row['validate_count']!=-1) { ?>
					<div class="buybtn" data-cartid="<?php  echo $row['id'];?>">续费</div>
					<?php  } ?>
					<a class='cardindex content <?php  echo $row['card_style'];?>' data-index=<?php  echo $index;?>  href="<?php  echo mobileUrl('membercard/detail',array('id'=>$row['id'],'k'=>$index,'type'=>my))?>">
						<i class="iconbg icon icon-huangguan-line"></i>
						<div class='content-inner'>
							<div class="content-title">
								<div class="title-l">
									<i class="icon icon-huangguan"></i><?php  echo $row['name'];?></div>
							</div>
							<div class="price"><?php  echo floatval($row['price'])?>元</div>

							<div class="date"><?php  if($row['validate_count']==-1) { ?>有效期：永久有效<?php  } else { ?>有效期至：<?php  echo date('Y-m-d H:i',$row['expire_time'])?><?php  } ?></div>
							<div class="equity"><?php  if($row['shipping']) { ?>免费包邮·<?php  } ?><?php  if($row['discount_rate']) { ?><?php  echo $row['discount_rate'];?>折优惠·<?php  } ?><?php  if($row['is_card_coupon']) { ?>开卡送券·<?php  } ?><?php  if($row['is_month_coupon']) { ?>每月领券·<?php  } ?>更多特权</div>
						</div>
					</a>
				</div>
				<?php  } } ?>

		<div class="fui-loading empty">
			<div class="text">没有更多了</div>
		</div>
		<?php  } ?>
				<!--缺省页-->
				<?php  if(empty($list_my)) { ?>
				<div class='card-blank' style="display: block">
					<div class='shadow'>
						<i style="font-size: 5rem;color: #f3f3f3;" class="icon icon-queshengye"></i>
					</div>
					<div class='text'>暂无可用会员卡</div>
					<a  class='cardbtn'>去购买</a>
				</div>
				<?php  } ?>

			</div>



		</div>

	</div>

</div>
<script>
    require(['../addons/ewei_shopv2/plugin/membercard/static/js/index.js'], function (modal) {
        modal.init({fromDetail: false,cate:'<?php  echo $cate;?>',token:'<?php  echo $_W['token'];?>'});
    });
</script>
