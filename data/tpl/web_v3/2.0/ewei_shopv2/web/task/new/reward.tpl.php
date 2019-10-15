<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>
<div class="page-header">
    <span class="text-primary">奖励记录</span>
</div>
<div class="page-content">
    <div class="main">
        <div class="page-toolbar row m-b-sm m-t-sm">
            <form action="" method="get">
                <input type="hidden" name="c" value="site">
                <input type="hidden" name="a" value="entry">
                <input type="hidden" name="m" value="ewei_shopv2">
                <input type="hidden" name="do" value="web">
                <input type="hidden" name="r" value="task.reward">
                <div class="col-sm-6">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-default btn-sm" type="button" data-toggle="refresh"><i class="fa fa-refresh"></i></button>
                        <div class="btn btn-default btn-sm">奖励时间</div>
                        <?php  echo tpl_form_field_eweishop_daterange('time', array('starttime'=>date('Y-m-d H:i'),'endtime'=>date('Y-m-d H:i')),true);?>
                    </div>
                </div>
                <div class="col-sm-5 pull-right">
                    <div class="input-group">
                        <input type="text" class="form-control input-sm" name="keyword" value="" placeholder="搜索任务名称">
                        <span class="input-group-btn">
                            <button class="btn btn-sm btn-primary" type="submit" id="so"> 搜索</button>
                        </span>
                    </div>
                </div>
            </form>
        </div>
        <div class="panel-default">
            <div class="panel-body table-responsive" style="padding:0;">
                <table class="table table-hover table-bordered">
                    <thead class="navbar-inner">
                    <tr>
                        <th style="width:130px; text-align: center;">任务名称</th>
                        <th style="width:70px; text-align: center;">任务类型</th>
                        <th style="width:80px;text-align: center;">发起人</th>
                        <th style="width:80px;text-align: center;">奖励人</th>
                        <th style="width:130px;text-align: center;">奖励时间/领取时间<br></th>
                        <th style="width:70px;text-align: center;">奖励类型</th>
                        <th style="width:100px;text-align: center;">奖励详情</th>
                        <th style="width:60px;text-align: center;">领取状态</th>
                    </tr>
                    </thead>
                    <tbody style="text-align: center;">
                    <?php  if(empty($rewards)) { ?>
                    <tr><td colspan="8">没有任何奖励记录</td></tr>
                    <?php  } ?>
                    <?php  if(is_array($rewards)) { foreach($rewards as $reward) { ?>
                    <tr>
                        <td><a data-toggle="ajaxModal" href="<?php  echo webUrl('task.preview',array('id'=>$reward['recordid']));?>"><?php  echo $reward['tasktitle'];?></a></td>
                        <td><?php  echo $this->whatType($reward['tasktype'])?></td>
                        <td><a title="<?php  echo $reward['ownernickname'];?>"><?php  echo $reward['ownernickname'];?></a></td>
                        <td><a title="<?php  echo $reward['nickname'];?>"><?php  echo $reward['nickname'];?></a></td>
                        <td><?php  echo $reward['gettime'];?><br><?php  echo $reward['senttime'];?></td>
                        <td><?php  echo $this->whatRewardType($reward['reward_type'])?></td>
                        <td><a title="<?php  echo $reward['reward_title'];?>"><?php  echo $reward['reward_title'];?></a></td>
                        <td><?php  echo $this->whatRewardStatus($reward)?></td>
                    </tr>
                    <?php  } } ?>
                    </tbody>
                </table>
                <?php  echo $page;?>
                <div style="text-align:right;width:100%;"></div>
            </div>
        </div>
    </div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>