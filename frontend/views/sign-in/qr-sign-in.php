  <?php
/**
 * User: baoxianjian
 * Date: 2016/5/24
 * Time: 15:31
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;
use common\helpers\TTimeHelper;
use common\helpers\TStringHelper;
use yii\helpers\Html;
use components\widgets\TLinkPager;
use common\models\learning\LnCourseSignIn; 
use common\models\learning\LnCourse;

?>
<div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="row">
          <div class="panel panel-default hotNews topBordered" style="margin:40px 0">
            <div class="panel-body">
              <div class="row" style="margin: 100px auto; ">
                <div class="col-xs-4" style=" text-align: center; font-size: 6rem; padding-top: 30px; ">
                  <?php if($signInResult['result']=='1'){?>
                    <i class="glyphicon glyphicon-ok" style="color: #0197d6;"></i>
                  <?php }else{?>
                    <i class="glyphicon glyphicon-remove" style="color: #0197d6;"></i>
                  <?php }?>
                </div>

                <div class="col-xs-8">
                  <?php if($signInResult['result']=='1'){?>
                    <h2><?=Yii::t('common', '{value}_success',['value'=>Yii::t('frontend','sign_in')])?></h2>
                  <?php }else{?>
                    <h2><?=Yii::t('common', '{value}_failed',['value'=>Yii::t('frontend','sign_in')])?></h2>
                    <p><?=Yii::t('common', 'error_message')?>：<?=$signInResult['msg']?></p>
                    <p><?=Yii::t('frontend', 'you_can_contact_manager_when_need')?></p>
                  <?php }?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div> 