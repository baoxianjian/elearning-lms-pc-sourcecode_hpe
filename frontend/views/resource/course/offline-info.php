<?php
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','face_to_face').Yii::t('common','course_management'),'url'=>['/resource/course/manage-face']];
if($model->kid){
    $this->params['breadcrumbs'][] = Yii::t('frontend', 'edit_course');
    $this->params['breadcrumbs'][] = ['label' => Html::decode($model->course_name)];
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
    .-query-selected-list {
        z-index: 9!important;
    }
</style>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<script>
    var common_teacher = [];
    var common_address = [];
    var common_vendor = [];
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
                        'action' => Url::toRoute(['/resource/course/content-face','id'=>$model->kid]),
                    ]); ?>
                    <input type="hidden" name="LnCourse[kid]" value="<?=$model->kid?>"/>
                    <input type="hidden" name="LnCourse[course_name]" value="<?=$model->course_name?>"/>
                    <input type="hidden" name="LnCourse[course_type]" value="<?=$model->course_type?>"/>
                    <?php
                    $service = new \common\services\learning\ResourceService();
                    $hiddenInput = $service->GetResourceInput($resource);
                    echo $hiddenInput;
                    ?>
                    <textarea name="LnCourse[course_desc_nohtml]" style="display: none;"><?=$model->course_desc_nohtml?></textarea>
                    <textarea name="LnCourse[course_desc]" style="display: none;"><?=$model->course_desc?></textarea>
                    <input type="hidden" name="LnCourse[category_id]" value="<?=$model->category_id?>">
                    <input type="hidden" name="LnCourse[theme_url]" value="<?=$model->theme_url?>">
                    <input type="hidden" name="LnCourse[course_level]" value="<?=$model->course_level?>">
                    <input type="hidden" name="LnCourse[max_attempt]" value="<?=$model->max_attempt?>">
                    <input type="hidden" name="LnCourse[course_period]" value="<?=$model->course_period?>">
                    <input type="hidden" name="LnCourse[course_period_unit]" value="<?=$model->course_period_unit?>" />
                    <input type="hidden" name="LnCourse[default_credit]" value="<?=$model->default_credit?>">
                    <input type="hidden" name="LnCourse[course_language]" value="<?=$model->course_language?>">
                    <input type="hidden" name="LnCourse[course_price]" value="<?=$model->course_price?>">
                    <input type="hidden" name="LnCourse[currency]" value="<?=$model->currency?>">
                    <input type="hidden" name="LnCourse[start_time]" value="<?=$model->start_time?>">
                    <input type="hidden" name="LnCourse[end_time]" value="<?=$model->end_time?>">
                    <input type="hidden" name="LnCourse[is_display_pc]" value="<?=$model->is_display_pc?>">
                    <input type="hidden" name="LnCourse[is_display_mobile]" value="<?=$model->is_display_mobile?>">
                    <input type="hidden" name="domain_id" value="<?=$domain_id?>"/>
                    <input type="hidden" name="audience_id" value="<?=$audience_id?>"/>
                    <input type="hidden" name="course_time" value="<?=$course_time?>"/>
                    <?php
                    if (!empty($tag)){
                        foreach ($tag as $val){
                    ?>
                    <input type="hidden" name="tag[]" value="<?=$val?>"/>
                    <?php
                        }
                    }
                    ?>
                    <input type="hidden" name="certification_id" value="<?=$certification_id?>"/>
                    <input type="hidden" name="LnCourse[approval_rule]" value="<?=$model->approval_rule?>">
                    <input type="hidden" name="LnCourse[is_annony_view]" value="<?=$model->is_annony_view?>">
                    <input type="hidden" name="LnCourse[is_course_project]" value="<?=$model->is_course_project?>">
                    <?php
                    if (!empty($course_temp)) {
                        foreach ($course_temp as $i => $t) {
                    ?>
                    <div class="offline_course_part">
                        <h4>
                            <?= Yii::t('frontend', '{value}_stage_course',['value'=>'<font>'.($i+1).'</font>']) ?>
                            <?php
                            if ($i > 0) {
                            ?>
                            <div class="addAction pull-right"><a href="javascript:;" class="glyphicon glyphicon-remove del" title="<?= Yii::t('common', 'delete_button') ?>"></a></div>
                            <?php
                            }
                            ?>
                        </h4>
                        <hr/>
                        <div class="uploadFileTable">
                            <table class="table noneBorder">
                                <tr>
                                    <td width="150"><?= Yii::t('frontend', 'enroll_time') ?></td>
                                    <td>
                                        <input type="text" name="course[<?=$i?>][enroll_start_time]" style="width:22%;" data-type="rili" readonly="readonly" value="<?=!empty($t['enroll_start_time']) ? date('Y-m-d', $t['enroll_start_time']):''?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>"/><span style="width:6%;"><?= Yii::t('common', 'to2') ?> </span><input type="text" id="enroll_end_time[<?=$i?>]" name="course[<?=$i?>][enroll_end_time]" style="width:22%" data-type="rili" readonly="readonly" value="<?=!empty($t['enroll_end_time']) ? date('Y-m-d', $t['enroll_end_time']):''?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'copy_sucess') ?><?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')]) ?>"/>
                                        <a href="javascript:;" class="btn glyphicon glyphicon-remove resetBtn" title="<?= Yii::t('frontend', 'reset') ?><?= Yii::t('common', 'time') ?>" id="clear_end_time" onclick="document.getElementById('enroll_end_time[<?=$i?>]').value='';return false;"></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= Yii::t('frontend', 'start_course_time') ?></td>
                                    <td>
                                        <input type="text" name="course[<?=$i?>][open_start_time]" value="<?=!empty($t['open_start_time']) ? date('Y-m-d', $t['open_start_time']):''?>" style="width:22%;" data-type="rili" readonly="readonly" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>"/><span style="width:6%;"><?= Yii::t('common', 'to2') ?></span><input type="text" name="course[<?=$i?>][open_end_time]" style="width:22%" value="<?=!empty($t['open_end_time']) ? date('Y-m-d', $t['open_end_time']):''?>" data-type="rili" readonly="readonly" id="open_end_time[<?=$i?>]" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'copy_sucess') ?><?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')]) ?>"/>
                                        <a href="javascript:;" class="btn glyphicon glyphicon-remove resetBtn" title="<?= Yii::t('frontend', 'reset') ?><?= Yii::t('common', 'time') ?>" id="clear_end_time" onclick="document.getElementById('open_end_time[<?=$i?>]').value='';return false;"></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= Yii::t('frontend', 'places') ?></td>
                                    <td>
                                        <input type="text" name="course[<?=$i?>][limit_number]"  value="<?=isset($t['limit_number']) ? $t['limit_number'] : ''?>" style="width:22%;" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'places')]) ?>" onkeyup="this.value=this.value.replace(/\D+/,'');"/>
                                        <span style="width:auto;"><label><input type="checkbox" style="height: 24px;margin: 4px 10px 0 12px;" name="course[<?=$i?>][is_allow_over]" value="1" <?=isset($t['is_allow_over']) && intval($t['is_allow_over'])==1?'checked':""?>><?= Yii::t('frontend', 'allow_over_entry') ?></label></span>
                                        <input type="text" name="course[<?=$i?>][allow_over_number]" style="width:73px;" value="<?=isset($t['allow_over_number']) ? $t['allow_over_number'] : ''?>" onkeyup="this.value=this.value.replace(/\D+/,'');" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'over_entry')]) ?>" data-delay="1"/><span>人</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= Yii::t('frontend', 'train_place') ?></td>
                                    <td>
                                        <input type="text" id="trainingAddress_<?=$i?>" style="width:100%; margin-right:2%;"  placeholder="<?= Yii::t('frontend', 'name_and_code') ?>" data-url="<?=Url::toRoute(['/resource/course/get-training-address'])?>" autocomplete="off"/>
                                        <div id="select_trainingAddress_<?=$i?>"></div>
                                        <?php
                                        if (!empty($t['training_address'])) {
                                            ?>
                                            <script>
                                                $(function() {
                                                    common_address[<?=$i?>] = app.queryList("#trainingAddress_<?=$i?>", '<?=$t['training_address']?>');
                                                });
                                            </script>
                                        <?php
                                        }else{
                                        ?>
                                            <script>
                                                $(function() {
                                                    common_address[<?=$i?>] = app.queryList("#trainingAddress_<?=$i?>");
                                                });
                                            </script>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= Yii::t('frontend', 'lecturer_selection') ?></td>
                                    <td>
                                        <?php
                                        $temp_teacher = array();
                                        if (!empty($t['teacher_id'])){
                                            foreach ($t['teacher_id'] as $val){
                                                $temp_teacher[] = array('kid'=> $val['kid'], 'title' => urlencode($val['teacher_name']).'('.$val['email'].')');
                                            }
                                            $temp_teacher = urldecode(json_encode(array('results' => $temp_teacher)));
                                        }
                                        ?>
                                        <input type="text" class="popInput" id="teacherInput_<?=$i?>" style="width:100%; margin-right:2%;" placeholder="<?= Yii::t('frontend', 'can_choose_more_{value}',['value'=> Yii::t('common', 'lecturer') ]) ?>" data-url="<?=Url::toRoute(['/common/get-teacher','format'=>'new'])?>" data-mult="1" autocomplete="off" />
                                        <div id="select_teacherInput_<?=$i?>"></div>
                                        <?php
                                        if (!empty($temp_teacher)) {
                                            ?>
                                            <script>
                                                $(function() {
                                                    common_teacher[<?=$i?>] = app.queryList("#teacherInput_<?=$i?>", '<?=$temp_teacher?>');
                                                });
                                            </script>
                                        <?php
                                        }else {
                                        ?>
                                            <script>
                                                $(function() {
                                                    common_teacher[<?=$i?>] = app.queryList("#teacherInput_<?=$i?>");
                                                });
                                            </script>
                                            <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'supplier')?></td>
                                    <td>
                                        <input type="text" id="vendor_<?=$i?>" style="width:100%; margin-right:2%;" placeholder="<?= Yii::t('frontend', 'name_and_code') ?>" data-url="<?=Url::toRoute(['/resource/courseware/get-vendor'])?>" autocomplete="off"/>
                                        <div id="select_vendor_<?=$i?>"></div>
                                        <?php
                                        if (!empty($t['vendor'])) {
                                            ?>
                                            <script>
                                                $(function() {
                                                    common_vendor[<?=$i?>] = app.queryList("#vendor_<?=$i?>", '<?=$t['vendor']?>');
                                                });
                                            </script>
                                        <?php
                                        }else{
                                        ?>
                                            <script>
                                                $(function() {
                                                    common_vendor[<?=$i?>] = app.queryList("#vendor_<?=$i?>");
                                                });
                                            </script>
                                            <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php
                        }
                    }else{
                    ?>
                    <div class="offline_course_part">
                        <h4>
                            <?= Yii::t('frontend', '{value}_stage_course',['value'=>'<font>1</font>']) ?>
                            <div class="addAction pull-right"><a href="javascript:;" class="glyphicon glyphicon-remove del" title="<?= Yii::t('common', 'delete_button') ?>"></a></div>
                        </h4>
                        <hr/>
                        <div class="uploadFileTable">
                            <table class="table noneBorder">
                                <tr>
                                    <td width="150"><?= Yii::t('frontend', 'enroll_time') ?></td>
                                    <td>
                                        <input type="text" name="course[0][enroll_start_time]" style="width:22%;" data-type="rili" readonly="readonly" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>"/><span style="width:6%;"><?= Yii::t('common', 'to2') ?></span>
                                        <input type="text" name="course[0][enroll_end_time]" id="enroll_end_time[0]" style="width:22%" data-type="rili" readonly="readonly" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')]) ?>" />
                                        <a href="javascript:;" class="btn glyphicon glyphicon-remove resetBtn" title="<?= Yii::t('frontend', 'reset') ?><?= Yii::t('common', 'time') ?>" id="clear_end_time" onclick="document.getElementById('enroll_end_time[0]').value='';return false;"></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= Yii::t('frontend', 'start_course_time') ?></td>
                                    <td>
                                        <input type="text" name="course[0][open_start_time]" style="width:22%;" data-type="rili" readonly="readonly" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>" /><span style="width:6%;"><?= Yii::t('common', 'to2') ?></span>
                                        <input type="text" name="course[0][open_end_time]" id="open_end_time[0]" style="width:22%" data-type="rili" readonly="readonly" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')]) ?>" />
                                        <a href="javascript:;" class="btn glyphicon glyphicon-remove resetBtn" title="<?= Yii::t('frontend', 'reset') ?><?= Yii::t('common', 'time') ?>" id="clear_end_time" onclick="document.getElementById('open_end_time[0]').value='';return false;"></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= Yii::t('frontend', 'places') ?></td>
                                    <td>
                                        <input type="text" name="course[0][limit_number]" style="width:22%;" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'places')]) ?>" onkeyup="this.value=this.value.replace(/\D+/,'');" />
                                        <span style="width:auto;"><label><input type="checkbox" style="height: 24px;margin: 4px 10px 0 12px;" name="course[0][is_allow_over]" value="1"><?= Yii::t('frontend', 'allow_over_entry') ?></label></span>
                                        <input type="text" name="course[0][allow_over_number]" style="width:60px;" onkeyup="this.value=this.value.replace(/\D+/,'');" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'over_entry')]) ?>" data-delay="1" /><span>人</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= Yii::t('frontend', 'train_place') ?></td>
                                    <td>
                                        <input type="text" id="trainingAddress_0" name="course[0][training_address]" style="width:100%; margin-right:2%;" placeholder="<?= Yii::t('frontend', 'name_and_code') ?>" data-url="<?=Url::toRoute(['/resource/course/get-training-address'])?>" autocomplete="off" />
                                        <div id="select_trainingAddress_0"></div>
                                        <script>
                                            $(function(){
                                                common_address[0] = app.queryList("#trainingAddress_0");
                                            });
                                        </script>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= Yii::t('frontend', 'lecturer_selection') ?></td>
                                    <td>
                                        <input type="text" class="popInput" id="teacherInput_0" style="width:100%; margin-right:2%;" placeholder="<?= Yii::t('frontend', 'can_choose_more_{value}',['value'=> Yii::t('common', 'lecturer') ]) ?>" data-url="<?=Url::toRoute(['/common/get-teacher','format'=>'new'])?>" data-mult="1" autocomplete="off" />
                                        <div id="select_teacherInput_0"></div>
                                        <script>
                                            $(function(){
                                                common_teacher[0] = app.queryList("#teacherInput_0");
                                            });
                                        </script>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'supplier')?></td>
                                    <td>
                                        <input type="text" id="vendor_0" name="course[0][vendor]" style="width:100%; margin-right:2%;" placeholder="<?= Yii::t('frontend', 'name_and_code') ?>" data-url="<?=Url::toRoute(['/resource/courseware/get-vendor'])?>" autocomplete="off" />
                                        <div id="select_vendor_0"></div>
                                        <script>
                                            $(function(){
                                                common_vendor[0] = app.queryList("#vendor_0");
                                            });
                                        </script>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php
                    }
                    if (empty($id)) {
                    ?>
                    <div class="centerBtnArea col-md-12 col-sm-12">
                        <a href="javascript:;" class="btn btn-default btn-sm centerBtn additionBtn"><?= Yii::t('frontend', 'add_stage') ?></a>
                    </div>
                    <hr/>
                    <?php
                    }
                    ?>
                    <input type="submit" id="backEdit" class="btn btn-success pull-left" value="<?=Yii::t('common', 'previous')?>" />
                    <input type="submit" id="gotoContent" class="btn btn-success pull-right" value="<?=Yii::t('common', 'next')?>" />
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="copy_number" value="<?=count($course_temp)?>">
<div id="copy" class="hidden">
    <div class="offline_course_part">
        <h4>
            <?= Yii::t('frontend', '{value}_stage_course',['value'=>'<font>1</font>']) ?>
            <div class="addAction pull-right"><a href="javascript:;" class="glyphicon glyphicon-remove del" title="<?= Yii::t('common', 'delete_button') ?>"></a></div>
        </h4>
        <hr/>
        <div class="uploadFileTable">
            <table class="table noneBorder">
                <tr>
                    <td width="150"><?= Yii::t('frontend', 'enroll_time') ?></td>
                    <td>
                        <input type="text" name="course[_numbers_][enroll_start_time]" style="width:22%;" data-type="rili" readonly="readonly" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>"/><span style="width:6%;"><?= Yii::t('common', 'to2') ?></span>
                        <input type="text" name="course[_numbers_][enroll_end_time]" id="enroll_end_time[_numbers_]" style="width:22%" data-type="rili" readonly="readonly" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'copy_sucess') ?><?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')]) ?>" />
                        <a href="javascript:;" class="btn glyphicon glyphicon-remove resetBtn" title="<?= Yii::t('frontend', 'reset') ?><?= Yii::t('common', 'time') ?>" id="clear_end_time" onclick="document.getElementById('enroll_end_time[_numbers_]').value='';return false;"></a>
                    </td>
                </tr>
                <tr>
                    <td><?= Yii::t('frontend', 'start_course_time') ?></td>
                    <td>
                        <input type="text" name="course[_numbers_][open_start_time]" style="width:22%;" data-type="rili" readonly="readonly" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>" /><span style="width:6%;"><?= Yii::t('common', 'to2') ?></span>
                        <input type="text" name="course[_numbers_][open_end_time]" id="open_end_time[number]" style="width:22%" data-type="rili" readonly="readonly" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'course_open_end')]) ?>" />
                        <a href="javascript:;" class="btn glyphicon glyphicon-remove resetBtn" title="<?= Yii::t('frontend', 'copy_sucess') ?><?= Yii::t('frontend', 'reset') ?><?= Yii::t('common', 'time') ?>" id="clear_end_time" onclick="document.getElementById('open_end_time[_numbers_]').value='';return false;"></a>
                    </td>
                </tr>
                <tr>
                    <td><?= Yii::t('frontend', 'places') ?></td>
                    <td>
                        <input type="text" name="course[_numbers_][limit_number]" style="width:22%;" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'course_places')]) ?>" onkeyup="this.value=this.value.replace(/\D+/,'');" />
                        <span style="width:auto;"><label><input type="checkbox" style="height: 24px;margin: 4px 10px 0 12px;" name="course[_numbers_][is_allow_over]" value="1"><?= Yii::t('frontend', 'allow_over_entry') ?></label></span>
                        <input type="text" name="course[_numbers_][allow_over_number]" style="width:60px;" onkeyup="this.value=this.value.replace(/\D+/,'');" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'over_entry')]) ?>" data-delay="1" /><span>人</span>
                    </td>
                </tr>
                <tr>
                    <td><?= Yii::t('frontend', 'train_place') ?></td>
                    <td>
                        <input type="text" id="trainingAddress__numbers_" style="width:100%; margin-right:2%;" placeholder="<?= Yii::t('frontend', 'name_and_code') ?>" data-url="<?=Url::toRoute(['/resource/course/get-training-address'])?>" autocomplete="off" />
                        <div id="select_trainingAddress__numbers_"></div>
                    </td>
                </tr>
                <tr>
                    <td><?= Yii::t('frontend', 'lecturer_selection') ?></td>
                    <td>
                        <input type="text" class="popInput" id="teacherInput__numbers_" style="width:100%; margin-right:2%;" placeholder="<?= Yii::t('frontend', 'can_choose_more_{value}',['value'=> Yii::t('common', 'lecturer') ]) ?>" data-url="<?=Url::toRoute(['/common/get-teacher','format'=>'new'])?>" data-mult="1" autocomplete="off" />
                        <div id="select_teacherInput__numbers_"></div>
                    </td>
                </tr>
                <tr>
                    <td><?=Yii::t('common', 'supplier')?></td>
                    <td>
                        <input type="text" id="vendor__numbers_" style="width:100%; margin-right:2%;" placeholder="<?= Yii::t('frontend', 'name_and_code') ?>" data-url="<?=Url::toRoute(['/resource/courseware/get-vendor'])?>" autocomplete="off" />
                        <div id="select_vendor__numbers_"></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<script>
    app.preventGenCalendar();
    var back_url = "<?=Url::toRoute(['resource/course/edit-face', 'id'=>$model->kid])?>";
    $(function(){
        app.genCalendar();
        var validation = app.creatFormValidation($("#edit-form"));
        $("#backEdit").click(function(e){
            $(".uploadCourse").find('.offline_course_part').each(function(){
                var input_id_7 = $(this).find("input[id^='trainingAddress_']").attr('id');
                $("#select_"+input_id_7).empty();
                if (typeof input_id_7 != 'undefined') {
                    var count_id_7 = input_id_7.split('_')[1];
                    var address_json = common_address[count_id_7].get();
                    if (typeof address_json != 'undefined' && typeof address_json['kid'] != 'undefined') {
                        var address_title = address_json['title'].replace(/(\(.*?\))/g, '');
                        $("#select_" + input_id_7).append('<input type="hidden" name="course[' + count_id_7 + '][training_address]" value="' + address_title + '" /><input type="hidden" name="course[' + count_id_7 + '][training_address_id]" value="' + address_json['kid'] + '" />');
                    }
                }
                var input_id = $(this).find("input[id^='teacherInput_']").attr('id');
                $("#select_"+input_id).empty();
                var count_id = input_id.split('_')[1];
                var teacher_json_count_id = common_teacher[count_id].get();
                if (typeof teacher_json_count_id != 'undefined'){
                    var teacher_count_id_length = teacher_json_count_id.length;
                    if (teacher_count_id_length > 0){
                        for (var j = 0; j < teacher_count_id_length; j++){
                            $("#select_"+input_id).append('<input type="hidden" name="course['+count_id+'][teacher_id][]" value="'+teacher_json_count_id[j]['kid']+'" />');
                        }
                    }
                }

                var input_vendor = $(this).find("input[id^='vendor_']").attr('id');
                $("#select_"+input_vendor).empty();
                if (typeof input_vendor != 'undefined') {
                    var count_vendor = input_vendor.split('_')[1];
                    var vendor_json = common_vendor[count_vendor].get();
                    if (typeof vendor_json != 'undefined' && typeof vendor_json['kid'] != 'undefined') {
                        var vendor_title = vendor_json['title'].replace(/(\(.*?\))/g, '');
                        $("#select_" + input_vendor).append('<input type="hidden" name="course[' + count_vendor + '][vendor]" value="' + vendor_title + '" /><input type="hidden" name="course[' + count_vendor + '][vendor_id]" value="' + vendor_json['kid'] + '" />');
                    }else {
                        $("#select_" + input_vendor).append('<input type="hidden" name="course[' + count_vendor + '][vendor]" value="" /><input type="hidden" name="course[' + count_vendor + '][vendor_id]" value="" />');
                    }
                }
            });
            $("#edit-form").attr('action', back_url);
        });
        $("#gotoContent").click(function(e){
            var err = 0;
            var start_time = $("input[name='LnCourse[start_time]']").val();
            var end_time = $("input[name='LnCourse[end_time]']").val();
            var start_time1 = new Date(start_time.replace(/\-/g, "\/"));
            var end_time1 = new Date(end_time.replace(/\-/g, "\/"));
            $(".uploadCourse").find('.offline_course_part').each(function(){
                var id = $(this).find("input").eq(0).attr('name');
                id = id.replace('course[','');
                id = id.replace('[enroll_start_time]','');
                id = id.replace(']','');
                var beginDate=$(this).find("input").eq(0).val();
                if (beginDate==""){
                    validation.showAlert($(this).find("input").eq(0), "<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>");
                    err ++;
                    return false;
                }
                var endDate=$(this).find("input").eq(1).val();
                if (endDate==""){
                    validation.showAlert($(this).find("input").eq(1), "<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')]) ?>");
                    err ++;
                    return false;
                }
                var d1 = new Date(beginDate.replace(/\-/g, "\/"));
                var d2 = new Date(endDate.replace(/\-/g, "\/"));
                if(beginDate!="" && endDate!="" && d1 > d2)
                {
                    validation.showAlert($(this).find("input").eq(1), "<?= Yii::t('frontend', 'alert_warning_time2') ?>");
                    err ++;
                    return false;
                }

                if (( start_time!="" && d1<start_time1) || (end_time!="" && d2>end_time1)){
                    validation.showAlert($(this).find("input").eq(1), "<?= Yii::t('frontend', 'enroll_time_and_end_time_in_range') ?>");
                    err++;
                    return false;
                }
                validation.hideAlert($(this).find("input").eq(1));
                var beginDate2=$(this).find("input").eq(2).val();
                if (beginDate2==""){
                    validation.showAlert($(this).find("input").eq(2), "<?= Yii::t('frontend', 'course_open') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>");
                    err ++;
                    return false;
                }
                var d12 = new Date(beginDate2.replace(/\-/g, "\/"));
                if (d12 <= d2){
                    validation.showAlert($(this).find("input").eq(2), "<?= Yii::t('frontend', 'start_more_than_end') ?>");
                    err ++;
                    return false;
                }
                var endDate2=$(this).find("input").eq(3).val();
                if (endDate2==""){
                    validation.showAlert($(this).find("input").eq(3), "<?= Yii::t('frontend', 'course_open') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>");
                    err ++;
                    return false;
                }

                var d22 = new Date(endDate2.replace(/\-/g, "\/"));
                if(beginDate2!="" && endDate2!="" && d12 > d22)
                {
                    validation.showAlert($(this).find("input").eq(3), "<?= Yii::t('frontend', 'start_more_than_end') ?>");
                    err ++;
                    return false;
                }

                if ((start_time!="" && d12<start_time1) || (end_time!="" && d22>end_time1)){
                    validation.showAlert($(this).find("input").eq(3), "<?= Yii::t('frontend', 'course_time_and_end_time_in_range') ?>");
                    err++;
                    return false;
                }
                validation.hideAlert($(this).find("input").eq(3));
                var input4=$(this).find("input").eq(4).val().replace(/\s+/g,'');
                if (input4==""){
                    validation.showAlert($(this).find("input").eq(4), "<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'places')]) ?>");
                    err ++;
                    return false;
                }
                if (input4 == 0){
                    validation.showAlert($(this).find("input").eq(4), "<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_people_number_not_0'),['value'=>Yii::t('frontend','places')] ?>");
                    err ++;
                    return false;
                }
                var input5 = $(this).find('input').eq(5).is(':checked');
                var input6 = $(this).find('input').eq(6).val();
                if (input5 && input6==""){
                    err ++;
                    validation.showAlert($(this).find('input').eq(6), '<?= Yii::t('common', 'course') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'over_entry')]) ?>');
                    return false;
                }else if (input5 && input6 == 0){
                    err ++;
                    validation.showAlert($(this).find('input').eq(6), '<?= Yii::t('frontend', 'copy_sucess') ?><?= Yii::t('common', 'course') ?><?= Yii::t('frontend', '{value}_people_number_not_0'),['value'=>Yii::t('frontend','over_entry')] ?>');
                    return false;
                }else if (!input5 && input6.length > 0){
                    err ++;
                    validation.showAlert($(this).find('input').eq(6), '<?= Yii::t('frontend', 'confirm_is_over_entry') ?>');
                    return false;
                }else{
                    validation.hideAlert($(this).find('input').eq(6));
                }
                var input_id_7 = $(this).find("input").eq(7).attr('id');
                $("#select_"+input_id_7).empty();
                if (typeof input_id_7 != 'undefined') {
                    var count_id_7 = input_id_7.split('_')[1];
                    var address_json = common_address[count_id_7].get();
                    if (typeof address_json != 'undefined' && typeof address_json['kid'] != 'undefined') {
                        var address_title = address_json['title'].replace(/(\(.*?\))/g, '');
                        $("#select_" + input_id_7).append('<input type="hidden" name="course[' + count_id_7 + '][training_address]" value="' + address_title + '" /><input type="hidden" name="course[' + count_id_7 + '][training_address_id]" value="' + address_json['kid'] + '" />');
                    } else {
                        app.showMsg("<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'train_place')]) ?>");
                        err++;
                        return false;
                    }
                } else {
                    app.showMsg("<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'train_place')]) ?>");
                    err++;
                    return false;
                }
                validation.hideAlert($(this).find("input").eq(7));
                var input_id = $(this).find("input[id^='teacherInput_']").attr('id');
                $("#select_"+input_id).empty();
                if (typeof input_id != 'undefined') {
                    var count_id = input_id.split('_')[1];
                    var teacher_json_count_id = common_teacher[count_id].get();
                    if (typeof teacher_json_count_id != 'undefined') {
                        var teacher_count_id_length = teacher_json_count_id.length;
                        if (teacher_count_id_length > 0) {
                            for (var j = 0; j < teacher_count_id_length; j++) {
                                $("#select_" + input_id).append('<input type="hidden" name="course[' + count_id + '][teacher_id][]" value="' + teacher_json_count_id[j]['kid'] + '" />');
                            }
                        } else {
                            app.showMsg('<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'lecturer')]) ?>');
                            err++;
                            return false;
                        }
                    } else {
                        app.showMsg('<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'lecturer')]) ?>');
                        err++;
                        return false;
                    }
                }else{
                    app.showMsg('<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'lecturer')]) ?>');
                    err++;
                    return false;
                }
                /*<?=Yii::t('common', 'suppluer')?>*/
                var vendor = $(this).find("input[id^='vendor_']").attr('id');
                $("#select_"+vendor).empty();
                if (typeof vendor != 'undefined') {
                    var count_vendor = vendor.split('_')[1];
                    var vendor_json = common_vendor[count_vendor].get();
                    if (typeof vendor_json != 'undefined' && typeof vendor_json['kid'] != 'undefined') {
                        var vendor_title = vendor_json['title'].replace(/(\(.*?\))/g, '');
                        $("#select_" + vendor).append('<input type="hidden" name="course[' + count_vendor + '][vendor]" value="' + vendor_title + '" /><input type="hidden" name="course[' + count_vendor + '][vendor_id]" value="' + vendor_json['kid'] + '" />');
                    }else {
                        $("#select_" + vendor).append('<input type="hidden" name="course[' + count_vendor + '][vendor]" value="" /><input type="hidden" name="course[' + count_vendor + '][vendor_id]" value="" />');
                    }
                }
            });
            if (err > 0){
                return false;
            }
        });
        $(".uploadCourse").find('.offline_course_part').first().find(".addAction").addClass('hidden');
        /*增加一期课程*/
        $(".additionBtn").click(function(e){
            e.preventDefault();
            var html = $("#copy").html()
            var count = parseInt($("#copy_number").val());
            var count2 = parseInt($(".uploadCourse").find('.offline_course_part').length);
            var content = html.replace(/_numbers_/g, count);
            $(this).parent().before(content);
            app.genCalendar();
            $(".uploadCourse").find('.offline_course_part').last().find('h4').find('font').html(count2+1);
            $("#copy_number").val(count+1);
            common_teacher[count] = app.queryList("#teacherInput_"+count);
            common_address[count] = app.queryList("#trainingAddress_"+count);
            common_vendor[count] = app.queryList("#vendor_"+count);
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