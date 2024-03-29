<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>
<style>
	.pre {
		white-space: pre-wrap;
		white-space: -moz-pre-wrap;
		white-space: -pre-wrap;
		white-space: -o-pre-wrap;
		*word-wrap: break-word;
		*white-space : normal ;
	}
</style>
<link rel="stylesheet" type="text/css" href="../addons/ewei_shopv2/plugin/membercard/static/css/swiper-3.4.2.min.css">
<link rel="stylesheet" type="text/css" href="../addons/ewei_shopv2/plugin/membercard/static/css/detail.css">

<div class='fui-page creditshop-index-page'>
	<div class="fui-header">
		<div class="fui-header-left">
			<a class="back"></a>
		</div>
		<div class="title head-card-name">会员卡详情</div>
		<div class="fui-header-right"></div>
	</div>

	<div class="fui-content navbar" style="bottom: 0">
		<div class='card-swiper'>
			<div class="swiper-container">
				<div class="swiper-wrapper">
					<?php  if($card_list) { ?>
					<?php  if(is_array($card_list)) { foreach($card_list as $index => $row) { ?>
					<div class="swiper-slide" data-id="<?php  echo $row['id'];?>" data-k="<?php  echo $index;?>">
						<div class="clubcard <?php  echo $row['card_style'];?>">
							<i class="icon-bg icon icon-huangguan-line"></i>
							<div class='content'>
								<div class='icon'>  <img src="../addons/ewei_shopv2/static/images/icon-white.png"  alt=""/></div>
								<div class='title'><?php  echo $row['name'];?></div>
								<?php  if($row['expire']) { ?>
								<?php  if($row['expire']=='-1') { ?>
								<div class='subtitle'>有效期:永久有效</div>
								<?php  } else { ?>
								<div class='subtitle'>有效期至:<?php  echo $row['expire'];?></div>
								<?php  } ?>

								<?php  } else { ?>
								<div class='subtitle'>享受<?php  echo $row['quanyi'];?>项特权</div>
								<?php  } ?>
								<?php  if($row['kaitong']) { ?>
								<div class='opencard buybtn'><?php  if($row['chongxin_kaitong']) { ?>重新购买<?php  } else { ?>立即开通<?php  } ?></div>
								<?php  } else { ?>
								<?php  if($row['expire_time']!='-1') { ?>
								<div class='opencard buybtn'>续费</div>
								<?php  } ?>
								<?php  } ?>
							</div>
						</div>
					</div>
					<?php  } } ?>
					<?php  } ?>




				</div>
			</div>
		</div>

		<div class='fui-content-inner'>
			<div class='container'></div>
			<!--<div class='infinite-loading'><span class='fui-preloader'></span><span class='text'> 正在加载...</span></div>-->
		</div>

		<script id='tpl_order_index_list' type='text/html'>

			<div class='card-group nomargin'>
				<div class='card-title'>会员特权</div>

				<div class='card-btn-group'>
					<%each list.quanyi as card%>
					<div class='card-btn-item'>
						<div class='card-btn-icon'>
							<div class='icon'>
								<i class="icon <%card.icon%>"></i>
							</div>
						</div>
						<div class='card-btn-text'><%card.text%></div>
					</div>
					<%/each%>
				</div>
			</div>
			<%if list.discount_rate%>
			<div class='card-group' style='padding-bottom: 0.85rem;'>
				<div class='card-title'>会员折扣</div>
				<div class='card-subtitle'>会员可享受折扣价格</div>
				<div class='card-fiche gary'>
					<div class='fiche-icon'>
						<i class="icon icon-huiyuanzhuanxiangzhekou"></i>
					</div>
					<div class='fiche-inner'>
						<div class='title'>会员专享折扣</div>
					</div>
					<div class='fiche-btn'>全场<span><%list.discount_rate%></span>折</div>
				</div>
			</div>
			<%/if%>
			<!--开通之前样式开始-->
			<%if list.goumai==1%>
			<%if list.coupon_card.length>0||list.is_card_points>0%>
			<div class='card-group'>
				<div class='card-title'>开卡赠送</div>
				<div class='card-subtitle'>会员开卡即送<%if list.coupon_card.length>0%>优惠券<%/if%><%if list.coupon_card.length>0&&list.is_card_points>0 %>、<%/if%><%if list.is_card_points>0%>积分<%/if%></div>
				<%if list.coupon_card.length>0%>
				<div class='coupon-list left'>
					<%each list.coupon_card as kai_card%>
					<div class='coupon-list-item'>
						<div class='circle-l'></div>
						<div class='circle-r'></div>
						<div class='coupon-inner'>
							<%if kai_card.backtype==0%>
							<div class='price'>立减￥
								<span><%kai_card.deduct%></span>
							</div>
							<%/if%>
							<%if kai_card.backtype==1%>
							<div class='price'>打
								<span><%kai_card.discount%></span>折
							</div>
							<%/if%>
							<%if kai_card.backtype==2%>
							<div class='price'><%kai_card.tagtitle%></div>
							<%if kai_card.backmoney>0%>
							<div>购物返<span><%kai_card.backmoney%></span>余额</div>
							<%/if%>
							<%if kai_card.backcredit>0%>
							<div>购物返<span><%kai_card.backcredit%></span>积分</div>
							<%/if%>
							<%if kai_card.backredpack>0%>
							<div>购物返<span><%kai_card.backredpack%></span>现金</div>
							<%/if%>
							<%/if%>

							<!--<%if kai_card.enough<=0 %>
							<div class='title'><%kai_card.couponname%></div>
							<%/if%>-->
							<%if kai_card.enough>0 %>
							<div class='explain'>满<%kai_card.enough%>元可用</div>
							<%else%>
							<div class='explain'><%kai_card.title2%></div>
							<%/if%>
							<div class='condition'><%kai_card.use_limit%></div>
						</div>
					</div>

					<%/each%>
				</div>
				<%/if%>
				<%if list.is_card_points>0%>
				<div class='card-fiche golden' style='margin-top: 0;'>
					<div class='fiche-icon'>
						<i class="icon icon-kaiqiajifen"></i>
					</div>
					<div class='fiche-inner'>
						<div class='title'>开通立享积分</div>
						<div class='subtitle'><%list.card_points%>
							<span>积分</span>
						</div>
					</div>
					<div class='fiche-btn'>
						<%if list.kaika_jifen%>
						<div class='btn-submit border'>已发放</div>
						<%else%>
						<div class='btn-submit border'>开通送积分</div>
						<%/if%>
					</div>
				</div>
				<%/if%>

			</div>
			<%/if%>

			<%if list.coupon_month.length>0||list.is_month_points>0%>
			<div class='card-group' style='padding-bottom: 0.85rem;'>
				<div class='card-title'>每月领取</div>
				<div class='card-subtitle'>会员可每月领取<%if list.coupon_month.length>0%>优惠券<%/if%><%if list.coupon_month.length>0&&list.is_month_points>0%>、<%/if%><%if list.is_month_points>0%>积分<%/if%></div>
				<%if list.coupon_month.length>0%>
				<div class='coupon-list left'>

					<%each list.coupon_month as kai_month_card%>
					<div class='coupon-list-item'>
						<div class='circle-l'></div>
						<div class='circle-r'></div>
						<div class='coupon-inner'>
							<%if kai_month_card.backtype==0%>
							<div class='price'>立减￥
								<span><%kai_month_card.deduct%></span>
							</div>
							<%/if%>
							<%if kai_month_card.backtype==1%>
							<div class='price'>打
								<span><%kai_month_card.discount%></span>折
							</div>
							<%/if%>
							<%if kai_month_card.backtype==2%>
							<div class='price'><%kai_month_card.tagtitle%></div>
							<%if kai_month_card.backmoney>0%>
							<div>购物返<span><%kai_month_card.backmoney%></span>余额</div>
							<%/if%>
							<%if kai_month_card.backcredit>0%>
							<div>购物返<span><%kai_month_card.backcredit%></span>积分</div>
							<%/if%>
							<%if kai_month_card.backredpack>0%>
							<div>购物返<span><%kai_month_card.backredpack%></span>现金</div>
							<%/if%>
							<%/if%>
							<%if kai_month_card.enough>0 %>
							<div class='explain'>满<%kai_month_card.enough%>元可用</div>
							<%else%>
							<div class='explain'><%kai_month_card.title2%></div>
							<%/if%>
							<div class='condition'><%kai_month_card.use_limit%></div>
						</div>
					</div>

					<%/each%>

				</div>
				<%/if%>
				<%if list.is_month_points>0%>
				<div class='card-fiche golden' style='margin-top: 0;'>


					<div class='fiche-icon'>
						<!--已经领取的样式  -->
						<%if list.is_check_month_point%>
						<i class="icon icon-huangguan-line"></i>
						<%else%>
						<i class="icon icon-meiyuejifen"></i>
						<%/if%>
					</div>

					<div class='fiche-inner'>
						<div class='title'>会员每月领取积分</div>
						<div class='subtitle'><%list.month_points%>
							<span>积分</span>
						</div>
					</div>
					<div class='fiche-btn'>
						<%if list.goumai==1%>
						<div class='btn-submit border'>每月领积分</div>
						<%else%>
						<%if list.is_check_month_point%>
						<div class='btn-submit border'>已领取</div>
						<%else%>
						<div class='btn-submit border btn-month-points'>立即领取</div>
						<%/if%>
						<%/if%>

					</div>
				</div>
				<%/if%>
			</div>
			<%/if%>
			<%else%>
			<!--开通之前样式结束-->
			<!-- 开通之后样式开始-->
			<%if list.coupon_month.length>0||list.is_month_points%>
			<div class='card-group' style='padding-bottom: 0.85rem;'>
				<div class='card-title'>每月领取</div>
				<div class='card-subtitle'>会员可每月领取<%if list.coupon_month.length>0%>优惠券<%/if%><%if list.is_month_points>0&&list.coupon_month.length>0%>、<%/if%><%if list.is_month_points>0%>积分<%/if%></div>
				<%if list.coupon_month.length>0%>
				<div class='coupon-list left'>
					<%each list.coupon_month as kai_month_card%>
					<div class='coupon-list-item already'>
						<div class='circle-l'></div>
						<div class='circle-r'></div>
						<div class='coupon-inner'>
							<div class='coupon-inner-media'>
								<%if kai_month_card.backtype==0%>
								<div class='price'>￥<span><%kai_month_card.deduct%></span></div>
								<%/if%>
								<%if kai_month_card.backtype==1%>
								<div class='price'>打<span><%kai_month_card.discount%></span>折</div>
								<%/if%>

								<%if kai_month_card.backtype==2%>
								<div class='price'><span style="font-size: 0.85rem">返现</span></div>
								<%if kai_month_card.backmoney>0%>
								<div>购物返<span><%kai_month_card.backmoney%></span>余额</div>
								<%/if%>
								<%if kai_month_card.backcredit>0%>
								<div>购物返<span><%kai_month_card.backcredit%></span>积分</div>
								<%/if%>
								<%if kai_month_card.backredpack>0%>
								<div>购物返<span><%kai_month_card.backredpack%></span>现金</div>
								<%/if%>
								<%/if%>

								<div class='explain'>
									<div class='title'><%kai_month_card.couponname%></div>
									<%if kai_month_card.enough>0 %>
									<div class='subtitle'>满<%kai_month_card.enough%>元可用</div>
									<%else%>
									<div class='subtitle'><%kai_month_card.title2%></div>
									<%/if%>
								</div>
							</div>
							<div class='condition'>
								<%if kai_month_card.coupon_receive%>
								<div class='btn-condition gary'>已领取</div>
								<%else%>
								<div class='btn-condition btn-coupon-receive' data-couponid='<%kai_month_card.id%>'>立即领取</div>
								<%/if%>
							</div>

						</div>
					</div>
					<%/each%>

				</div>
				<%/if%>
				<%if list.is_month_points%>
				<div class='card-fiche golden' style='margin-top: 0;'>
					<div class='fiche-icon'>
						<!--已经领取的样式  -->
						<%if list.is_check_month_point%>
						<i class="icon icon-huangguan-line"></i>
						<%else%>
						<i class="icon icon-meiyuejifen"></i>
						<%/if%>
					</div>

					<div class='fiche-inner'>
						<div class='title'>会员每月领取积分</div>
						<div class='subtitle'><%list.month_points%>
							<span>积分</span>
						</div>
					</div>
					<div class='fiche-btn'>
						<%if list.goumai==1%>
						<div class='btn-submit border'>每月领积分</div>
						<%else%>
						<%if list.is_check_month_point%>
						<div class='btn-submit border'>已领取</div>
						<%else%>
						<div class='btn-submit border btn-month-points'>立即领取</div>
						<%/if%>
						<%/if%>

					</div>
				</div>
				<%/if%>
			</div>
			<%/if%>
			<!--开卡赠送-->
			<%if list.coupon_card.length>0||list.is_card_points>0%>
			<div class='card-group'>
				<div class='card-title'>开卡赠送</div>
				<div class='card-subtitle'>会员开卡即送<%if list.coupon_card.length>0%>优惠券<%/if%><%if list.coupon_card.length>0&& list.is_card_points>0%>、<%/if%><%if list.is_card_points>0 %>积分<%/if%></div>
				<%if list.coupon_card.length>0%>
				<div class='coupon-list left'>
					<%each list.coupon_card as kai_card%>
					<div class='coupon-list-item already'>
						<div class='circle-l'></div>
						<div class='circle-r'></div>
						<div class='coupon-inner'>
							<div class='coupon-inner-media'>
								<%if kai_card.backtype==0%>
								<div class='price'>￥<span><%kai_card.deduct%></span></div>
								<%/if%>
								<%if kai_card.backtype==1%>
								<div class='price'>打<span><%kai_card.discount%></span>折</div>
								<%/if%>
								<%if kai_card.backtype==2%>
								<div class='price'><span style="font-size: 0.85rem">返现</span></div>
								<%if kai_card.backmoney>0%>
								<div class='price'>购物返<span><%kai_card.backnum%></span>余额</div>
								<%/if%>
								<%if kai_card.backcredit>0%>
								<div class='price'>购物返<span><%kai_card.backcredit%></span>积分</div>
								<%/if%>
								<%if kai_card.backredpack>0%>
								<div class='price'>购物返<span><%kai_card.backredpack%></span>现金</div>
								<%/if%>
								<%/if%>
								<div class='explain'>
									<div class='title'><%kai_card.couponname%></div>
									<%if kai_card.enough>0 %>
									<div class='subtitle'>满<%kai_card.enough%>元可用</div>
									<%else%>
									<div class='subtitle'><%kai_card.title2%></div>
									<%/if%>
								</div>
							</div>
							<%if kai_card.buysend_coupon %>
							<div class='condition coupon-send'>已发放</div>
							<%else%>
							<div class='condition'><%kai_card.use_limit%></div>
							<%/if%>
						</div>
					</div>
					<%/each%>
				</div>
				<%/if%>
				<%if list.is_card_points>0 %>
				<div class='card-fiche golden' style='margin-top: 0;'>
					<div class='fiche-icon'>
						<i class="icon icon-kaiqiajifen"></i>
					</div>
					<div class='fiche-inner'>
						<div class='title'>开通立享积分</div>
						<div class='subtitle'><%list.card_points%>
							<span>积分</span>
						</div>
					</div>
					<div class='fiche-btn'>
						<%if list.kaika_jifen%>
						<div class='btn-submit border'>已发放</div>
						<%else%>
						<div class='btn-submit border'>开通送积分</div>
						<%/if%>
					</div>
				</div>
				<%/if%>

			</div>
			<%/if%>



			<!--开通之后样式结束-->
			<%/if%>



			<%if list.card_description%>
			<!--使用说明 start  -->
			<div class='card-group'>
				<div class='card-title'>使用说明</div>
				<div class='card-explain pre'><%list.card_description%></div>

			</div>
			<!--使用说明 end  -->
			<%/if%>

			<!--提示弹窗 start  -->
			<div class='card-modal' style="display: none">
				<div class='inner'>
					<div class='title'>优惠券</div>
					<div class='text'>每月1号发放，需手动领取</div>
					<div class='text'>尊享会员每月可领取5张优惠券</div>
					<div class='text'>优惠券种类包括折扣券与满减券</div>
					<div class='card-modal-submit submit'>确定</div>
				</div>
			</div>
			<!--提示弹窗 end  -->


			<!--未购买前  -->

			<%if list.goumai==3%>
			<div class='btn-footbar-bj' style="display: none">
				<div class='item'></div>
				<div class='item'></div>
			</div>
			<%else%>
			<div class='btn-footbar-bj'>
				<div class='item'></div>
				<div class='item'></div>
			</div>
			<%/if%>
				<%if list.goumai==1%>
			<div class='btn-footbar' style="">
				<div class='btn-text'>￥
					<span><%list.price%></span> / <%list.validate%></div>

				<div class='btn-submit'><a href="javascript:" class="buybtn" style="color: #ffffff;"><%if list.chongxin_kaitong%>重新购买<%else%>立即开通<%/if%></a>
					<i class="icon icon-jiantou-copy"></i>
				</div>

			</div>
				<%/if%>

			<!-- 续费 -->
			<%if list.goumai==2%>
				<%if list.expire==-1%>
			<div  class='renew'>永久有效</div>
				<%else%>
				<div  class='renew buybtn'>续费</div>
				<%/if%>
			<%/if%>
		</script>


</div>

<script >
    require(['../addons/ewei_shopv2/plugin/membercard/static/js/create.js'], function (modal) {
        modal.init({'token':'<?php  echo $_W['token'];?>',card_count: '<?php  echo count($lists['list'])?>'});
    });
</script>
