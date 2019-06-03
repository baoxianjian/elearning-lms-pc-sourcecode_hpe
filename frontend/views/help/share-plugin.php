<?php
/**
 * Created by PhpStorm.
 * User: Alex Liu
 * Date: 2016/3/4
 * Time: 18:01
 */
use components\widgets\TBreadcrumbs;

$this->pageTitle = Yii::t('frontend','help');
$this->params['breadcrumbs'][] = Yii::t('frontend','share_fast');
$this->params['breadcrumbs'][] = $this->pageTitle;
?>
<style type="text/css">
    .shareToLMS{display: inline-block; padding: 10px 20px; margin: 30px auto 30px auto; background-color: #337ab7; color: #fff; font-weight: bold;}
    .browserHelp{float: left; margin-bottom: 20px; transition: all 0.2s}
    .browserHelp img{border: 1px dotted #eee; width: 100%; }
/*    .browserHelp img:hover{position: absolute; zoom:2; cursor: pointer;}*/
</style>
<script type="text/javascript">
$(document).ready(function(){$('.shareToLMS').bind('click', function(e){e.preventDefault()})})
</script>

<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12">
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="basicInfo">
                    <div class=" panel-default scoreList">
                        <div class="panel-body">
                            <div class="panel-body courseInfoInput">
                                <div class="row">
                                    <div class="col-md-12" style="position: relative;">
                                        <div class="col-md-12" style="text-align: center; border: 1px dotted #ccc; margin-bottom: 10px;">
                                        <a href="javascript:void(document.title&&window.open('<?= $hostUrl ?>/common/share-web.html?title='+encodeURIComponent(document.title)+'&url='+encodeURIComponent(location.href)))" title="<?=Yii::t('frontend','share_to_xuemei')?>" class="shareToLMS"><?=Yii::t('frontend','share_to_xuemei')?></a>
                                        </div>
                                        <div class="col-md-12 browserHelp ">
                                            <label><h2><?=Yii::t('frontend','chrome_browser')?>：</h2></label>
                                            <p><?=Yii::t('frontend','chrome_browser_collection')?></p>
                                            <img src="/static/frontend/images/help_share_plugin_chrome.png">
                                        </div><br>
                                        <div class="col-md-12 browserHelp ">
                                            <label><h2><?=Yii::t('frontend','ie_browser')?>：</h2> </label>
                                            <p><?=Yii::t('frontend','ie_browser_collection')?></p>
                                            <img src="/static/frontend/images/help_share_plugin_ie.png">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>