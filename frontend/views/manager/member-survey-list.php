<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/12/2
 * Time: 15:07
 */
use common\models\learning\LnInvestigation;
use common\helpers\TStringHelper;
use common\helpers\TTimeHelper;
use yii\helpers\Html;

?>
<? if ($data): foreach($data as $v):?>
    <div class="col-md-12 myLessonList">
        <div class="col-sm-12">
            <h5 class="lessWord">【<?=TStringHelper::GetInvestigationTypeText($v['investigation_type'])?>】<?=Html::encode($v['title'])?></h5>
            <? if($v['answer_type']===LnInvestigation::ANSWER_TYPE_REALNAME && $v['complete_at']):?>
                <a href="<?= Yii::$app->urlManager->createUrl(['manager/survey-result', 'id' => $v['investigation_id'],'uid'=>$user_id]) ?>" class="check_detail" target="_blank"><?= Yii::t('common', 'view_button') ?></a>
            <? endif; ?>
            <p class="longDate"><?= Yii::t('common', 'time_validity') ?>：<?=TTimeHelper::toDateTime($v['start_at'],'Y年m月d日 H:i') ?> - <?=TTimeHelper::toDateTime($v['end_at'],'Y年m月d日 H:i') ?> &nbsp;&nbsp; <?= Yii::t('common', 'investigation_type') ?>：<?=TStringHelper::GetInvestigationAnswerTypeText($v['answer_type'])?> &nbsp;&nbsp;<?=Yii::t('common', 'status')?>：<? if($v['complete_at']):?><?=Yii::t('frontend', 'end_at')?><? echo TTimeHelper::toDateTime($v['complete_at'],'Y年m月d日 H:i'); else: ?><?=Yii::t('frontend', 'page_lesson_hot_tab_2')?><? endif;?></p>
        </div>
    </div>
<? endforeach; endif; ?>