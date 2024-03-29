<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>
<div class="page-header">
    当前位置：<span class="text-primary"><?php  if(empty($_GPC['id'])) { ?>添加<?php  } else { ?>编辑<?php  } ?>活动</span>
</div>

<script language='javascript' src="../addons/ewei_shopv2/plugin/task/static/js/designer.js"></script>

<style type='text/css'>
    #task {
        width:320px;height:504px;border:1px solid #ccc;position:relative
    }
    #task .bg { position:absolute;width:100%;z-index:0}
    #task .drag[type=img] img,#task .drag[type=thumb] img { width:100%;height:100%; }
    <?php if( ce('task' ,$item) ) { ?>
    #task .drag { position: absolute; width:80px;height:80px; border:1px solid #000; }
    <?php  } else { ?>
    #task .drag { position: absolute; width:80px;height:80px; }
    <?php  } ?>

    #task .drag[type=nickname],#task .drag[type=time] { width:80px;height:40px; font-size:16px; font-family: 黑体;}
    #task .drag img {position:absolute;z-index:0;width:100%; }

    #task .rRightDown,.rLeftDown,.rLeftUp,.rRightUp,.rRight,.rLeft,.rUp,.rDown{
        position:absolute;
        width:7px;
        height:7px;
        z-index:1;
        font-size:0;
    }

    <?php if( ce('task' ,$item) ) { ?>
    #task .rRightDown,.rLeftDown,.rLeftUp,.rRightUp,.rRight,.rLeft,.rUp,.rDown{
        background:#C00;
    }
    <?php  } ?>
    .rLeftDown,.rRightUp{cursor:ne-resize;}
    .rRightDown,.rLeftUp{cursor:nw-resize;}
    .rRight,.rLeft{cursor:e-resize;}
    .rUp,.rDown{cursor:n-resize;}
    .rLeftDown{left:-4px;bottom:-4px;}
    .rRightUp{right:-4px;top:-4px;}
    .rRightDown{right:-4px;bottom:-4px;}
    <?php if( ce('task' ,$item) ) { ?>
    .rRightDown{background-color:#00F;}
    <?php  } ?>

    .rLeftUp{left:-4px;top:-4px;}
    .rRight{right:-4px;top:50%;margin-top:-4px;}
    .rLeft{left:-4px;top:50%;margin-top:-4px;}
    .rUp{top:-4px;left:50%;margin-left:-4px;}
    .rDown{bottom:-4px;left:50%;margin-left:-4px;}
    .context-menu-layer { z-index:9999;}
    .context-menu-list { z-index:9999;}

</style>
<div class="page-content">
    <div class="page-sub-toolbar">
        <span class=''>
		<?php if(cv('friendcoupon.activity_list.add')) { ?>
                <a class="btn btn-primary btn-sm" href="<?php  echo webUrl('friendcoupon/add')?>">添加活动</a>
		<?php  } ?>
	</span>
    </div>
    <form action="" method="post" class="form-horizontal form-validate" enctype="multipart/form-data">

        <input type="hidden" name="c" value="site" />
        <input type="hidden" name="a" value="entry" />
        <input type="hidden" name="m" value="ewei_shopv2" />
        <input type="hidden" name="do" value="web" />
        <input type="hidden" name="r"  value="friendcoupon.post" />
        <input type="hidden" name="id" value="<?php  echo $activity['id'];?>" />
        <ul class="nav nav-arrow-next nav-tabs" id="myTab">
            <li <?php  if($_GPC['tab']=='basic' || empty($_GPC['tab'])) { ?>class="active"<?php  } ?> ><a href="#tab_basic">基本设置</a></li>
            <li <?php  if($_GPC['tab']=='design') { ?>class="active"<?php  } ?> ><a href="#tab_limit">使用限制</a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane  <?php  if($_GPC['tab']=='basic' || empty($_GPC['tab'])) { ?>active<?php  } ?>" id="tab_basic"><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('friendcoupon/tab/basic', TEMPLATE_INCLUDEPATH)) : (include template('friendcoupon/tab/basic', TEMPLATE_INCLUDEPATH));?></div>
            <div class="tab-pane  <?php  if($_GPC['tab']=='limit') { ?>active<?php  } ?>" id="tab_limit"><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('friendcoupon/tab/limit', TEMPLATE_INCLUDEPATH)) : (include template('friendcoupon/tab/limit', TEMPLATE_INCLUDEPATH));?></div>
        </div>

        <div class="form-group"></div>
        <div class="form-group">
            <label class="col-lg control-label"></label>
            <div class="col-sm-9 col-xs-12">
                <input type="submit" value="提交" class="btn btn-primary"  />
                <input type="hidden" name="rec_reward_data" value="">
                <input type="hidden" name="sub_reward_data" value="">
                <input type="hidden" name="poster_type" value="1" />
                <input type="hidden" name="data" value="" />
                <input type="button" name="back" onclick='history.back()' <?php if(cv('friendcoupon.activity_list.add|friendcoupon.activity_list.edit')) { ?>style='margin-left:10px;'<?php  } ?> value="返回列表" class="btn btn-default" />
            </div>
        </div>
    </form>
</div>
<script language='javascript'>
    require(['bootstrap'],function(){
        $('#myTab a').click(function (e) {
            e.preventDefault();
            $('#tab').val( $(this).attr('href'));
            $(this).tab('show');
        })
    });
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>
