<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>
<style type='text/css'>
	.trhead td {  background:#efefef;text-align: center}
	.trbody td {  text-align: center; vertical-align:top;border-left:1px solid #f2f2f2;overflow: hidden; font-size:12px;}
	.trorder { background:#f8f8f8;border:1px solid #f2f2f2;text-align:left;}
	.ops { border-right:1px solid #f2f2f2; text-align: center;}
	.panel-default .panel-heading {height: 34px; line-height: 34px; padding: 0 10px; color: #666;}
	.panel-default {margin-bottom: 10px;}
	.btn.btn-disabled,
	.btn.btn-disabled:hover,
	.btn.btn-disabled:focus {background: #ccc; color: #fff;}
	.select-1 {margin-bottom: 15px;}
	.select-1 .input-group-select {width: 15%;}
	.select-1 .input-group-select select.form-control {width: 100%;}
</style>
<div class="page-header">
	当前位置：<span class="text-primary">单个打印</span>
</div>

<div class="page-content transparent">

	<div class="panel panel-default">
		<div class="panel-heading">打印条件</div>
		<div class="panel-body">
			<div class="col-sm-12 select-1">
				<div class='input-group'>
					<span class="input-group-select">
					<?php  echo tpl_form_field_eweishop_daterange('time', array('starttime'=>date('Y-m-d H:i', $starttime),'endtime'=>date('Y-m-d H:i', $endtime)),true);?>
				</span>
					<span class="input-group-select">
					<select name='searchtime'  class='form-control'    >
						<option value=''>不按时间</option>
						<option value='create'>下单时间</option>
						<option value='pay'>付款时间</option>
						<option value='send'>发货时间</option>
					</select>
				</span>
				<span class="input-group-select">
					<select name="paytype" class="form-control">
						<option value="" <?php  if($_GPC['paytype']=='') { ?>selected<?php  } ?>>支付方式</option>
						<?php  if(is_array($paytype)) { foreach($paytype as $key => $type) { ?>
						<option value="<?php  echo $key;?>"><?php  echo $type['name'];?></option>
						<?php  } } ?>
					</select>
				</span>
					<span class="input-group-select">
					<select name="status" class="form-control">
						<option value="">订单状态</option>
						<?php  if(is_array($orderstatus)) { foreach($orderstatus as $key => $type) { ?>
						<option value="<?php  echo $key;?>" <?php  if($key==1) { ?> selected="selected" <?php  } ?>><?php  echo $type['name'];?></option>
						<?php  } } ?>
					</select>
				</span>
					<span class="input-group-select">
					<select name="printstate" class="form-control">
						<option value="">快递单打印状态</option>
						<option value='0'>未打印</option>
						<option value='1'>部分打印</option>
						<option value='2'>打印完成</option>
					</select>
				</span>
					<span class="input-group-select">
					<select name="printstate2" class="form-control">
						<option value="">发货单打印状态</option>
						<option value='0'>未打印</option>
						<option value='1'>部分打印</option>
						<option value='2'>打印完成</option>
					</select>
				</span>


				</div>
			</div>
			<div class="col-sm-12">
				<div class='input-group'>
					<span class="input-group-select">
						<select name='searchfield'  class='form-control'>
							<option value='ordersn'>订单号</option>
							<option value='member'>会员信息</option>
							<option value='address'>收件人信息</option>
							<option value='expresssn'>快递单号</option>
							<option value='goodstitle'>商品名称</option>
							<option value='goodssn'>商品编码</option>
						</select>
					</span>
					<input type="text" class="form-control"  name="keyword1" value="<?php  echo $_GPC['keyword1'];?>" placeholder="请输入关键词"/>
					<span class="input-group-btn">
						<span class="btn btn-primary" onclick="search()" id="search-btn"> 搜索</span>
					</span>
				</div>
			</div>
		</div>
	</div>

	<div id="result">
		<div class="panel panel-default" id="result-tip">
			<div class="panel-heading">订单列表</div>
			<div class="panel-body" style="line-height: 100px; text-align: center;">请先搜索您要打印的订单</div>
		</div>
	</div>
</div>

<script language='javascript' src="../addons/ewei_shopv2/plugin/exhelper/static/js/LodopFuncs.js"></script>
<script src='<?php  echo $lodopUrl;?>'></script>


<script language="javascript">
	$(function(){
		window.isCLodop = true;
		if(typeof getCLodop ==='undefined'){
			window.isCLodop = false;
			window.LodopTip = "打印控件错误：\r\n未开启打印控件或配置端口不正确！\r\n检查以上两项后刷新页面重试！";
			alert(LodopTip);
		}
	});
	// 执行搜索 
    function search(){
        var data = {
        	paytype: $.trim($("select[name='paytype'] option:selected").val()),
        	status: $.trim($("select[name='status'] option:selected").val()),//
        	printstate: $.trim($("select[name='printstate'] option:selected").val()),
        	printstate2: $.trim($("select[name='printstate2'] option:selected").val()),//
        	searchtime: $.trim($("select[name='searchtime'] option:selected").val()),
        	starttime: $.trim($("input[name='time[start]']").val()),
        	endtime: $.trim($("input[name='time[end]']").val()),//
        	searchfield: $.trim($("select[name='searchfield'] option:selected").val()),
        	keyword1: $.trim($("input[name='keyword1']").val())
        };
        
        $('#search-btn').html("<i class='fa fa-spinner fa-spin'></i> 正在搜索"); 
        $("#result-tip .panel-body").html("<i class='fa fa-spinner fa-spin'></i> 正在搜索...");
        
        $.ajax({
            url: "<?php  echo webUrl('exhelper/print/single/getdata')?>",
            data: data,
            type: "post",
            success:function(html){
            	$('#search-btn').html("<i class='fa fa-search'></i> 搜索")
                $('#result').html(html);
                
                $('.result-item').click(function(){
                       $('#result-order').html("<p style='line-height: 100px; text-align: center;'><i class='fa fa-spinner fa-spin'></i> 正在加载...</p>")
                    $.ajax({
                          url: "<?php  echo webUrl('exhelper/print/single/getorder')?>",
                          data: {orderids: $(this).data('orderids')},
                          type: "post",
                          success:function(html){
                              $('#result-order').html(html);
                              require(['bootstrap'],function(){
                              	$("[data-toggle='popover']").popover({'container':$(document.body)});
                              })
                          }
                      });

                  })
                  
            }
        });
    }  
</script>

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>
