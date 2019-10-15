<?php defined('IN_IA') or exit('Access Denied');?><link rel="stylesheet" href="../addons/ewei_shopv2/plugin/lottery/static/style/design.css"/>
<style>
    .form-group.rec_reward_data{clear:both;}
</style>
<div class="alert alert-warning">抽奖所有等级不能低于5个，概率之和可不等于100%</div>
<div class="form-group">
    <label class="col-lg control-label">抽奖标题&概率</label>
    <?php if(cv('lottery.edit')) { ?>
    <div class="col-sm-10 col-xs-12" id="rec_reward_people">
        <div class="row">
            <div class="col-sm-5 col-xs-5">
                <div class="input-group">
                    <input type="text" name="reward_title" class="form-control" placeholder="标题"  />
                    <input type="hidden" id="reward_title_icon" name="reward_title_icon" value="">
                    <span class="input-group-addon" style="padding: 0px;"><img src="" id="reward_title_icon_show" width="30px" height="28px" onerror="this.src='../addons/ewei_shopv2/static/images/nopic.png'"></span>
                    <span class="input-group-addon btn" data-toggle="selectImg" data-input="#reward_title_icon" data-img="#reward_title_icon_show" data-full="1">选择图片</span>
                </div>
            </div>
            <div class="col-sm-3 col-xs-3">
                <div class="input-group">
                    <input type="number" class="form-control valid" name="probability" placeholder="概率"  >
                    <span class="input-group-addon">%</span>
                </div>
            </div>
            <div class="col-sm-4 col-xs-4">
                <a class="btn btn-primary" onclick="addRewardrank();">添加</a>
                <a class="btn btn-primary" onclick="updateRewardrank();">修改</a>
            </div>
        </div>
    </div>
    <?php  } ?>
