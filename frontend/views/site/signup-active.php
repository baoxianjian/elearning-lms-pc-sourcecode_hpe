<?php
/**
 * Created by PhpStorm.
 * User: t62539
 * Date: 1/3/2016
 * Time: 11:59 AM
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Html;
$LoginLink = Yii::$app->urlManager->createAbsoluteUrl(['site/login']);
$this->pageTitle = Yii::t('frontend','new_user_regist');// Yii::t('frontend', 'page_lesson_hot_title');
$this->params['breadcrumbs'][] = $this->pageTitle;
?>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-8 col-md-offset-2">
            <div class=" panel-default scoreList">
                <div class="panel-body courseInfoInput">
                    <div class="row">
                        <div class="site-signup">
                            <h4><?=Yii::t('frontend','prompt_msg')?>：</h4>
                            <hr>
                            <?php if (!empty($model)) {?>
                                <p style="text-indent:30px; margin-top:50px;"><?=Yii::t('frontend','hello')?>：<?= Html::encode($model->real_name) ?> (<?= Html::encode($model->user_name) ?>)，</p>
                                <p style="text-indent:30px"><?=Yii::t('frontend','user_regist_sucess')?></p>
                                <br>
                                <div class="centerBtnArea">
                                    <a href="<?=$LoginLink?>" class="btn btn-success centerBtn" style="width:20%;"><?=Yii::t('frontend','go_for_login')?></a>
                                </div>
                            <? } else { ?>
                                    <?=Yii::t('frontend','invalid_activation_code')?>
                            <? }?>
                            <p style="margin-top:80px; text-align:right;"><?=Yii::t('frontend','from_text')?>：<?=Yii::t('system','frontend_manager')?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>