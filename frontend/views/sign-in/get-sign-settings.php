  <?php
/**
 * User: baoxianjian
 * Date: 2016/4/26
 * Time: 14:52
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use components\widgets\TLinkPager;
use common\models\learning\LnCourse;


?>

  <?php if($signSettingList){ foreach($signSettingList as $k=>$v) {?>
      <div idx="<?=$k?>" name="sign_setting_list" class="row timeRow timeRowResult">
          <div id="sign_setting_date_<?=$k?>" class="col-sm-2"><?=TTimeHelper::FormatTime($k)?></div>
          <div id="sign_setting_time_<?=$k?>" class="col-sm-7">
              <?php $i=0; foreach($v as $v2) { $idx2= $k.$i;?>
                  <span use-count="<?=$v2['use_count']?>" kid="<?=$v2['kid']?>" idx="<?=$idx2?>" name="sign_time_items_<?=$k?>">
                      <span id="sign_time_item_title_<?=$idx2?>"><?=$v2['title']?></span>
                      <span id="sign_time_item_start_<?=$idx2?>"><?=$v2['start_at_str']?></span>~
                      <span id="sign_time_item_end_<?=$idx2?>"><?=$v2['end_at_str']?></span>
                  </span>
              <?php $i++; }?>
          </div>
          <div class="col-sm-3" style="text-align: center;">
          <?php if($courseModel->open_status!=LnCourse::COURSE_END){?>
              <a href="###" class="btn btn-xs" data-toggle="modal" data-target="#editDate" onclick="showEditSignDateBox('edit','<?=$k?>')" ><?=Yii::t('common', 'edit_button')?></a>
              <a href="javascript:void(0);" onclick="deleteSignInSettings('<?=$k?>');" class="btn btn-xs delRowBtn"><?=Yii::t('common', 'delete_button')?></a>
          <?php }?>    
          <div class="btn-sm pull-left barCodeTodayLink"><?=Yii::t('frontend', 'sign_in_by_qr_code')?>
                  <div class="barCodeToday">
                      <span><img id="img_qr_code_<?=$k?>" src="<?=Yii::$app->urlManager->createUrl(['/sign-in/qr-scan-code','date'=>$k,'cid'=>$id])?>" height="128" width="128"></span>
                      <div>
                          <span class="miniBtn"><a href="javascript:void(0)" onclick="showSignInBigQrCodeImg('img_qr_code_<?=$k?>')"><?=Yii::t('frontend', 'enlarge')?></a></span>
                          <span class="miniBtn"><a href="javascript:void(0)" onclick="printSignInQrCodeImg('img_qr_code_<?=$k?>')"><?=Yii::t('common', 'print')?></a></span>
                          <span class="miniBtn"><a href="<?=Yii::$app->urlManager->createUrl(['/sign-in/qr-scan-code','date'=>$k,'cid'=>$id,'down'=>1])?>"><?=Yii::t('common', 'download')?></a></span>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  <?php }}else{?>
      <?=Yii::t('frontend', 'no_sign_in_settings_temporarily')?>
  <?php }?>