</div>


  <div class="form-group">
      <?php if(cv('lottery.edit')) { ?>
      <div class="col-sm-12 col-xs-12" id="rec-rank">
          <?php  if(!empty($reward)) { ?>
          <?php  $count=1;?>
          <?php  if(is_array($reward)) { foreach($reward as $rank => $value) { ?>
          <?php  if(!empty($value)) { ?>


          <div class="panel <?php  if($count==1) { ?> panel-primary <?php  } else { ?> panel-default <?php  } ?>" data-rank="<?php  echo $rank;?>" data-title="<?php  echo $value['title'];?>" data-icon="<?php  echo $value['icon'];?>" data-probability="<?php  echo $value['probability'];?>" onclick="rankclick(this);">
              <div class="panel-heading"><?php  echo $value['title'];?>(<?php  echo $value['probability'];?>%)
                  <div class="pull-right" style="padding:0;margin:0;margin-top:-8px;">
                  <button type="button" class="btn btn-warning" id="btn-add-time" onclick="delrank(this);">删除奖励</button>
                  </div>
              </div>
              <div class="panel-body">
                  <div class="form-group">
                      <label class="col-lg control-label">奖品设置</label>
                      <div class="col-lg col-xs-12">
                          <select class="input-sm form-control input-s-sm inline" data-id="<?php  echo $rank;?>" onchange="select_change(this);">
                              <option value="0">请选择</option>
                              <option value="1"><?php  echo $this->set['texts']['credit1']?></option>
                              <option value="2">奖金</option>
                              <option value="3">红包</option>
                              <option value="4">特惠商品</option>
                              <option value="5">优惠券</option>
                              <option value="6">无奖励</option>
                          </select>
                      </div>
                      <div class="col-sm-7 col-xs-10 " data-id="<?php  echo $rank;?>" id="reward_show<?php  echo $rank;?>"></div>
                  </div>
                  <hr>
                  <div class="form-group" style="border-bottom:1px solid #f2f2f2;">
                      <label class="col-lg control-label"></label>
                      <div class="col-sm-7">
                          <div class="form-control-static"><b>奖品信息</b></div>
                      </div>
                      <div class="col-lg"><b>操作</b></div>
                  </div>
                  <div id="selected_rec_reward<?php  echo $rank;?>">
                      <?php  if(!empty($value['reward'])) { ?>
                      <?php  if(is_array($value['reward'])) { foreach($value['reward'] as $key => $rec) { ?>
                      <?php  if($key=='credit') { ?>
                      <?php  if($rec>0) { ?>
                      <div class="form-group rec_reward_data"  id="rec_credit<?php  echo $rank;?>" data-rank="<?php  echo $rank;?>" data-type="1" data-value="<?php  echo $rec;?>" style="border-bottom:1px solid #f2f2f2;">
                          <label class="col-lg control-label"></label>
                          <div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;"><?php  echo $this->set['texts']['credit1']?><span class="pull-right label label-success"  style="margin-right: 10px;"><?php  echo $rec;?></span></div>
                          <div class="col-lg">
                              '&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button>
                              '</div></div>

                      <?php  } ?>
                      <?php  } else if($key=='money') { ?>
                      <?php  if($rec['num']>0) { ?>
                      <div class="form-group rec_reward_data"  id="rec_money<?php  echo $rank;?>"  data-rank="<?php  echo $rank;?>" data-type="2" data-total="<?php  echo $rec['total'];?>" data-value="<?php  echo $rec['num'];?>" data-moneytype="<?php  echo $rec['type'];?>" style="border-bottom:1px solid #f2f2f2;">
                          <label class="col-lg control-label"></label>
                          <div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;">奖金<span class="pull-right label label-primary">[总限额<?php  echo $rec['total'];?>元]</span><span class="pull-right label label-info" style="margin-right: 10px;">[<?php echo $rec['type']==0?'余额':'微信';?>]</span><span class="pull-right label label-success"  style="margin-right: 10px;"><?php  echo $rec['num'];?>元</span></div>
                          <div class="col-lg">
                              &nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button>
                              </div></div>
                      <?php  } ?>
                      <?php  } else if($key=='bribery') { ?>
                      <?php  if($rec>0) { ?>
                      <div class="form-group rec_reward_data"  id="rec_bribery<?php  echo $rank;?>" type="button" data-rank="<?php  echo $rank;?>" data-type="3" data-total="<?php  echo $rec['total'];?>" data-value="<?php  echo $rec['num'];?>" style="border-bottom:1px solid #f2f2f2;">
                          <label class="col-lg control-label"></label>
                          <div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;">红包<span class="pull-right label label-primary">[总限额<?php  echo $rec['total'];?>元]</span><span class="pull-right label label-success"  style="margin-right: 10px;"><?php  echo $rec['num'];?>元</span></div>
                          <div class="col-lg">
                              &nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button></div></div>
                      <?php  } ?>
                      <?php  } else if($key=='goods') { ?>
                      <?php  if(!empty($rec)) { ?>
                      <?php  if(is_array($rec)) { foreach($rec as $ke => $goods) { ?>
                      <?php  if(empty($goods['spec'])) { ?>
                      <div class="form-group rec_reward_data"   id="rec_goods<?php  echo $rank;?>_<?php  echo $ke;?>_<?php  echo $specgoods['goods_spec'];?>"  data-rank="<?php  echo $rank;?>" data-type="4" data-goodsid="<?php  echo $ke;?>" data-img="<?php  echo $goods['img'];?>" data-goodsname="<?php  echo $goods['title'];?>" data-goodstotal="<?php  echo $goods['count'];?>" data-goodsnum="<?php  echo $goods['total'];?>" data-goodsprice="<?php  echo $goods['marketprice'];?>" data-goodsec="0" data-specname='' style="border-bottom:1px solid #f2f2f2;">
                          <label class="col-lg control-label"></label>
                          <div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;"><img src="<?php  echo $goods['img'];?>" width="30px" height="30px"> <?php  echo $goods['title'];?>[无规格]<span class="pull-right label label-primary">[总限购<?php  echo $goods['count'];?>个]</span><span class="pull-right label label-info" style="margin-right: 10px;">[每人限购<?php  echo $goods['total'];?>个]</span><span class="pull-right label label-success"  style="margin-right: 10px;"><?php  echo $goods['marketprice'];?>元</span></div>
                          <div class="col-lg">
                              &nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button>
                              </div></div>
                      <?php  } else { ?>
                      <?php  if(is_array($goods['spec'])) { foreach($goods['spec'] as $k => $specgoods) { ?>
                      <div class="form-group rec_reward_data"  id="rec_goods<?php  echo $rank;?>_<?php  echo $ke;?>_<?php  echo $specgoods['goods_spec'];?>"  data-rank="<?php  echo $rank;?>" data-type="4" data-img="<?php  echo $goods['img'];?>" data-goodsid="<?php  echo $ke;?>" data-goodsname="<?php  echo $goods['title'];?>" data-goodstotal="<?php  echo $goods['count'];?>" data-goodsnum="<?php  echo $specgoods['total'];?>" data-goodsprice="<?php  echo $specgoods['marketprice'];?>" data-goodsec="<?php  echo $specgoods['goods_spec'];?>" data-specname="<?php  echo $specgoods['goods_specname'];?>" style="border-bottom:1px solid #f2f2f2;">
                          <label class="col-lg control-label"></label>
                          <div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;"><img src="<?php  echo $goods['img'];?>" width="30px" height="30px"> <?php  echo $goods['title'];?>[<?php  echo $specgoods['goods_specname'];?>]<span class="pull-right label label-primary">[总限购<?php  echo $goods['count'];?>个]</span><span class="pull-right label label-info" style="margin-right: 10px;">[每人限购<?php  echo $specgoods['total'];?>个]</span><span class="pull-right label label-success"  style="margin-right: 10px;"><?php  echo $specgoods['marketprice'];?>元</span></div>
                          <div class="col-lg">
                              &nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button>
                              </div></div>

                      <?php  } } ?>
                      <?php  } ?>
                      <?php  } } ?>
                      <?php  } ?>
                      <?php  } else if($key=='coupon') { ?>
                      <?php  unset($rec['total']);?>
                      <?php  if(is_array($rec)) { foreach($rec as $ke => $coupon) { ?>
                      <div class="form-group rec_reward_data"   id="rec_coupon<?php  echo $rank;?>_<?php  echo $coupon['id'];?>"  data-rank="<?php  echo $rank;?>" data-type="5" data-couponid="<?php  echo $coupon['id'];?>" data-img="<?php  echo $coupon['img'];?>" data-couponname="<?php  echo $coupon['couponname'];?>" data-coupontotal="<?php  echo $coupon['count'];?>" data-couponnum="<?php  echo $coupon['couponnum'];?>" style="border-bottom:1px solid #f2f2f2;">
                          <label class="col-lg control-label"></label>
                          <div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;"><img src="<?php  echo $coupon['img'];?>" width="30px" height="30px"> <?php  echo $coupon['couponname'];?><span class="pull-right label label-primary">[总限购<?php  echo $coupon['count'];?>张]</span><span class="pull-right label label-success"  style="margin-right: 10px;"><?php  echo $coupon['couponnum'];?>张</span></div>
                          <div class="col-lg">
                              &nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button>
                              </div></div>

                      <?php  } } ?>
                      <?php  } ?>
                      <?php  } } ?>
                      <?php  } else { ?>
                      <div class="form-group rec_reward_data" data-type="6" data-rank="<?php  echo $rank;?>">
                          <p class="text-center">无奖励</p>
                      </div>
                      <?php  } ?>
                  </div>

              </div>
          </div>


          <?php  $count++;?>
          <?php  } ?>
          <?php  } } ?>
          <?php  } ?>
      </div>
      <?php  } ?>
  </div>
