<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/12/2
 * Time: 15:07
 */
use common\models\learning\LnExamination;
use common\helpers\TStringHelper;
use common\helpers\TTimeHelper;
use yii\helpers\Html;

?>
<? if ($data): foreach($data as $v):?>
    <div class="col-md-12 myLessonList">
        <div class="col-sm-9">
            <h5 class="lessWord"><?=Html::encode($v['title'])?></h5>
            <p style="width: 100% !important;"><? if($v['examination_mode'] === LnExamination::EXAMINATION_MODE_TEST): ?><?=Yii::t('frontend', 'exam_time')?>:<?=TTimeHelper::toDateTime($v['start_at'],'Y年m月d日 H:i') ?> - <?=TTimeHelper::toDateTime($v['end_at'],'Y年m月d日 H:i') ?> &nbsp;&nbsp; <? endif; ?><?=Yii::t('frontend', 'pass_grade')?>:<?=floatval($v['pass_grade'])?> &nbsp;&nbsp;<?=Yii::t('common', 'status')?>:<? if ($v['exam_result']):?><?=Yii::t('frontend', 'complete_status_done')?><? else: ?><?=Yii::t('frontend', 'page_lesson_hot_tab_2')?><? endif; ?></p>
            <p class="score_detail"><?=Yii::t('frontend', 'grade_detail')?> （<?=Yii::t('frontend', 'score_rule')?>：<?= Yii::t('common', 'exam_attempt_strategy_' . $v['attempt_strategy']) ?>）</p>
            <? if ($v['exam_result']): foreach($v['exam_result'] as $r): ?>
                <? if($v['examination_mode'] === LnExamination::EXAMINATION_MODE_TEST): ?>
                    <p><?=Yii::t('common', 'complete_end_at')?>： <?=TTimeHelper::toDateTime($r['end_at'],'Y年m月d日 H:i') ?> &nbsp;&nbsp; <?=Yii::t('frontend', 'score')?>:<?=floatval($r['examination_score'])?></p>
                <? else: ?>
                    <p><?=Yii::t('common', 'complete_end_at')?>： <?=TTimeHelper::toDateTime($r['end_at'],'Y年m月d日 H:i') ?> &nbsp;&nbsp; <?=Yii::t('frontend', 'score')?>:<?=$r['correct_rate']?>%</p>
                <? endif;?>
            <? endforeach; else: ?>
                <p class="score_no_data"><?=Yii::t('frontend', 'temp_no_record')?></p>
            <? endif;?>
        </div>
        <div class="col-sm-3">
            <p class="score_all"><?=Yii::t('common', 'courseware_default_credit')?></p>
            <? if ($v['exam_result'] && $v['exam_grade']): ?>
                <p class="score_all_rate"><?= is_numeric($v['exam_grade']) ? floatval($v['exam_grade']) : $v['exam_grade'] ?></p>
            <? else: ?>
                <p class="score_all_rate_no"><?=Yii::t('frontend', 'dit_not_receive')?></p>
            <? endif;?>
        </div>
    </div>
<? endforeach; endif; ?>