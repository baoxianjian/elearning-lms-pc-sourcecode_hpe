<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/26
 * Time: 10:08
 */
use yii\helpers\Html;
use yii\helpers\Url;

$langArray = [];
$langArray['zh_CN'] = Yii::t('common', 'language_zh-CN');
$langArray['en_US'] = Yii::t('common', 'language_en-US');
?>
<style>
    .table > tbody > tr > td{
        vertical-align: middle;
    }
    .table tr td:first-child{
        text-align: left !important;
    }
    .table-bordered > tbody > tr > td
    {
        border: 1px solid #ddd !important;
    }
</style>
<div class=" panel-default scoreList">
    <div class="panel-body">
        <div class="panel-body courseInfoInput">
            <? if ($hasWechat == "1") {?>
                <div class="row">
                    <div class="col-md-12">
                        <h4><?=Yii::t('frontend','wechat_msg')?></h4>
                        <table class="table table-hover table-bordered" style="width: 100% !important;">
                            <thead>
                            <tr>
                                <th width="10%">头像</th>
                                <th width="15%">昵称</th>
                                <th width="10%">性别</th>
                                <th width="15%">国家</th>
                                <th width="15%">省份</th>
                                <th width="15%">城市</th>
                                <th width="10%">语言信息</th>
                                <th width="10%">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($wechatModels as $wechatModel) {?>
                                <tr>
                                    <td><img src="<?= $wechatModel->headimg_url ?>" width="64" height="64"></td>
                                    <td><?= $wechatModel->nick_name ?></td>
                                    <td><?= $wechatModel->sex ?></td>
                                    <td><?= $wechatModel->country ?></td>
                                    <td><?= $wechatModel->province ?></td>
                                    <td><?= $wechatModel->city ?></td>
                                    <td><?= $langArray[$wechatModel->language] ? $langArray[$wechatModel->language] : $wechatModel->language ?></td>
                                    <td><button type="button" class="btn btn-danger unBindBtn" data-id="<?= $wechatModel->open_id ?>"><?= Yii::t('frontend', 'unfollow') ?></button></td>
                                </tr>
                            <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <? } else { ?>
                <? if (empty($errMessage)) {?>
                    <?=Yii::t('frontend','wechat_followed_public_number')?><br>
                    <img id="img_thumb" width="300" height="300" src="<?= $ticketUrl ?>" />
                <? } else { ?>
                    <?=$errMessage?>
                <? } ?>
            <? }?>
        </div>
    </div>
</div>
<script>
    $(".unBindBtn").bind("click", function () {
        var openId = $(this).attr('data-id');

        $.post('<?=Url::toRoute(['student/set-wechat'])?>', {'openId': openId}, function (data) {
            if (data.result === "success") {
                app.showMsg('操作成功',1000);
                setTimeout("FmodalLoad(wechatBind, setWechatUrl)",1000);
            } else {
                app.showMsg('操作失败');
            }
        }, 'json');
    });
</script>