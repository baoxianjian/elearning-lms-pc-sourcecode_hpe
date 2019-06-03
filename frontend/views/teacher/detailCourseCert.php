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
                      <div class="col-md-6 col-md-offset-3">
                      <?php if($certificationTemplatesUrl){?>
                        <h3 style="text-align:center;"><?=Yii::t('frontend', 'tip_for_get_credentialstip_for_get_credentials')?>.</h3>
                        <img src="<?php echo $certificationTemplatesUrl ;?>" style="margin-top:4%; width:100%; box-shadow:0 0 1px 2px #ccc;">
                     <?php }else {?>
                     	 <h3 style="text-align:center;"><?=Yii::t('frontend', 'tip_for_no_credentialstip')?>.</h3>
                     	 <?php }?>
                      </div>
                    </div>
                  </div>      