<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>
<div class="page-header" style="padding-bottom: 20px">
    <span class="text-primary" style="margin-top: 10px">任务管理</span>
</div>

<div class="page-content">
    <div class="page-toolbar row m-b-sm m-t-sm">
        <div class="col-sm-4">
            <?php if(cv('task.add')) { ?>
            <span class="pull-left">
                <a class="btn btn-primary btn-sm" href="<?php  echo webUrl('task.post')?>"><i class="fa fa-plus"></i> 添加任务</a>
            </span>
            <?php  } ?>
        </div>

        <form action="<?php  echo webUrl('task.tasklist')?>" type="get">
            <input type="hidden" name="c" value="site">
            <input type="hidden" name="a" value="entry">
            <input type="hidden" name="m" value="ewei_shopv2">
            <input type="hidden" name="do" value="web">
            <input type="hidden" name="r" value="task.tasklist">
            <div class="col-sm-4 pull-right">
                <!--<select name="type" class="form-control  input-sm select-md" style="width:85px;padding:0;">-->
                    <!--<option value="">任务类型</option>-->
                    <?php  if(is_array($this->model->taskType)) { foreach($this->model->taskType as $type) { ?>
                    <!--<option value="<?php  echo $type['type_key'];?>"><?php  echo $type['type_name'];?></option>-->
                    <?php  } } ?>
                <!--</select>-->
                <div class="input-group">
                    <input type="text" class="input-sm form-control" name="keyword" value="" placeholder="请输入任务名称"> <span class="input-group-btn">
                    <button class="btn btn-sm btn-primary" type="submit"> 搜索</button> </span>
                </div>
            </div>
        </form>

    </div>

    <form action="" method="post">
        <table class="table table-hover table-responsive">
            <thead>
            <tr>
                <!--<th style="width:25px;text-align: center;"><input type="checkbox"></th>-->
                <th style="width:50px;text-align: center;">排序</th>
                <th style="width:70px;">任务标题</th>
                <th style="width:150px;text-align: center;"></th>
                <th style="width:100px;text-align: center;">任务类型</th>
                <th style="width: 150px;text-align: center;">开启时间 / 关闭时间</th>
                <th style="width: 100px;text-align: center;">状态</th>
                <th style="width: 100px;text-align: center;">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php  if(empty($list)) { ?>
                <tr>
                    <td colspan="7" style="text-align: center;border-bottom: 1px solid #efefef">没有任何任务</td>
                </tr>
            <?php  } ?>
            <?php  if(is_array($list)) { foreach($list as $no => $task) { ?>
            <tr>
                <!--<td><input type="checkbox" value="<?php  echo $task['id'];?>"></td>-->
                <td style="text-align: center;">
                    <a href="javascript:;" data-toggle="ajaxEdit" data-href="<?php  echo webUrl('task.setdisplayorder',array('id'=>$task['id']));?>"><?php  echo $task['displayorder'];?></a>
                </td>
                <td>
                    <img src="<?php  echo $task['image'];?>" style="width:40px;height:40px;padding:1px;border:1px solid #ccc;">
                </td>
                <td><?php  echo $task['title'];?></td>
                <td style="text-align: center;">
                    <?php  echo $this->whatType($task['type']);?>
                </td>
                <td style="text-align: center;"><?php  echo $task['starttime'];?><br><?php  echo $task['endtime'];?></td>
                <td style="text-align: center;">
                    <?php  echo $this->whatStatus($task);?>
                </td>
                <td style="text-align: center;">
                    <?php if(cv('task.edit')) { ?>
                    <a class="btn btn-default btn-sm" href="<?php  echo webUrl('task.post',array('id'=>$task['id']));?>" title="编辑">
                        <i class="fa fa-edit"></i></a><?php  } ?>
                    <?php if(cv('task.delete')) { ?>
                    <a class="btn btn-default btn-sm" data-toggle="ajaxRemove"
                       href="<?php  echo webUrl('task.delete',array('ids'=>$task['id']))?>" data-confirm="确定要删除该任务吗？" title="删除">
                        <i class="fa fa-trash"></i></a><?php  } ?>
                </td>
            </tr>
            <?php  } } ?>
            </tbody>
        </table>
        <div class="pull-right"><?php  echo $page;?></div>

    </form>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>