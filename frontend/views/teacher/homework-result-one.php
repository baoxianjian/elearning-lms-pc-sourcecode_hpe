<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 7/9/2015
 * Time: 10:11 AM
 */
use yii\helpers\Url;
use yii\helpers\Html;
use components\widgets\TBreadcrumbs;
use components\widgets\TUploadifive;
use common\helpers\TTimeHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
?>
<style>
    #endline{
        float: right !important;
    }
    .uploadifive-button {
        background-color: #00993a !important;
        background-image: none !important;
        border-radius: 4px !important;
        border: 1px solid transparent !important;
    }
    .uploadifive-button:hover {
        background-color: #449d44 !important;
        border-color: #398439 !important;
        background-image:none !important;
    }

</style>

<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">[<?= Yii::t('frontend', 'homework') ?>]<?=$title?></h4>
</div>
<div class="content">
    <div class="header">
        <h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', 'sub_homework') ?><i id="endline"><?= Yii::t('common', 'end_time2') ?>:<?=TTimeHelper::FormatTime($result['finish_before_at'],2)?></i></h4>
    </div>
    <div class="content">
        <div class="courseInfo">
            <div role="tabpanel" class="tab-pane active" id="teacher_info">
                <div class=" panel-default scoreList">
                    <div class="panel-body">
                        <div class="infoBlock">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-12 control-label"><?= Yii::t('frontend', 'homework_need') ?></label>
                                            <div class="col-sm-12">
                                                <lable><?=html_entity_decode($result['requirement'])?></lable>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php foreach($result['teacherFiles'] as $k=>$v){?>
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-group form-group-sm">
                                                <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'enclosure') ?><?=($k+1)?>:</label>
                                                <?php $downlordurl = Url::toRoute(['/resource/homework-down','id'=>$v->kid,'file_name'=>$v->file_name])?>
                                                <a href="<?=$downlordurl?>" target="_blank"><div class="col-sm-9"><?=$v->file_name?></div></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php }?>
                            <?php if($result['homework_mode']==1||$result['homework_mode']==2){?>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group form-group-sm">
                                            <label class="col-sm-12 control-label"><?= Yii::t('frontend', 'homework_content') ?></label>
                                            <div class="col-sm-12">
                                                <textarea id="answer" <? if(isset($result['homeworkResult']->homework_result)){ echo "disabled";}?>  placeholder="<?=Yii::t('frontend', 'input_{value}',['value'=>Yii::t('frontend','result2')])?>"><?=$result['homeworkResult']->homework_result?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }?>
                            <?php if($result['homework_mode']==0||$result['homework_mode']==2){?>
                                    <?php foreach($result['userFiles'] as $k=>$v){?>
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-group form-group-sm">
                                                    <label class="col-sm-3 control-label"><?= Yii::t('frontend', 'homework_enclosure') ?><?=($k+1)?>:</label>
                                                    <?php $downlordurl = Url::toRoute(['/resource/homework-down','id'=>$v->kid,'file_name'=>$v->file_name])?>
                                                    <a href="<?=$downlordurl?>" target="_blank"><div class="col-sm-9"><?=$v->file_name?></div></a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }?>
                                <?php }?>
                            <div class="centerBtnArea">
                                <a href="###" class="btn btn-success btn-md centerBtn homeworDetailClose" style="width:20%"><?= Yii::t('common', 'back_button') ?></a>
                            </div>
                          </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
    <div class="c"></div>
</div>
<script>
    $(function(){
        $(".homeworDetailClose").on('click', function(){
            app.hideAlert($("#courseware"));
            $("#courseware").empty();
        }) ;
    });
</script>
