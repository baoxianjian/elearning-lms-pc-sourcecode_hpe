  <?php
/**
 * User: zhanglei
 * Date: 2015/8/12
 * Time: 13:02
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use components\widgets\TLinkPager;
?>
<div class=" panel-default scoreList">
  <div class="panel-body">
      <div class="col-md-12 col-sm-12" style="margin-top:20px;">
          <div class="col-md-6 col-sm-6">
              <?php if($canPushCertification){?>
                  <a href="javascript:void(0);" id="push_certification_all" class="btn btn-sm btn-default pull-left"><?=Yii::t('frontend', 'issue_all')?></a>
              <?php }?>
          </div>
          <div class="col-md-6 col-sm-6">
              <div class="form-group" style="margin-bottom:0;">
                  <select class="form-control" id='sortcert' style="width:50%;">
                      <option value="1"><?= Yii::t('frontend', 'rank_by_position') ?></option>
                      <option value="2" <?if($param['sort']==2):?> selected <?endif;?>><?= Yii::t('frontend', 'rank_by_organization') ?></option>
                  </select>
              </div>
              <div class="input-group ">
                  <input type="text" class="form-control search_people"  id="keywordcert" style="height: 30px;" placeholder="<?= Yii::t('common', 'real_name') ?>/<?= Yii::t('frontend', 'position') ?>/<?= Yii::t('frontend', 'department') ?>" <?if($param['keyword']):?> value="<?=$param['keyword']?>"<?endif;?>>
                  <span class="input-group-btn"><button id="certification_search_btn" class="btn btn-success btn-sm" type="button"><?= Yii::t('frontend', 'top_search_text') ?><?= Yii::t('frontend', 'top_search_text') ?></button></span>
              </div>
          </div>
      </div>

      <div class="col-md-12 col-sm-12 nameList">
          <ul>
              <?if(($num=count($students))>0):?><?  foreach ($students as $stu): ?>

                  <li class="col-md-3 col-sm-3 col-xs-12">
                      <div class="controlBtns4">
                          <?php if($stu['iscert']==1 || $stu['iscert']==2 || $canPushCertification){?>
                          <a href="javascript:void(0);" data-uid=<?=$stu['user_id']?>  class="btn <?php if(!$stu['iscert']){?>btn-success  push_certification<?php }?> btn-sm pull-right "> <?php if($stu['iscert']==1){?><?= Yii::t('frontend', 'issued') ?><?php }elseif ($stu['iscert']==2){echo Yii::t('frontend', 'canceled') ;}else{ echo  Yii::t('frontend', 'issue') ;} ?></a>
                          <?php }?>
                          <a href="javascript:void(0);" class="btn btn-sm pull-right cert-detail-person"  data-uid="<?=$stu['user_id']?>"><?= Yii::t('common', 'examination_score') ?></a>
                          <input type="hidden" class="push_certification_user" name="push_certification_user[]" value="<?=$stu['user_id']?>" />
                      </div>
                      <h5><?=$stu['real_name'] ?></h5>
                      <p><?= Yii::t('frontend', 'from_text') ?>: <?
                          $dictionaryService = new \common\services\framework\DictionaryService();
                          if(isset($stu['location'])): echo $dictionaryService->getDictionaryNameByCode("location",$stu['location']); endif;?>
                          <?=$stu['orgnization_name']?>
                      </p>
                      <p><?= Yii::t('frontend', 'learning_state') ?>:<?php if($stu['studystatus']){echo Yii::t('frontend', 'page_lesson_hot_tab_3')  ;}else{echo Yii::t('frontend', 'page_lesson_hot_tab_2') ;}?></p>
                  </li>

              <? endforeach;?>
          </ul>
      </div>

      <div class="col-md-12">
          <nav id="certificationPage">
              <?php
              echo TLinkPager::widget([
                  'id' => 'page',
                  'pagination' => $pages,
                  'displayPageSizeSelect'=>false
              ]);
              ?>
          </nav>
      </div>
      <? endif;?>
  </div>
</div>
   <script>
   $("document").ready(function (){
		var url = '<?=Yii::$app->urlManager->createUrl(['teacher/detail-certification', 'id' => $id])?>';
		var push_cert_url = '<?=Yii::$app->urlManager->createUrl(['teacher/push-certification', 'id' => $id])?>';
		var push_cert_url_all = '<?=Yii::$app->urlManager->createUrl(['teacher/push-certification-all', 'id' => $id])?>';
		var certDetailUrl = '<?=Yii::$app->urlManager->createUrl(['teacher/detail-score-person', 'id' => $id, 'iframe' => $iframe, 'header' => 'show'])?>';
		
		$('#certification_search_btn').bind('click', function() {
		   var sort = $('#sortcert').val();
		   var keyword = $('#keywordcert').val();
			var inputdata = {sort:sort,keyword:keyword};
			ajaxGet(url, "courseAward5",null,inputdata);
	   });

     $('.push_certification').bind('click', function() {
            var obj = $(this);
            var uid = obj.attr('data-uid');
            $.post(push_cert_url, {"sid": uid},
                function (data) {
                    var result = data.result;
                    var msg = data.msg;
                    if (result == 'fail') {
                        app.showMsg(msg,1000) ;
                    }
                    else if (result == 'success') {
                        app.showMsg(msg,1000) ;
                        ajaxGet(url, "courseAward5");
                    }
                }, "json");
            return true ;
	   });

	   $('#push_certification_all').bind('click', function() {
	        $.post(push_cert_url_all, null,
	            function (data) {
	                var result = data.result;
	                if (result == 'fail') {
	                	app.showMsg(data.msg,1000) ;
	                }
	                else if (result == 'success') {
	                	app.showMsg(data.msg,1000) ;
						ajaxGet(url, "courseAward5");
	                }
	            }, "json");
			return true ;
  		});

  		$('.cert-detail-person').bind('click', function() {
		   var userId = $(this).attr("data-uid");
		   ajaxGet( certDetailUrl  + "&userId="+userId, "scoreDetails");
		   app.alertWideAgain('#scoreDetails') ;
	   });
   });
   
    $(function(){
        $("#certificationPage .pagination").on('click', 'a', function(e){
            e.preventDefault();
            ajaxGet($(this).attr('href'), "courseAward5");
        });
    });
</script>
