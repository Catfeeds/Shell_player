<?php defined('IN_IA') or exit('Access Denied');?><div class="col-sm-12" style="padding: 0;">
	<div class='panel panel-default col-sm-3' style="width: 15%;">
		<div class="panel-heading">搜索结果 (收件人姓名)</div>
		<div class='panel-body' style='padding: 5px;' id="result-left">
			<div class="panel-body" style="min-height:100px; height: 500px; overflow-y: auto; padding: 0;">
				<table class="table table-hover" style="width: auto; min-width: 100%; margin: 0;">
					<?php  if(!empty($list)) { ?>
					<?php  if(is_array($list)) { foreach($list as $row) { ?>
					<tr style="cursor: pointer;">
						<td class="result-item" data-orderids="<?php  echo implode(',',$row['orderids'])?>"><?php  echo $row['realname'];?></td>
					</tr>
					<?php  } } ?>
					<?php  } else { ?>
					<p style="text-align: center; line-height: 100px; padding:10px;">抱歉！未查找到相关数据。</p>
					<?php  } ?>
				</table>
			</div>
		</div>
	</div>
	<div class="col-sm-9" style="padding-right: 0;width: 85%;">
		<div class='panel panel-default '>
			<div class="panel-heading">订单信息</div>
			<div class='panel-body' id="result-order">
				<p style="text-align: center; line-height: 100px;">
					<?php  if(!empty($list)) { ?> 请先选择左侧搜索结果 <?php  } else { ?> 抱歉！未查到相关数据。 <?php  } ?>
				</p>
			</div>
		</div>
	</div>
</div>
<!--NDAwMDA5NzgyNw==-->