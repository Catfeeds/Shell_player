<?php defined('IN_IA') or exit('Access Denied');?><style>
    .time-fix .btn-default{
        margin-left: 23px;
    }
</style>
<div class="form-group">
    <label class="col-lg control-label">使用条件</label>
    <div class="col-sm-9 col-xs-12">
        <div class="input-group">
            <input type="text" name="use_condition" class="form-control" value="<?php  echo $activity['use_condition'];?>" />
            <!--<span class="input-group-addon" style="padding: 0px;"><img src="<?php  if(!empty($activity['titleicon'])) { ?><?php  echo $activity['titleicon'];?><?php  } else { ?>../addons/ewei_shopv2/plugin/task/static/images/taskicon.png<?php  } ?>" id="showimg" width="30px" height="28px"></span>-->
            <!--<span class="input-group-addon" data-toggle="selectImg" data-input="#titleimg" data-img="#showimg" data-full="1">选择图片</span>-->
            <span class="input-group-addon" id="basic-addon2">元</span>
        </div>
    <span class='help-block'>订单满多少元可用，不填或0不限制。</span>
    </div>
</div>

<div class="form-group time-fix">
    <label class="col-lg control-label must">使用时间限制</label>
    <div class="col-sm-9 col-xs-12" >
        <?php if( ce('sale.coupon' ,$activity) ) { ?>
        <div class="input-group" style="display: flex;">
            <label class="radio-inline coupon-radio">
                <input type="radio"  name="use_time_limit" value="0" <?php  if($activity['use_time_limit']==0) { ?>checked<?php  } ?>/> 在瓜分后
            </label>
            <input style="width: 197px;" type="text" class="form-control" name="use_valid_days" value="<?php  echo $activity['use_valid_days'];?>" <?php  if($activity['use_time_limit']==0) { ?>checked<?php  } ?>>
            <span class="input-group-addon" style="width:auto">天内有效</span>
        </div>
        <div class="input-group"  style="display: flex;">

        <label class="radio-inline">
                <input type="radio"  name="use_time_limit" value="1" <?php  if($activity['use_time_limit']==1) { ?>checked<?php  } ?>/> 在日期
                <?php  echo tpl_form_field_eweishop_daterange('use_time', array('starttime'=> substr($activity['use_start_time'], 0, -3) ,'endtime'=>  substr($activity['use_end_time'], 0, -3) ),true);?>
            </label>
        </div>

        <?php  } else { ?> <div class='form-control-static'>
        <?php  if($activity['limitdiscounttype']==0) { ?>无优惠使用限制
        <?php  } else if($activity['limitdiscounttype']==1) { ?>不可与促销优惠同时使用
        <?php  } else if($activity['limitdiscounttype']==2) { ?>不可与会员折扣同时使用
        <?php  } else if($activity['limitdiscounttype']==3) { ?>不可与促销优惠和会员折扣同时使用
        <?php  } ?>
    </div>
        <?php  } ?>
    </div>
</div>


<div class="form-group">
    <label class="col-lg control-label must">优惠限制</label>
    <div class="col-sm-9 col-xs-12" >
        <?php if( ce('sale.coupon' ,$activity) ) { ?>
        <label class="radio-inline coupon-radio">
            <input type="radio" name="limitdiscounttype" value="0" <?php  if($activity['limitdiscounttype'] == 0) { ?>checked="true"<?php  } ?>  /> 无优惠使用限制
        </label>
        <label class="radio-inline coupon-radio">
            <input type="radio" name="limitdiscounttype" value="1" <?php  if($activity['limitdiscounttype'] == 1) { ?>checked="true"<?php  } ?>" /> 不可与促销优惠同时使用
        </label>
        <label class="radio-inline coupon-radio">
            <input type="radio" name="limitdiscounttype" value="2" <?php  if($activity['limitdiscounttype'] == 2) { ?>checked="true"<?php  } ?>  /> 不可与会员折扣同时使用
        </label>
        <label class="radio-inline coupon-radio">
            <input type="radio" name="limitdiscounttype" value="3" <?php  if($activity['limitdiscounttype'] == 3) { ?>checked="true"<?php  } ?>  /> 不可与促销优惠和会员折扣同时使用
        </label>
        <span class='help-block'>优惠券是否可以与特定优惠同时使用</span>

        <?php  } else { ?> <div class='form-control-static'>
        <?php  if($activity['limitdiscounttype']==0) { ?>无优惠使用限制
        <?php  } else if($activity['limitdiscounttype']==1) { ?>不可与促销优惠同时使用
        <?php  } else if($activity['limitdiscounttype']==2) { ?>不可与会员折扣同时使用
        <?php  } else if($activity['limitdiscounttype']==3) { ?>不可与促销优惠和会员折扣同时使用
        <?php  } ?>
    </div>
        <?php  } ?>
    </div>
</div>

