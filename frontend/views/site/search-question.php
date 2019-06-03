<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 2015/8/1
 * Time: 17:09
 */

use common\helpers\TStringHelper;
use common\helpers\TTimeHelper;
use components\widgets\TLinkPager;
use common\models\social\SoQuestion;
use yii\helpers\Html;

$uid=Yii::$app->user->getId();
?>
<div class="panel panel-default scoreList">
    <div class="panel-body">
        <? foreach($data as $val):?>
            <h3><a href="<?= Yii::$app->urlManager->createUrl(['question/detail', 'id' => $val->kid]) ?>"><?=TStringHelper::HighlightDecode(Html::encode($val->title))  ?></a> <a href="javascript:void(0);" onclick="careQuestion(this,'<?=$val->kid?>')" class="follow pull-right" role="button"><?=in_array($val->kid,$care_questions)?Yii::t('frontend','page_info_good_cancel').Yii::t('common','attention'):Yii::t('common','attention')?></a> <a href="javascript:void(0);" onclick="CollectQuestion(this,'<?=$val->kid?>')" class="follow pull-right" role="button"><?=in_array($val->kid,$collect_questions)? Yii::t('common','audience_code').Yii::t('common','collection'):Yii::t('common','collection') ?></a></h3>
            <span><?=Yii::t('frontend','date_of_issue')?>:<?= TTimeHelper::toDateTime($val->created_at)?></span>
            <? if($val->question_type===SoQuestion::QUESTION_TYPE_COURSE):?>
                <span><?=Yii::t('frontend','from_text')?>:<?= $val->course_name;?></span>
            <?endif;?>
            <span><?=Yii::t('frontend','posted_question_text')?>:<?= $val->real_name?></span>
            <p><?= TStringHelper::HighlightDecode(Html::encode(trim($val->question_content))) ?></p>
            <hr />
        <? endforeach;?>
        <nav>
            <?php
            echo TLinkPager::widget([
                'id' => 'page2',
                'pagination' => $pages,
            ]);
            ?>
        </nav>
    </div>
</div>