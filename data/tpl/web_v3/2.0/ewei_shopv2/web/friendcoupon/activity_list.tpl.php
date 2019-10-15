<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>

<div class="page-header" style="padding-bottom: 20px">
    当前位置：<span class="text-primary" style="margin-top: 10px">活动列表</span>
</div>

<div class="page-content">
    <div class="page-toolbar row m-b-sm m-t-sm">
        <div class="col-sm-4">
            <?php if(cv('friendcoupon.activity_list.add')) { ?>
            <span class="pull-left">
                <a class="btn btn-primary btn-sm" href="<?php  echo webUrl('friendcoupon.add')?>"><i class="fa fa-plus"></i> 创建活动</a>
            </span>
            <?php  } ?>
        </div>

        <form action="<?php  echo webUrl('friendcoupon.add')?>" type="get">
            <input type="hidden" name="c" value="site">
            <input type="hidden" name="a" value="entry">
            <input type="hidden" name="m" value="ewei_shopv2">
            <input type="hidden" name="do" value="web">
            <input type="hidden" name="r" value="friendcoupon.activity_list">

            <div class="col-sm-4 pull-right">
                <div class="input-group">
                    <div class="input-group-select">
                        <select name="type" class='form-control'>
                            <option value="1" <?php  if($_GPC['type'] == '1') { ?> selected<?php  } ?>>进行中</option>
                            <option value="0" <?php  if($_GPC['type'] == '0') { ?> selected<?php  } ?>>未开始</option>
                            <option value="-1" <?php  if($_GPC['type'] == '-1') { ?> selected<?php  } ?>>已结束</option>
                            <option value="-2" <?php  if($_GPC['type'] == '-2') { ?> selected<?php  } ?>>手动停止</option>
                        </select>
                    </div>
                    <input type="text" class="input-sm form-control" name="keyword" value="" placeholder="请输入活动名称"> <span class="input-group-btn">
                    <button class="btn btn-sm btn-primary" type="submit"> 搜索</button> </span>
                </div>
            </div>
        </form>

    </div>

    <form action="" method="post">
        <table class="table table-hover table-responsive center-align">
            <thead>
            <tr>
                <!--<th style="width:25px;text-align: center;"><input type="checkbox"></th>-->
                <th style="width:150px;">活动名称</th>
                <th style="width:100px;">内容</th>
                <th style="width:150px;text-align: center;"></th>
                <th style="width:100px;text-align: center;">活动时间</th>
                <th style="width: 150px;text-align: center;">停止时间</th>
                <th style="width: 100px;text-align: center;">状态</th>
                <th style="width: 130px;text-align: center;">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php  if(empty($list)) { ?>
            <tr>
                <td colspan="7" style="text-align: center;border-bottom: 1px solid #efefef">没有任何活动</td>
            </tr>
            <?php  } ?>
            <?php  if(is_array($list)) { foreach($list as $key => $activity) { ?>
            <tr>
                <!--<td><input type="checkbox" value="<?php  echo $task['id'];?>"></td>-->
                <td>
                    <a href="javascript:;" data-href="<?php  echo webUrl('task.setdisplayorder',array('id'=>$task['id']));?>"><?php  echo $activity['title'];?></a>
                </td>
                <td>
                    <?php  echo $activity['people_count'];?>人瓜分<?php  echo $activity['coupon_money'];?>元
                </td>
                <td></td>
                <td style="text-align: center;">
                    <?php  echo date("Y-m-d H:i:s", $activity['activity_start_time'])?> <br> <?php  echo date("Y-m-d H:i:s", $activity['activity_end_time'])?>
                </td>
                <td style="text-align: center;"> <?php echo $activity['stop_time']?  date("Y-m-d H:i:s", $activity['stop_time']) : "-"?> </td>
                <td style="text-align: center;">
                    <?php  if($activity['status']== -1) { ?>
                        <?php  if($activity['stop_time']) { ?>
                            手动停止
                        <?php  } else { ?>
                            已结束
                        <?php  } ?>
                    <?php  } else if($activity['status'] == 0) { ?>
                        未开始
                    <?php  } else if($activity['status'] == 1) { ?>
                        进行中
                    <?php  } ?>
                </td>
                <td style="text-align: center;">
                    <?php if(cv('friendcoupon.activity_list.edit')) { ?>
                    <a class="btn btn-default btn-sm" href="<?php  echo webUrl('friendcoupon.post',array('id'=>$activity['id']));?>" title="编辑">
                        <i class="fa fa-edit"></i></a>
                    <?php  } ?>
                    <?php if(cv('friendcoupon.activity_list.copy')) { ?>
                    <a class="btn btn-default btn-sm" href="<?php  echo webUrl('friendcoupon.copy',array('cp_id'=>$activity['id']))?>">
                        <i class="fa fa-copy"></i></a>
                    <?php  } ?>
                    <a href="javascript:void(0)" class="btn btn-default btn-sm" data-toggle="popover" data-trigger="hover" data-html="true"
                       data-content="<img width='130px' src='<?php  echo m('qrcode')->createQrcode($activity['codeUrl'])?>'>"
                       href="<?php  echo webUrl('friendcoupon.delete',array('id'=>$activity['id']))?>"
                       alt="链接二维码" >
                        <i class="glyphicon glyphicon-qrcode"></i>
                    </a>
                    <?php if(cv('friendcoupon.activity_list.stop')) { ?>
                    <a class="btn btn-default btn-sm" data-toggle="ajaxRemove"
                       href="<?php  echo webUrl('friendcoupon.stop',array('id'=>$activity['id']))?>" data-confirm="确定要停止该任务吗？所有进行中的活动都会停止" title="停止">
                        <i class="fa fa-stop"></i></a>
                    <?php  } ?>
                    <?php if(cv('friendcoupon.activity_list.delete')) { ?>
                    <a class="btn btn-default btn-sm" data-toggle="ajaxRemove"
                       href="<?php  echo webUrl('friendcoupon.delete',array('id'=>$activity['id']))?>" data-confirm="确定要删除该任务吗？" title="删除">
                        <i class="fa fa-trash"></i></a>
                    <?php  } ?>

                </td>
            </tr>
            <?php  } } ?>
            </tbody>
        </table>
        <div class="pull-right"><?php  echo $pager;?></div>

    </form>
</div>

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>