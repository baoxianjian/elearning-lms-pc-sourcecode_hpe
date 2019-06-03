<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/30
 * Time: 15:53
 */

use yii\helpers\Html;

$uid = Yii::$app->user->getId();
?>
<div class="panel-heading">
    <i class="glyphicon glyphicon-flag"></i> <?=Yii::t('frontend', '{value}_care_question',['value'=>count($data)])?>
    <!--                    <a class="pull-right" href="#" role="button">更多 &raquo;</a>-->
</div>
<? if (count($data) > 0):?>
    <div class="panel-body">
        <ul class="thumbList popOverPanel"  style="display: none;">
            <? foreach ($data as $care): ?>
                <li class="popContainer">
                    <a href="javascript:void(0);">
                        <img src="<?= $care->fwUser->getThumb() ?>" title="<?= Html::encode($care->fwUser->real_name) ?>" width="99" height="99"/>
                        <p class="name"><?= Html::encode($care->fwUser->real_name) ?></p>
                    </a>
                    <? if($uid!==$care->user_id): ?>
                        <ul class="popPanel">
                            <li><a href="javascript:void(0);" class="btn btn-xs" onclick="attentionUser(this,'<?=$care['user_id']?>')" data-id="<?=$care['user_id']?>"><?=in_array($care['user_id'],$attentionUser)? Yii::t('common', 'cancel_attention'): Yii::t('common', 'attention')?></a></li>
                            <li><a href="javascript:void(0);" class="btn btn-xs" onclick="showPointTransBox('<?=$care['user_id']?>')"><?=Yii::t('frontend', 'point_gratuity')?></a></li>
                        </ul>
                    <? endif; ?>
                </li>
            <? endforeach; ?>
        </ul>
        <?if($data!=null && count($data)>8):?>
            <div class="pageController">
                <a href="###" class="btn btn-xs pull-right" id="nextSwitch">&gt;</a>
                <a href="###" class="btn btn-xs pull-right" id="prevSwitch">&lt;</a>
            </div>
        <?endif;?>
    </div>
    <script>
        $(document).ready(
            function () {
                app.genSwitch($(".thumbList.popOverPanel"), $("#prevSwitch"), $("#nextSwitch"));
            });
    </script>
<? endif;?>