<div class="form-group">
    <label class="col-lg control-label must">商品分类限制</label>
    <div class="col-sm-9 col-xs-12" >
        <?php if( ce('sale.coupon' ,$activity) ) { ?>
        <label class="radio-inline coupon-radio">
            <input type="radio" name="limitgoodcatetype" value="0" <?php  if($activity['limitgoodcatetype'] == 0) { ?>checked="true"<?php  } ?>  onclick="$('.selectcats').hide();"/> 不添加商品分类限制
        </label>
        <label class="radio-inline coupon-radio">
            <input type="radio" name="limitgoodcatetype" value="1" <?php  if($activity['limitgoodcatetype'] == 1) { ?>checked="true"<?php  } ?> onclick="$('.selectcats').show();" /> 允许以下商品分类使用
        </label>
        <span class='help-block'>优惠券是否只能用于特定商品或商品类型</span>

        <?php  } else { ?> <div class='form-control-static'>
        <?php  if($activity['limitgoodcatetype']==0) { ?>不添加商品分类限制
        <?php  } else if($activity['limitgoodcatetype']==1) { ?>允许以下商品分类使用
        <?php  } ?>
    </div>
        <?php  } ?>
    </div>
</div>

<div class="form-group selectcats" <?php  if($activity['limitgoodcatetype']!=1&&$activity['limitgoodcatetype']!=2) { ?>style="display:none"<?php  } ?>>
<?php if( ce('sale.coupon' ,$activity) ) { ?>
<div class="form-group" >
    <label class="col-lg control-label">选择商品分类</label>
    <div class="col-sm-9 col-xs-12">
        <select id="cates"  name='cates[]' class="form-control select2" style='width:605px;' multiple='' >
            <?php  if(is_array($goodcategorys)) { foreach($goodcategorys as $c) { ?>
            <p id="uuu">{is_array($cates)}</p>
            <p id="ccc">{in_array($c['id'], $cates)}</p>
            <option value="<?php  echo $c['id'];?>" <?php  if(is_array($cates) &&  in_array($c['id'],$cates)) { ?>selected<?php  } ?> ><?php  echo $c['name'];?></option>
            <?php  } } ?>
        </select>
    </div>
</div>
<?php  } else { ?>
<div class='form-control-static ops'>
    <?php  if(is_array($cates)) { foreach($cates as $c) { ?>
    <a><?php  echo $goodcategorys[$c]['name'];?></a>
    <?php  } } ?>
</div>
<?php  } ?>

</div>

<div class="form-group">
    <label class="col-lg control-label must">商品限制</label>
    <div class="col-sm-9 col-xs-12" >
        <?php if( ce('sale.coupon' ,$activity) ) { ?>
        <label class="radio-inline coupon-radio">
            <input type="radio" name="limitgoodtype" value="0" <?php  if($activity['limitgoodtype'] == 0) { ?>checked="true"<?php  } ?> onclick="$('.selectgoods').hide();" /> 不添加商品限制
        </label>
        <label class="radio-inline coupon-radio">
            <input type="radio" name="limitgoodtype" value="1" <?php  if($activity['limitgoodtype'] == 1) { ?>checked="true"<?php  } ?> onclick="$('.selectgoods').show();" /> 允许以下商品使用
        </label>

        <?php  } else { ?>
        <div class='form-control-static'>
            <?php  if($activity['limitgoodtype']==0) { ?>不添加商品限制
            <?php  } else if($activity['limitgoodtype']==1) { ?>允许以下商品使用
            <?php  } ?>
        </div>
        <?php  } ?>
    </div>
</div>

<div class="form-group selectgoods" <?php  if($activity['limitgoodtype']!=1&&$activity['limitgoodtype']!=2) { ?>style="display:none"<?php  } ?>>

<?php if( ce('sale.coupon' ,$activity) ) { ?>
<div class="" >
    <label class="col-lg control-label">选择商品</label>
    <div class="col-sm-9 col-xs-12">
        <div class="">
            <?php  echo tpl_selector('goodsid',array(
                    'preview'=>true,
            'readonly'=>true,
            'multi'=>1,
            'value'=>请选择商品,
            'url'=>webUrl('sale/coupon/querygoods'),
            'items'=>$goods,
            'buttontext'=>'选择商品',
            'placeholder'=>'请选择商品')
            )
            ?>
        </div>
        <span class='help-block'>添加限制的商品必须已上架,并且不属于砍价商品.</span>
    </div>
</div>
<?php  } else { ?>
<?php  if(!empty($goods)) { ?>
<?php  if(is_array($goods)) { foreach($goods as $activity) { ?>
<a href="<?php  echo tomedia($activity['thumb'])?>" target='_blank'>
    <img src="<?php  echo tomedia($activity['thumb'])?>" style='width:100px;border:1px solid #ccc;padding:1px' />
</a>
<?php  } } ?>
<?php  } else { ?>
暂无商品
<?php  } ?>
<?php  } ?>

</div>


<script>

    var str = [];
    $('.img-nickname').each(function (v,i) {
        str.push(i.innerHTML)
    });

    $('#goodsid_text').val(str.join('; '))

</script>

