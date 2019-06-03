<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/23
 * Time: 11:22
 */
use yii\helpers\Url;
use common\models\learning\LnExamination;
?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?=$model->title?></h4>
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
                                    <label class="col-sm-2 control-label"><?=Yii::t('frontend', 'exam_kaoshimiaoshu')?></label>
                                    <div class="col-sm-10"><?=$model->description?></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-2 control-label"><?=Yii::t('frontend', 'exam_kaoqianmiaoshu')?></label>
                                    <div class="col-sm-10"><?=$model->pre_description?></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group form-group-sm">
                                    <label class="col-sm-2 control-label"><?=Yii::t('frontend', 'exam_kaohoumiaoshu')?></label>
                                    <div class="col-sm-10"><?=$model->after_description?></div>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </div>
                    <div class="infoBlock">
                        <div id="questionList">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="c"></div>
</div>
<script>
var get_paper_url = '<?=Url::toRoute(['/exam-manage-main/get-paper-question'])?>';
$(function(){
    $.get(get_paper_url, {examination_paper_id: '<?=$examination_paper_id?>', examination_mode: '<?=$examination_mode?>', random_mode: '<?=$random_mode?>', random_number: '<?=$model->random_number?>', each_page_number: '<?=$model->each_page_number?>', question_disorder: '<?=$model->question_disorder?>', option_disorder: '<?=$model->option_disorder?>', preview: '<?=$preview?>'}, function(html){
        if (html){
            $("#questionList").html(html);
            app.refreshAlert("#new_exam");
        }
    });
});
</script>