<!-- 奖励Modal -->
<div class="modal fade" id="rewardcouponModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" >选择优惠券</h4>
            </div>
            <div class="modal-body">
                <?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('lottery/post/select_coupon', TEMPLATE_INCLUDEPATH)) : (include template('lottery/post/select_coupon', TEMPLATE_INCLUDEPATH));?>
            </div>
        </div>
    </div>
</div>
<!-- 奖励Modal -->
<div class="modal fade" id="rewardgoodsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" >选择商品</h4>
            </div>
            <div class="modal-body" style="padding: 15px;">
                <?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('lottery/post/select_goods', TEMPLATE_INCLUDEPATH)) : (include template('lottery/post/select_goods', TEMPLATE_INCLUDEPATH));?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function () {
        var currentUrl = window.location.href;

        if(currentUrl.indexOf('lottery.edit') > -1) {
            $('#reward-submit').on('click', function () {
                if($('.rec_reward_data').length< 1) {
                    tip.msgbox.err('请先添加奖励');
                    return false;
                }
            })
        }
    })

      var currank = 0;
      var countrank = <?php echo $count ? $count-1:0;?>;
      //选择奖励类型
      function select_change(obj) {
          var select_item = $(obj).val();
          currank = $(obj).data('id');
          $('#reward_show'+currank).empty();
          var div_item = '';
          if(select_item==1){
              div_item = '<div class="row"><div class="col-sm-4 col-xs-4"><input type="number" class="form-control" name="reccredit" placeholder="请输入积分">'
                      +'</div><div class="col-sm-4 col-xs-4"><a class="btn btn-primary" onclick="addReward(this,1);">添加奖励</a>'
                      +'</div></div>';
          }
          if(select_item==2){
              div_item = '<div class="row"><div class="col-sm-3 col-xs-4"><input type="number" class="form-control" name="recmoney" placeholder="奖金">'
                      +'</div><div class="col-sm-3 col-xs-4"><input type="number" class="form-control" name="recmoneytotal" placeholder="总限额"></div><div class="col-sm-3 col-xs-4"><select class="input-sm form-control input-s-sm inline" id="recmoneytype">'
                      +'<option value="0">余额</option><option value="1">微信</option></select></div>'
                      +'<div class="col-sm-3 col-xs-4"><a class="btn btn-primary" onclick="addReward(this,2);">添加奖励</a>'
                      +'</div></div>';
          }
          if(select_item==3){
              div_item = '<div class="row"><div class="col-sm-4 col-xs-4"><input type="number" class="form-control" name="recbribery" placeholder="请输入红包">'
                      +'</div><div class="col-sm-4 col-xs-4"><input type="number" class="form-control" name="recbriberytotal" placeholder="输入总限额"></div><div class="col-sm-4 col-xs-4"><a class="btn btn-primary" onclick="addReward(this,3);">添加奖励</a>'
                      +'</div></div>';
          }
          if(select_item==4){
              div_item = '<div class="row"><div class="col-sm-4 col-xs-4"><a class="btn btn-primary" onclick="addReward(this,4);">添加特惠商品</a></div></div>';
          }
          if(select_item==5){
              div_item = '<div class="row"><div class="col-sm-4 col-xs-4"><a class="btn btn-primary" onclick="addReward(this,5);">添加优惠券</a></div></div>';
          }
          if(select_item==6){
              div_item = '<div class="row"><div class="col-sm-4 col-xs-4"><a class="btn btn-primary" onclick="addReward(this,6);">添加空奖励</a></div></div>';
          }

          $('#reward_show'+currank).append(div_item);

      }
        //添加奖励等级
      function addRewardrank() {
//          $('#rec-rank .panel')
          var pn = $('#rec-rank ').find('.panel').length;
          if(pn+1>8){
              tip.msgbox.err('奖项不能大于8个');
              return;
          }
          var reward_title = $('input[name="reward_title"]').val();
          if(reward_title==''){
              tip.msgbox.err('标题不能为空');
              return;
          }

          var probability = $('input[name="probability"]').val();
          if(probability==''){
              tip.msgbox.err('概率不能为空');
              return;
          }
          probability = parseFloat(probability);
          if(probability<0){
              tip.msgbox.err('概率不能为空或小于0');
              return;
          }
          var icon = $('input[name="reward_title_icon"]').val();
          var div_content = '<div class="panel panel-primary" data-rank="'+countrank+'" data-icon="'+icon+'" data-title="'+reward_title+'" data-probability="'+probability+'" onclick="rankclick(this);"><div class="panel-heading">'+reward_title+'('+probability+'%)'+
                  '<div class="pull-right" style="padding:0;margin:0;margin-top:-8px;"><button type="button" class="btn btn-warning" id="btn-add-time" onclick="delrank(this);">删除奖励</button>'+
                  '</div></div><div class="panel-body" ><div class="form-group">'+
                  '<label class="col-lg control-label">奖品设置</label><div class="col-lg col-xs-12">'+
                  '<select class="input-sm form-control input-s-sm inline" data-id="'+countrank+'" onchange="select_change(this);">'+
                  '<option value="0">请选择</option>'+
                  '<option value="1">积分</option>'+
                  '<option value="2">奖金</option>'+
                  '<option value="3">红包</option>'+
                  '<option value="4">特惠商品</option>'+
                  '<option value="5">优惠券</option>'+
                  '<option value="6">无奖励</option>'+
                  '</select>'+
                  '</div>'+
                  '<div class="col-sm-7 col-xs-10 " data-id="'+countrank+'"  id="reward_show'+countrank+'">'+
                  '</div>'+
                  '</div>'+
                  '<hr/>'+
                  '<div class="form-group" style="border-bottom:1px solid #f2f2f2;">'+
                  '<label class="col-lg control-label" ></label>'+
                  '<div class="col-sm-7">'+
                  '<div class="form-control-static" ><b>奖品信息</b></div>'+
                  '</div>'+
                  '<div class="col-lg">'+
                  '<b>操作</b>'+
                  '</div></div>'+
                  '<div class="reward-item" id="selected_rec_reward'+countrank+'">'+
                  '</div></div></div>';


          $('.panel').attr('class','panel panel-default');
          $('#rec-rank').append(div_content);
          currank = countrank;
          countrank++;

          buildpan();
          return;
      }
        //更新奖励等级
      function updateRewardrank() {
          var rank_div = $('div[data-rank="'+currank+'"]');
          if(rank_div.length<=0){
              tip.msgbox.err('请先选中要修改的奖励模块');
              return;
          }
          var reward_title = $('input[name="reward_title"]').val();
          if(reward_title==''){
              tip.msgbox.err('标题不能为空');
              return;
          }

          var probability = $('input[name="probability"]').val();
          if(probability==''){
              tip.msgbox.err('概率不能为空');
              return;
          }
          var icon = $('input[name="reward_title_icon"]').val();
          rank_div.find('div[class="panel-heading"]').html(reward_title+'('+probability+'%)'+
                  '<div class="pull-right" style="padding:0;margin:0;margin-top:-8px;"><button type="button" class="btn btn-warning" id="btn-add-time" onclick="delrank(this);">删除奖励</button>'+
                  '</div>');
          rank_div.data('title',reward_title);
          rank_div.data('icon',icon);
          rank_div.data('probability',probability);
          setTimeout(function () {
              buildpan();
          },100);
          return;

      }

      function rankclick(obj) {
          currank = $(obj).data('rank');
          $('.panel').attr('class','panel panel-default');
          $(obj).attr('class','panel panel-primary');
          return;
      }
      function delrank(obj) {
          //$(obj).parent().parent().attr('data-state',0);
          $(obj).parent().parent().parent().remove();
          buildpan();
          return;
      }
        //添加奖励
      function addReward(obj,datatype) {
          currank = $(obj).parent().parent().parent().data('id');
          $('.panel').attr('class','panel panel-default');
          $('div[data-rank="'+currank+'"]').attr('class','panel panel-primary');
          if(currank<0){
              tip.msgbox.err('请先选择抽奖等级..');
              return;
          }

          if(datatype==1){
              var reccredit = $('#reward_show'+currank+' input[name="reccredit"]').val();

              reccredit = parseInt(reccredit);
              if(reccredit>0){
                  var rec_credit = $('#rec_credit'+currank);
                  var btn_data = '<div class="form-group rec_reward_data"  data-rank="'+currank+'" data-type="1" data-value="'+reccredit+'" id="rec_credit'+currank+'" style="border-bottom:1px solid #f2f2f2;">'+
                          '<label class="col-lg control-label"></label>'+
                          '<div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;">积分<span class="pull-right label label-success"  style="margin-right: 10px;">'+reccredit+'</span></div>'+
                          '<div class="col-lg">'+
                          '&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button>'+
                          '</div></div>';
//                  if(rec_credit.length>0){
//                      rec_credit.attr('data-value',reccredit);
//                      var up_div = '<label class="col-lg control-label"></label>'+
//                              '<div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;">积分<span class="pull-right label label-success"  style="margin-right: 10px;">'+reccredit+'</span></div>'+
//                              '<div class="col-lg">'+
//                              '&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button></div>';
//                      rec_credit.html(up_div);
//                  }else{
                      $('#selected_rec_reward'+currank).empty();
                      $('#selected_rec_reward'+currank).append(btn_data);
//                  }
              }else{
                  tip.msgbox.err('请先填写积分..');
              }
          }
          if(datatype==2){
              var recmoney = $('#reward_show'+currank+' input[name="recmoney"]').val();
              recmoney = parseInt(recmoney);
              var recmoneytotal = $('#reward_show'+currank+' input[name="recmoneytotal"]').val();
              recmoneytotal = parseInt(recmoneytotal);
              var recmoneytype = parseInt($('#recmoneytype').val());
              if(recmoney>0){
                  var rec_money = $('#rec_money'+currank);
                  var moneytype='余额';
                  if(recmoneytype==1){
                      moneytype='微信';
                  }
                  var btn_data = '<div class="form-group rec_reward_data"  data-rank="'+currank+'" data-type="2" data-total="'+recmoneytotal+'" data-value="'+recmoney+'" data-moneytype="'+recmoneytype+'" id="rec_money'+currank+'" style="border-bottom:1px solid #f2f2f2;">'+
                          '<label class="col-lg control-label"></label>'+
                          '<div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;">奖金<span class="pull-right label label-primary">[总限额'+recmoneytotal+'元]</span><span class="pull-right label label-info" style="margin-right: 10px;">['+moneytype+']</span><span class="pull-right label label-success"  style="margin-right: 10px;">'+recmoney+'元</span></div>'+
                          '<div class="col-lg">'+
                          '&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button>'+
                          '</div></div>';
//                  if(rec_money.length>0){
//                      rec_money.attr('data-value',recmoney);
//                      rec_money.attr('data-moneytype',recmoneytype);
//                      var up_div = '<label class="col-lg control-label"></label>'+
//                              '<div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;">奖金<span class="pull-right label label-primary">[总限额'+recmoneytotal+'元]</span><span class="pull-right label label-info" style="margin-right: 10px;">['+moneytype+']</span><span class="pull-right label label-success"  style="margin-right: 10px;">'+recmoney+'元</span></div>'+
//                              '<div class="col-lg">'+
//                              '&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button>'+
//                              '</div>';
//                      rec_money.html(up_div);
//                  }else{
                      $('#selected_rec_reward'+currank).empty();
                      $('#selected_rec_reward'+currank).append(btn_data);
//                  }
              }else{
                  tip.msgbox.err('请先填写奖金..');
              }
          }
          if(datatype==3){
              var recbribery = $('#reward_show'+currank+' input[name="recbribery"]').val();
              recbribery = parseInt(recbribery);
              var recbriberytotal = $('#reward_show'+currank+' input[name="recbriberytotal"]').val();
              recbriberytotal = parseInt(recbriberytotal);
              if(recbribery>0){
                  var btn_data = '<div class="form-group rec_reward_data"   data-rank="'+currank+'" data-type="3" data-total="'+recbriberytotal+'" data-value="'+recbribery+'" id="rec_money'+currank+'" style="border-bottom:1px solid #f2f2f2;">'+
                          '<label class="col-lg control-label"></label>'+
                          '<div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;">红包<span class="pull-right label label-primary">[总限额'+recbriberytotal+'元]</span><span class="pull-right label label-success"  style="margin-right: 10px;">'+recbribery+'元</span></div>'+
                          '<div class="col-lg">'+
                          '&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button>'+
                          '</div></div>';

                  var rec_bribery = $('#rec_bribery'+currank);
//                  if(rec_bribery.length>0){
//                      rec_bribery.attr('data-value',recbribery);
//                      rec_bribery.attr('data-total',recbriberytotal);
//                      var up_div = '<label class="col-lg control-label"></label>'+
//                              '<div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;">红包<span class="pull-right label label-primary">[总限额'+recbriberytotal+'元]</span><span class="pull-right label label-success"  style="margin-right: 10px;">'+recbribery+'元</span></div>'+
//                              '<div class="col-lg">'+
//                              '&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button>'+
//                              '</div>';
//                      rec_bribery.html(up_div);
//                  }else{
                      $('#selected_rec_reward'+currank).empty();
                      $('#selected_rec_reward'+currank).append(btn_data);
//                  }
              }else{
                  tip.msgbox.err('请先填写红包..');
              }
          }
          if(datatype==4){
              $('#selected_goods').empty();
              $('#reward_show'+currank+' input[name="reward_type"]').val('rec');
              $('#rewardgoodsModal').modal('show');
              $("#select-good-list").html('<div class="tip">正在进行搜索...</div>');
              $.ajax("<?php  echo webUrl('lottery/select/query')?>", {
                  type: "get",
                  dataType: "html",
                  cache: false,
                  data: {title:'', type:'good',page:1,psize:5}
              }).done(function (html) {
                  $("#select-good-list").html(html);
              });
          }
          if(datatype==5){
              $('#selected_coupon').empty();
              $('#reward_show'+currank+' input[name="reward_type"]').val('rec');
              $('#rewardcouponModal').modal('show');
              $("#select-coupon-list").html('<div class="tip">正在进行搜索...</div>');
              $.ajax("<?php  echo webUrl('lottery/select/query')?>", {
                  type: "get",
                  dataType: "html",
                  cache: false,
                  data: {title:'', type:'coupon',page:1,psize:5}
              }).done(function (html) {
                  $("#select-coupon-list").html(html);
              });
          }
          if(datatype==6){
              var btn_data = '<div class="form-group rec_reward_data" data-type="6" data-rank="'+currank+'"><p class="text-center">无奖励</p></div>';
              $('#selected_rec_reward'+currank).empty();
              $('#selected_rec_reward'+currank).append(btn_data);
          }
      }
      //添加特惠商品奖励
      $(function(){
          $(".select-btn").click(function(){
              type = $(this).data("type");
              var goodsgroup = parseInt($("#cates").val());
              var kw = $.trim($("#select-"+type+"-kw").val());
              $("#select-"+type+"-list").html('<div class="tip">正在进行搜索...</div>');
              $.ajax("<?php  echo webUrl('lottery/select/query')?>", {
                  type: "get",
                  dataType: "html",
                  cache: false,
                  data: {title:kw, type:type,page:1,psize:5,goodsgroup:goodsgroup}
              }).done(function (html) {
                  $("#select-"+type+"-list").html(html);
              });
          });
      });
      //分页函数
      function select_page(url,pindex,obj) {
          if(pindex==''||pindex==0){
              return;
          }
          var who_type = url.indexOf("good");
          type = 'good';
          if(!who_type){
              type = 'coupon';
          }
          var kw = $.trim($("#select-"+type+"-kw").val());
          $("#select-"+type+"-list").html('<div class="tip">正在进行搜索...</div>');
          $.ajax("<?php  echo webUrl('lottery/select/query')?>", {
              type: "get",
              dataType: "html",
              cache: false,
              data: {title:kw, type:type,page:pindex,psize:5}
          }).done(function (html) {
              $("#select-"+type+"-list").html(html);
          });
      }
      //选择优惠券
      function coupon_select(obj,data){
          $('#error').html('');
          var need_count = $('input[name="need_count_'+data.id+'"]').val();
          var total_count = $('input[name="total_count_'+data.id+'"]').val();

          if(need_count==''||need_count<=0){
              $('#couponerror').html('优惠券数量不能<=0或空');
              return;
          }

          var coupon = '<div class="form-group rec_reward_data"   id="rec_coupon'+currank+'_'+data.id+'" type="button" data-rank="'+currank+'" data-type="5" data-couponid="'+data.id+'" data-img="'+data.img+'" data-couponname="'+data.name+'" data-couponnum="'+need_count+'" data-coupontotal="'+total_count+'" style="border-bottom:1px solid #f2f2f2;">'+
                  '<label class="col-lg control-label"></label>'+
                  '<div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;"><img src="'+data.img+'" width="30px" height="30px">'+data.name+'<span class="pull-right label label-primary">[总限购'+total_count+'张]</span><span class="pull-right label label-success"  style="margin-right: 10px;">'+need_count+'张</span></div>'+
                  '<div class="col-lg">'+
                  '&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button>'+
                  '</div></div>';
          $('#selected_rec_reward'+currank).empty();
          $('#selected_rec_reward'+currank).append(coupon);

          $('#rewardcouponModal').modal('hide');
      }
      //选择指定商品
      function goods_select(obj,data){
          $('#error').html('');
          var money = $('input[name="need_money_'+data.id+'"]').val();
          var totalneed = $('input[name="total_goods_'+data.id+'"]').val();
          var total = $('input[name="need_goods_'+data.id+'"]').val();
          if(money==''){
              $('#error').html('商品指定价格不能为空');
              return;
          }
          if(total<=0 || total==''){
              $('#error').html('单人奖励商品数量不能为空或者小于0');
              return;
          }
          if(total>data.total){
              $('#error').html('单人奖励商品数量不能大于库存');
              return;
          }
          var spec= $('#spc_'+data.id).find("option:selected");
          var spec_id = 0;
          var spec_name = '无规格';
          if(spec.length>0){
              spec_id = spec.data('specs');
              spec_name = spec.data('title');
          }
          var goods = '<div class="form-group rec_reward_data"   id="rec_goods'+currank+'_'+data.id+'_'+spec_id+'" data-rank="'+currank+'" data-img="'+data.img+'" data-type="4" data-goodsid="'+data.id+'" data-goodsname="'+data.name+'" data-goodsnum="'+total+'" data-goodstotal="'+totalneed+'" data-goodsprice="'+money+'" data-goodsec="'+spec_id+'" data-specname="'+spec_name+'" style="border-bottom:1px solid #f2f2f2;">'+
                  '<label class="col-lg control-label"></label>'+
                  '<div class="col-sm-7" style="padding-top: 8px;padding-bottom: 20px;"><img src="'+data.img+'" width="30px" height="30px">'+data.name+'['+spec_name+']<span class="pull-right label label-primary">[总限购'+totalneed+'个]</span><span class="pull-right label label-info" style="margin-right: 10px;">[每人限购'+total+'个]</span><span class="pull-right label label-success"  style="margin-right: 10px;">'+money+'元</span></div>'+
                  '<div class="col-lg">'+
                  '&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" onclick="del_data(this);" class="btn btn-danger  btn-sm btn-delete-time">删除</button>'+
                  '</div></div>';
          $('#selected_rec_reward'+currank).empty();
          $('#selected_rec_reward'+currank).append(goods);

          $('#rewardgoodsModal').modal('hide');
      }
      //删除数据
      function del_data(obj) {
          $(obj).parent().parent().remove();
      }
  </script>