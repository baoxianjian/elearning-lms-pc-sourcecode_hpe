<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/29
 * Time: 16:56
 */
use common\models\learning\LnCourse;
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
$this->pageTitle = Yii::t('common', 'resource_management');
$this->params['breadcrumbs'][] = $this->pageTitle;
?>
<style>
    body{background: linear-gradient(90deg,#E0F1F3,#F6F8E3) !important;}
</style>
  <div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12" style="min-height:700px;">
            <div class="panel panel-default hotNews" style="box-shadow:none;">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-dashboard"></i> <?= Yii::t('common', 'resource_status') ?>
                </div>
                <div class="panel-body">
                    <div class="">
                        <div class="tiles">
                            <div class="tile-row">
                                <div class="lg-tile tile bg-red">
                                    <a href="<?=Url::toRoute(['/resource/course/manage'])?>" class="glyphicon glyphicon-book icon-lg"></a>
                                    <span class="icon-font-lg"><?= Yii::t('common', 'course') ?>(<?=$result['course_online']?>)</span>
                                    <div class="tile-caption">
                                        <header>
                                            <a href="<?=Url::toRoute(['/resource/course/edit'])?>" class="glyphicon glyphicon-plus addNewBtn" title="<?= Yii::t('frontend', 'build') ?>"></a>
                                        </header>
                                    </div>
                                </div>
                                <div class="sm-tile tile bg-blue">
                                    <a href="<?=Url::toRoute(['/resource/course/manage-face'])?>" class="glyphicon glyphicon-list-alt icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('common', 'face_to_face') ?>(<?=$result['course_face']?>)</span>
                                    <div class="tile-caption">
                                        <header>
                                            <a href="<?=Url::toRoute(['/resource/course/edit-face'])?>" class="glyphicon glyphicon-plus addNewBtn" title="<?= Yii::t('frontend', 'build') ?>"></a>
                                        </header>
                                    </div>
                                </div>
                                <div class="sm-tile tile bg-blue">
                                    <a href="<?=Url::toRoute(['/certification/index'])?>" class="glyphicon glyphicon-flag icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('common', 'serial') ?>(<?=$result['lnCertificationCount']?>)</span>
                                    <div class="tile-caption">
                                        <header>
                                            <a href="<?=Url::toRoute(['/certification/index'])?>#.btn.btn-success.pull-left@click" class="glyphicon glyphicon-plus addNewBtn" title="<?= Yii::t('frontend', 'build') ?>"></a>
                                        </header>
                                    </div>
                                </div>
                                <div class="sm-tile tile bg-blue">
                                    <a href="<?=Url::toRoute(['/investigation/index'])?>" class="glyphicon glyphicon-check icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('common', 'investigation') ?>(<?=$result['investigationCount']?>)</span>
                                </div>
                                <div class="sm-tile tile bg-blue">
                                    <a href="<?=Url::toRoute(['/tag/index'])?>" class="glyphicon glyphicon-tags icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('common', '{value}_list',['value'=>Yii::t('common','tag')]) ?>(<?= $result['TagCount']?>)</span>
                                </div>
                                <div class="md-tile tile bg-light-blue">
                                    <a href="<?=Yii::$app->urlManager->createUrl('/resource/courseware/manage')?>" class="glyphicon glyphicon-file icon-md"></a>
                                    <span class="icon-font-md"><?= Yii::t('common', 'courseware') ?>(<?=$result['courseware']?>)</span>
                                    <div class="tile-caption">
                                        <header>
                                            <a href="<?=Yii::$app->urlManager->createUrl('/resource/courseware/upload')?>" class="glyphicon glyphicon-plus addNewBtn" title="<?= Yii::t('frontend', 'build') ?>"></a>
                                        </header>
                                    </div>
                                </div>
                                <div class="md-tile tile bg-light-blue">
                                    <a href="<?=Yii::$app->urlManager->createUrl('/task/index')?>" class="glyphicon glyphicon-tasks icon-md"></a>
                                    <span class="icon-font-md"><?= Yii::t('frontend', 'push_task') ?>(<?= $task_count; ?>)</span>
                                </div>
                                <div class="sm-tile tile bg-orange">
                                    <a href="<?=Url::toRoute(['/teacher-manage/index'])?>" class="glyphicon glyphicon-education icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('common', 'lecturer') ?>(<?=$result['lnTeacherCount']?>)</span>
                                    <div class="tile-caption">
                                        <header>
                                            <a href="<?=Url::toRoute(['/teacher-manage/index'])?>#.btn.btn-success.pull-left@click" class="glyphicon glyphicon-plus addNewBtn" title="<?= Yii::t('frontend', 'build') ?>"></a>
                                        </header>
                                    </div>
                                </div>
                                <div class="sm-tile tile  bg-orange">
                                    <a href="<?=Url::toRoute(['/report-new/index'])?>" class="glyphicon glyphicon-list-alt icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('frontend', 'report') ?>(<?=$result['lnReportCount']?>)</span>
                                </div>
                               
                                <div class="sm-tile tile bg-green">
                                    <a href="<?=Url::toRoute(['/point/index'])?>" class="glyphicon glyphicon-equalizer icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('common', 'point') ?>(<?=$result['pointRuleCount']?>)</span>
                                </div>
                              
                                <div class="sm-tile tile bg-green">
                                    <a href="<?=Url::toRoute(['/exam-manage-main/index'])?>" class="glyphicon glyphicon-headphones icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('frontend', 'exam') ?>(<?=$result['lnExaminationCount']?>)</span>
                                </div>

                                <div class="sm-tile tile bg-green">
                                    <a href="<?=Url::toRoute(['/training-address/index'])?>" class="glyphicon glyphicon-earphone icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('frontend', 'place2') ?>(<?=$result['lnTrainingAddressCount']?>)</span>
                                    <div class="tile-caption">
                                        <header>
                                            <a href="<?=Url::toRoute(['/training-address/index'])?>?add=1" class="glyphicon glyphicon-plus addNewBtn" title="<?= Yii::t('frontend', 'brief') ?><?= Yii::t('frontend', 'build') ?>"></a>
                                        </header>
                                    </div>
                                </div>

                                <div class="sm-tile tile bg-green">
                                    <a href="<?=Url::toRoute(['/vendor/index'])?>" class="glyphicon glyphicon-globe icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('common', 'supplier') ?>(<?=$result['lnVendorCount']?>)</span>
                                    <div class="tile-caption">
                                        <header>
                                            <a href="<?=Url::toRoute(['/vendor/index'])?>?add=1" class="glyphicon glyphicon-plus addNewBtn" title="<?= Yii::t('frontend', 'build') ?>"></a>
                                        </header>
                                    </div>
                                </div>

                                <div class="sm-tile tile bg-green">
                                    <a href="<?=Url::toRoute(['/audience-manage/index'])?>" class="glyphicon glyphicon-user icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('common', 'audience') ?>(<?=$result['soAudienceCount']?>)</span>
                                    <div class="tile-caption">
                                        <header>
                                            <a href="<?=Url::toRoute(['/audience-manage/add'])?>#.btn.btn-success.pull-left@click" class="glyphicon glyphicon-plus addNewBtn" title="<?= Yii::t('frontend', 'build') ?>"></a>
                                        </header>
                                    </div>
                                </div>

                                <div class="sm-tile tile bg-green">
                                    <a href="#" class="glyphicon glyphicon-blackboard icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('frontend', 'nav_topic_text') ?>(0)</span>
                                    <div class="blockIcon">
                                        <i class="glyphicon glyphicon-remove-circle"></i>
                                        <p><?= Yii::t('frontend', 'opening') ?></p>
                                    </div>
                                </div>

                                <div class="sm-tile tile bg-green">
                                    <a href="#" class="glyphicon glyphicon-asterisk icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('frontend', 'resources') ?>(0)</span>
                                    <div class="blockIcon">
                                        <i class="glyphicon glyphicon-remove-circle"></i>
                                        <p><?= Yii::t('frontend', 'opening') ?></p>
                                    </div>
                                </div>

                                <div class="sm-tile tile bg-green">
                                    <a href="#" class="glyphicon glyphicon-question-sign icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('frontend', 'question_answer') ?>(0)</span>
                                    <div class="blockIcon">
                                        <i class="glyphicon glyphicon-remove-circle"></i>
                                        <p><?= Yii::t('frontend', 'opening') ?></p>
                                    </div>
                                </div>

                                <div class="sm-tile tile bg-green">
                                    <a href="#" class="glyphicon glyphicon-briefcase icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('frontend', 'expert_database') ?>(0)</span>
                                    <div class="blockIcon">
                                        <i class="glyphicon glyphicon-remove-circle"></i>
                                        <p><?= Yii::t('frontend', 'opening') ?></p>
                                    </div>
                                </div>

                                <div class="sm-tile tile bg-green">
                                    <a href="#" class="glyphicon glyphicon-th icon-sm"></a>
                                    <span class="icon-font-sm"><?= Yii::t('frontend', 'operate') ?>(0)</span>
                                    <div class="blockIcon">
                                        <i class="glyphicon glyphicon-remove-circle"></i>
                                        <p><?= Yii::t('frontend', 'opening') ?></p>
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