<?php
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use yii\widgets\ActiveForm;

$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','face_to_face').Yii::t('common','course_management'),'url'=>['/resource/course/manage-face']];
if($model->kid){
    $this->params['breadcrumbs'][] = Yii::t('frontend', 'edit_course');
    $this->params['breadcrumbs'][] = ['label' => $model->course_name];
}else{
    $this->params['breadcrumbs'][] = Yii::t('common', 'create_{value}',['value'=>Yii::t('common','course')]);
    $this->params['breadcrumbs'][] = '';
}
?>
<style>
    .-query-list{
        display:inline-block;
        width:50%;
    }
</style>
<script>
    var common_teacher = [];
</script>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'moduleText' => ['label' => $model->kid ? Yii::t('common', 'edit_{value}',['value'=>Yii::t('common','course')]) : Yii::t('common', 'create_{value}',['value'=>Yii::t('common','course')])],
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-10 col-sm-12 col-md-offset-1">
            <div class="panel panel-default hotNews">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-cloud-upload"></i> <?=Yii::t('common', 'tab_basic_info')?>
                </div>
                <div class="panel-body uploadCourse">
                    <?php $form = ActiveForm::begin([
                        'id'=>'edit-form',
                        'method' => 'post',
                        'action' => Url::toRoute(['/resource/course/aftersaveedit-face','id'=>$model->kid]),
                    ]); ?>
                    <div class="offline_course_part">
                        <h4></h4>
                        <hr/>
                        <div class="uploadFileTable">
                            <table class="table noneBorder">

                                <tr>
                                    <td width="150"><?= Yii::t('frontend', 'start_course_time') ?></td>
                                    <td>
                                        <input id="open_start_time" type="text" disabled="disabled" style="width:22%;" readonly="readonly" value="<?=!empty($model->open_start_time) ? date('Y-m-d', $model->open_start_time):''?>" /><span style="width:6%;"><?=Yii::t('common', 'to2')?></span><input type="text" id="open_end_time"  style="width:22%" disabled="disabled" readonly="readonly" value="<?=!empty($model->open_end_time) ? date('Y-m-d', $model->open_end_time):''?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <td width="150"><?= Yii::t('common', 'time_validity') ?></td>
                                    <td>
                                        <input id="start_time" type="text" name="course[start_time]" style="width:22%;" data-type="rili" readonly="readonly" value="<?=!empty($model->start_time) ? date('Y-m-d', $model->start_time):''?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>"/><span style="width:6%;"><?= Yii::t('common', 'to2') ?></span><input type="text" id="end_time" name="course[end_time]" style="width:22%" data-type="rili" readonly="readonly" value="<?=!empty($model->end_time) ? date('Y-m-d', $model->end_time):''?>" />
                                        <a href="javascript:;" class="btn glyphicon glyphicon-remove resetBtn" title="<?= Yii::t('frontend', 'reset') ?><?=Yii::t('frontend','time')?>" id="clear_end_time" onclick="$('#end_time').val('');return false;"></a>
                                    </td>
                                </tr>
                                <?php if($model->course_type == '1'){ ?>
                                    <tr>
                                        <td width="150"><?= Yii::t('frontend', 'enroll') ?><?= Yii::t('common', 'time') ?></td>
                                        <td>
                                            <input id="enroll_start_time" type="text" name="course[enroll_start_time]" style="width:22%;" data-type="rili" readonly="readonly" value="<?=!empty($model->enroll_start_time) ? date('Y-m-d', $model->enroll_start_time):''?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>"/><span style="width:6%;"><?=Yii::t('common', 'to2')?></span><input type="text" id="enroll_end_time" name="course[enroll_end_time]" style="width:22%" data-type="rili" readonly="readonly" value="<?=!empty($model->enroll_end_time) ? date('Y-m-d', $model->enroll_end_time):''?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')]) ?>"/>
                                            <a href="javascript:;" class="btn glyphicon glyphicon-remove resetBtn" title="<?= Yii::t('frontend', 'reset') ?><?=Yii::t('frontend','time')?>" id="clear_end_time" onclick="$('#enroll_end_time').val('');return false;"></a>
                                        </td>
                                    </tr>
                                <?php }?>
                                <tr>
                                    <td><?= Yii::t('frontend', 'train_place') ?></td>
                                    <td>
                                        <input type="text" id="trainingAddress_0" name="course[training_address]" style="width:100%;" placeholder="<?= Yii::t('frontend', 'name_and_code') ?>" data-url="<?=Url::toRoute(['/resource/course/get-training-address'])?>" autocomplete="off" />
                                        <div id="select_trainingAddress_0"></div>
                                        <script>
                                            $(function(){
                                                window.common_address = app.queryList("#trainingAddress_0", '<?=$training_address?>');
                                            });
                                        </script>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= Yii::t('common', 'supplier') ?></td>
                                    <td>
                                        <input type="text" id="vendor_0" name="course[vendor]" style="width:100%;" placeholder="<?= Yii::t('frontend', 'name_and_code') ?>" data-url="<?=Url::toRoute(['/resource/courseware/get-vendor'])?>" autocomplete="off" />
                                        <div id="select_vendor_0"></div>
                                        <script>
                                            $(function(){
                                                window.common_vendor = app.queryList("#vendor_0", '<?=$vendor?>');
                                            });
                                        </script>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <input type="submit" id="gotoContent" class="btn btn-success pull-right" value="<?= Yii::t('frontend', 'finish') ?>" />
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    app.preventGenCalendar();
    var back_url = "<?=Url::toRoute(['resource/course/edit-face', 'id'=>$model->kid])?>";
    $(function(){
        var html = $("#copy").html();
        app.genCalendar();
        var validation = app.creatFormValidation($("#edit-form"));
        $("#gotoContent").click(function(){
            var err = 0;
            var start_time = $("input[name='course[start_time]']").val();
            var end_time = $("input[name='course[end_time]']").val();
            var start_time1 = new Date(start_time.replace(/\-/g, "\/"));
            var end_time1 = new Date(end_time.replace(/\-/g, "\/"));
            var beginDate1=$('#start_time').val();
            if (beginDate1==""){
                validation.showAlert($('#enroll_start_time'), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>");
                err ++;
                return false;
            }
            var endDate1=$('#end_time').val();
            var d3 = new Date(beginDate1.replace(/\-/g, "\/"));
            var d4 = new Date(endDate1.replace(/\-/g, "\/"));
            if(beginDate1!="" && endDate1!="" && d3 > d4)
            {
                validation.showAlert($('#start_time'), "<?= Yii::t('frontend', 'alert_warning_time3') ?>");
                err ++;
                return false;
            }

            <?php if($model->course_type == '1'){ ?>
            var beginDate=$('#enroll_start_time').val();
            if (beginDate==""){
                validation.showAlert($('#enroll_start_time'), "<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>");
                err ++;
                return false;
            }
            var endDate=$('#enroll_end_time').val();
            if (endDate==""){
                validation.showAlert($('#enroll_end_time'), "<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')]) ?>");
                err ++;
                return false;
            }
            var d1 = new Date(beginDate.replace(/\-/g, "\/"));
            var d2 = new Date(endDate.replace(/\-/g, "\/"));
            if(beginDate!="" && endDate!="" && d1 > d2)
            {
                validation.showAlert($('#enroll_start_time'), "<?= Yii::t('frontend', 'alert_warning_time2') ?>");
                err ++;
                return false;
            }
            var beginDate2=$('#open_start_time').val();
            var d12 = new Date(beginDate2.replace(/\-/g, "\/"));
            if (d12 <= d2){
                validation.showAlert($('#enroll_end_time'), "<?= Yii::t('frontend', 'start_more_than_end') ?>");
                err ++;
                return false;
            }
            if (( start_time!="" && d1<start_time1) || (end_time!="" && d2>end_time1)){
                validation.showAlert($('#enroll_start_time'), "<?= Yii::t('frontend', 'range_in_time') ?>");
                err++;
                return false;
            }
            validation.hideAlert($('#enroll_start_time'));
            <?php }?>
            if (err > 0){
                return false;
            }
            var address_json = common_address.get();
            if (typeof address_json != 'undefined' && typeof address_json['kid'] != 'undefined') {
                var address_title = address_json['title'].replace(/(\(.*?\))/g, '');
                var address_id = address_json['kid'];
                $("#select_trainingAddress_0").append('<input type="hidden" name="course[training_address]" value="' + address_title + '" /><input type="hidden" name="course[training_address_id]" value="' + address_id + '" />');
            }else{
                app.showMsg('<?= Yii::t('frontend', 'choose_in_train_place') ?>');
                return false;
            }
            var vendor_json = common_vendor.get();
            if (typeof vendor_json != 'undefined' && typeof vendor_json['kid'] != 'undefined') {
                var vendor_title = vendor_json['title'].replace(/(\(.*?\))/g, '');
                var vendor_id = vendor_json['kid'];
                $("#select_vendor_0").append('<input type="hidden" name="course[vendor]" value="' + vendor_title + '" /><input type="hidden" name="course[vendor_id]" value="' + vendor_id + '" />');
            }else{
                app.showMsg('<?= Yii::t('frontend', 'choose_in_train_place') ?>');
                return false;
            }

        });
        $(".uploadCourse").find('.offline_course_part').first().find(".addAction").addClass('hidden');
        /*增加一期课程*/
        $(".additionBtn").click(function(e){
            e.preventDefault();
            var count = parseInt($("#copy_number").val());
            var count2 = parseInt($(".uploadCourse").find('.offline_course_part').length);
            var content = html.replace(/number/g, count);
            $(this).parent().before(content);
            app.genCalendar();
            $(".uploadCourse").find('.offline_course_part').last().find('h4').find('font').html(count2+1);
            $("#copy_number").val(count+1);
            common_teacher[count] = app.queryList("#teacherInput_"+count);
        }) ;
        if ($(".uploadCourse").find('.offline_course_part').length == 0){
            $(".additionBtn").trigger('click');
            $(".uploadCourse").find('.offline_course_part').first().find(".addAction").addClass('hidden');
        }

        $("#edit-form").on('click', '.del', function(e){
            e.preventDefault();
            $(this).parents('.offline_course_part').remove();
            var i = 1;
            $(".uploadCourse").find(".offline_course_part").each(function(){
                $(this).find('h4').find('font').html(i);
                i++;
            });
        });

        $(".uploadCourse").on('keypress', 'input', function(event){
            var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
            if (keyCode == 13){
                return false;
            }
        });


    });
</script>
