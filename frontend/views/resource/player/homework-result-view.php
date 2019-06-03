<?php
/**
 * Created by PhpStorm.
 * User: Liu Cheng
 * Date: 1/27/2016
 * Time: 10:11 AM
 */
use common\models\learning\LnHomework;
use common\helpers\TTimeHelper;

?>
<style>
    #endline{
        float: right !important;
    }
</style>
<div class="content">
    <div class="header">
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend', 'sub_homework')?><i id="endline"><?=Yii::t('common', 'end_time2')?>:<?= TTimeHelper::toDate($finish_before_at) ?></i></h4>
    </div>
    <div class="modal-body">
        <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
                <div class=" panel-default scoreList">
                    <div class="panel-body">
                        <div class="infoBlock">
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group form-group-sm">
                                        <label class="col-sm-12 control-label"><?=Yii::t('frontend', 'homework_need')?></label>
                                        <div class="col-sm-12">
                                            <lable><?=html_entity_decode($requirement)?></lable>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php foreach($teacherFiles as $k=>$v):?>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'enclosure')?><?=($k+1)?>:</label>
                                            <a href="<?= Yii::$app->urlManager->createUrl(['resource/homework-down', 'id' => $v->kid, 'file_name' => $v->file_name]) ?>"><div class="col-sm-9"><?=$v->file_name?></div></a>
                                        </div>
                                    </div>
                                </div>
                            <? endforeach; ?>
                            <hr/>
                            <? if ($homework_mode === LnHomework::HOMEWORK_MODE_TEXT || $homework_mode === LnHomework::HOMEWORK_MODE_ALL): ?>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-12 control-label"><?=Yii::t('frontend', 'homework_content')?></label>
                                            <div class="col-sm-12">
                                                <textarea <? if(!empty($homeworkResult->homework_result)){ echo "disabled";}?>  placeholder="<?=Yii::t('frontend', 'fill_answer')?>"><?=$homeworkResult->homework_result?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <? endif; ?>
                            <? if ($homework_mode === LnHomework::HOMEWORK_MODE_FILE || $homework_mode === LnHomework::HOMEWORK_MODE_ALL): ?>
                                <? foreach ($userFiles as $k => $v): ?>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'enclosure')?><?=($k+1)?>:</label>
                                                <a href="<?= Yii::$app->urlManager->createUrl(['resource/homework-down', 'id' => $v->kid, 'file_name' => $v->file_name]) ?>"><div class="col-sm-9"><?=$v->file_name?></div></a>
                                            </div>
                                        </div>
                                    </div>
                                <? endforeach; ?>
                            <? endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>