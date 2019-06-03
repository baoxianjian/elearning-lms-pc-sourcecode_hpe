<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/14
 * Time: 09:09
 */
use yii\bootstrap\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;

/* @var $this yii\web\View */
/* @var $content string */

//AppAsset::register($this);
//$this->title = ($this->pageTitle ? $this->pageTitle . '-' : '') . '惠普在线学习平台';
$this->pageTitle = Yii::t('frontend', 'top_person_info_text');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = $this->pageTitle;
?>
<?= Html::hiddenInput("hasWechat",$hasWechat,['id'=>'hasWechat'])?>
<?= Html::hiddenInput("duration",$duration,['id'=>'duration'])?>
<script>
    var changePassword='changePassword',basicInfo='basicInfo',setThumb='setThumb',wechatBind='wechatBind';

    var changePasswordUrl="<?=Yii::$app->urlManager->createUrl(['student/change-password'])?>",
        basicInfoUrl="<?=Yii::$app->urlManager->createUrl(['student/basic-info'])?>",
        setThumbUrl="<?=Yii::$app->urlManager->createUrl(['student/set-thumb'])?>",
        setWechatUrl="<?=Yii::$app->urlManager->createUrl(['student/set-wechat'])?>";

    $(document).ready(function () {
        FmodalLoad(changePassword,changePasswordUrl);
        FmodalLoad(basicInfo,basicInfoUrl);
        FmodalLoad(setThumb,setThumbUrl);
        var wechatFunction = "<?=$wechatFunction?>";

        if (wechatFunction == "START") {
            FmodalLoad(wechatBind, setWechatUrl);

            var duration = $("#duration").val() * 1000;
            setInterval("wechatInterval()", duration);//1000为1秒钟
        }

    });

    function FmodalLoad(target, url)
    {
        if(url){
            $('#'+target).empty();
            var loadingDiv = '<div class="load-wrapp"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p><?=Yii::t('frontend', 'loading')?>...</p></div></div>';
            $('#'+target).html(loadingDiv); // 设置页面加载时的loading图片
            $('#'+target).load(url);
        }
    }

    function reloadForm(formId) {
        var fun;
        if (formId == 'changepasswordform') {
            fun = "FmodalLoad('" + changePassword + "', '" + changePasswordUrl + "')";
        }
        else if (formId == 'updateInfoForm') {
            fun = "FmodalLoad('" + basicInfo + "', '" + basicInfoUrl + "')";
        }
        else if (formId == 'frm') {
            fun = "FmodalLoad('" + setThumb + "', '" + setThumbUrl + "')";
        }
        else if (formId == 'wechatForm') {
            //fun = "FmodalLoad('" + wechatBind + "', '" + setWechatUrl + "')";
        }
        setTimeout(fun, 1500);
    }

    function ReloadPageAfterUpdate(frameId, formId, modalId, isClose) {
        app.showMsg('<?=Yii::t('common', 'operation_success')?>', 1500);
        reloadForm(formId);
    }

    function wechatInterval()
    {
        var hasWechat = $("#hasWechat").val();
        var timestamp = new Date().getTime();
        //alert(currentComponentCode);

        var ajaxUrl = "<?=Url::toRoute(['student/get-wechat-status'])?>";
        ajaxData(ajaxUrl,
            "POST",
            {"timestamp": timestamp},
            "json",
            function (data) {
                var currentHasWechat = data.hasWechat;

                if (currentHasWechat != hasWechat) {
                    $("#hasWechat").val(currentHasWechat);
                    FmodalLoad(wechatBind, setWechatUrl);
                }
            }
        );
    }
</script>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12">
            <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
                <li role="presentation" class="active"><a href="#basicInfo" aria-controls="basicInfo" role="tab"
                                                          data-toggle="tab"><?= Yii::t('frontend', 'tab_basic_information') ?></a>
                </li>
                <li role="presentation"><a href="#setThumb" aria-controls="setThumb" role="tab"
                                           data-toggle="tab"><?= Yii::t('frontend', 'tab_setting_picture') ?></a>
                </li>
                <li role="presentation"><a href="#changePassword" aria-controls="changePassword" role="tab"
                                           data-toggle="tab"><?= Yii::t('frontend', 'tab_change_password') ?></a></li>
                <? if ($wechatFunction == "START") {?>
                <li role="presentation"><a href="#wechatBind" aria-controls="wechatBind" role="tab"
                                           data-toggle="tab"><?= Yii::t('frontend', 'tab_wechatBind') ?></a></li>
                <? }?>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="basicInfo">
                </div>
                <div role="tabpanel" class="tab-pane" id="setThumb">
                </div>
                <div role="tabpanel" class="tab-pane" id="changePassword">
                </div>
                <? if ($wechatFunction == "START") {?>
                    <div role="tabpanel" class="tab-pane" id="wechatBind">
                    </div>
                <? }?>
            </div>
        </div>
    </div>
</div>
<div style="height:20px"></div>