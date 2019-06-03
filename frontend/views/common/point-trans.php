<?php
/**
 * Created by PhpStorm.
 * User: baoxianjina
 * Date: 2016/5/17
 * Time: 10:38
 */
use yii\helpers\Html;
?>
<?= Html::jsFile('/static/frontend/js/key.replace.js') ?>
<!-- 积分打赏面板 -->
<div id="point_trans_box" class="ui modal">
  <div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
    <h4 class="modal-title"><?=Yii::t('frontend', 'point_gratuity')?></h4>
  </div>
  <div class="content">
    <div class="infoBlock">
      <div class="row">
        <div class="col-md-12 col-sm-12">
          <div class="form-group form-group-sm">
            <label style="display: block" class="col-sm-2 control-label"><?=Yii::t('frontend', 'gratuity')?></label>
            <div class="col-sm-10">
              <input class="form-control pull-left" id="point_trans_value" onkeyup="changeTransPointAfter(this)" type="text" placeholder="<?=Yii::t('frontend', 'please_enter_gratuity_number')?>" style="width:80%"><?=Yii::t('frontend', 'point')?>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12 col-sm-12">
          <div class="form-group form-group-sm" style="text-align: center;">
            <span><?=Yii::t('frontend', 'gratuity_before')?>:<span id="point_trans_before_point">0</span><?=Yii::t('frontend', 'point')?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=Yii::t('frontend', 'gratuity_after')?>:<span id="point_trans_after_point">0</span><?=Yii::t('frontend', 'point')?></span>
          </div>
        </div>
      </div>
      <hr>
      <div class="col-md-12 col-sm-12 centerBtnArea">
        <a href="javascript:void(0)" class="btn btn-success btn-sm centerBtn" style="width:30%;" onclick="closePointTransBox();"><?=Yii::t('frontend', 'be_sure')?></a>
      </div>
    </div>
    <div class="c"></div> <!--新增-->
  </div>
  <div class="c"></div> <!--新增-->
</div>




<script type="text/javascript">
  var point_trans_to_uid='';
  var point_trans_desc='{user.real_name}';//'<?=Yii::t('frontend', 'point_gratuity')?>';
  function showPointTransBox(uid,desc)
  {
      point_trans_to_uid=uid;
      if(typeof desc!='undefined')
      {
         point_trans_desc=desc;
      }
      $("#point_trans_value").val("");
      $.ajax({
        type: "GET",
        url: "<?=Yii::$app->urlManager->createUrl(['/point/avaliable-point'])?>",
        dataType: 'json',
        data: null,
        success: function(data){
          $("#point_trans_before_point").html(data.avaliable_point);
          $("#point_trans_after_point").html(data.avaliable_point);
        }
      });
      app.alert("#point_trans_box");
      $(".ui.dimmer").css("z-index","999999");
  }

  function changeTransPointAfter(obj)
  {
    var point1=parseInt(replaceToFloat(obj,0,true));//$("#point_trans_before_point").html();
    var point2=parseInt($("#point_trans_before_point").html());


    if(point1==0 || obj.value=='')
    {
       obj.value='';
       $("#point_trans_after_point").html(point2);
       return;
    }


    var result=point2-point1;

    if(result<0)
    {
       obj.value='';
       result=point2;
       app.showMsg('<?=Yii::t('frontend', 'point_not_enough')?>');
    }
    $("#point_trans_after_point").html(result);
  }

  function closePointTransBox()
  {
    var number=$("#point_trans_value").val();

    if(number<=0)
    {
      app.showMsg("<?=Yii::t('common', 'point_entered_must_greater_than_{value}',['value'=>0])?>");
      return;
    }

    var point2=parseInt($("#point_trans_before_point").html());
    if(point2<=0)
    {
      app.showMsg('<?=Yii::t('frontend', 'point_not_enough')?>');
      return;
    }


    $.ajax({
      type: "POST",
      url: "<?=Yii::$app->urlManager->createUrl(['/point/point-trans'])?>",
      dataType: 'json',
      data: {uid:point_trans_to_uid,num:number,desc:point_trans_desc},
      success: function(data){
        if (data.result=='success')
        {
          app.showMsg("<?=Yii::t('frontend', 'point_trans_success')?>");
          app.hideAlert("#point_trans_box");
          $("#point_trans_before_point").html(data.pointResult.available_point);
          $("#point_trans_after_point").html(data.pointResult.available_point);
          scoreRefresh(parseInt(data.pointResult.available_point));
        }
        else
        {
          app.showMsg("<?=Yii::t('frontend', 'point_trans_failed')?>");
          app.hideAlert("#point_trans_box");
        }
      }
    });
  }
</script>
