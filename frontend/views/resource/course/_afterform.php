<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\helpers\TFileHelper;
use components\widgets\TDatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\learning\LnCourse */
/* @var $form yii\widgets\ActiveForm */

$ListRoute = '/../resource/courseware/empty';
$domainUrl = Url::toRoute(['/resource/course/domain','course_id'=>$model->kid]);
?>
<style>
    .pic-display {
        display: block;
        margin: 20px 0;
        width: auto;
    }
    input.form-control {padding: 0 12px;}
    select.form-control {padding: 0 0 0 12px;}
    .field-lncourse-course_desc_nohtml,.field-lncourse-category_id,.field-courseservice-category_id,.field-lncourse-theme {margin:0;}
    .field-lncourse-course_period {float: left; width: 75%;}
    .field-lncourse-course_period input {width: 100%;}
    .well {position: relative;}
    .auto {background-color: white;}
    #jsTree>div:nth-child(2) {overflow-x: hidden;}
    .field-lncourse-theme_url {display: none;}
    input[type=checkbox]{height: 15px;margin-right: 10px;}
    .noneBorder tr td:first-child {
        font-size: 15px;
        width: 100px;
    }
    .ke-statusbar .ke-statusbar-center-icon {background-image: none;}
    .label-domain input{margin-top: 0;}
    .ke-toolbar{
        /*display: none !important;*/
    }
    .ke-toolbar span {width: 22px!important;margin: 0!important;}
    .-selected-list span {width: auto;}
    .-query-list{
        display:inline-block;
        width:56%;
    }
</style>
<?= Html::cssFile('/static/common/css/jquery.Jcrop.css') ?>
<?= Html::jsFile('/static/common/js/ajaxupload.js') ?>
<?= Html::jsFile('/static/common/js/jquery.Jcrop.min.js') ?>
<?= Html::jsFile('/static/common/js/jquery.validate.min.js') ?>
<?= Html::jsFile('/static/common/js/jquery.validate-extend.js') ?>
<?= Html::cssFile("/components/kindeditor/themes/default/default.css")?>
<?= Html::jsFile('/components/kindeditor/kindeditor-all-min.js')?>
<?= Html::jsFile('/static/frontend/js/key.replace.js') ?>
<?php $form = ActiveForm::begin([
    'id'=>'edit-form',
    'method' => 'post',
    'action' => Url::toRoute(['/resource/course/aftersaveedit','id'=>$model->kid]),
]); ?>
<input type="hidden" id="lncourse-kid" class="form-control" name="LnCourse[kid]" value="<?=$model->kid?>">
<input type="hidden" id="lncourse-course_type" class="form-control" name="LnCourse[course_type]" value="<?=$model->course_type?>">
<!--<textarea style="display: none;" name="temp">--><?//=$temp?><!--</textarea>-->
<?php
$service = new \common\services\learning\ResourceService();
$hiddenInput = $service->GetResourceInput($resource);
echo $hiddenInput;
?>
<div class="uploadFileTable" style="display: inline-block;width: 90%;margin: 0 auto;text-align: left;">
    <table class="table noneBorder" style="width: 100%;">
        <tr>
            <td><?=Yii::t('common', 'course_name')?></td>
            <td>
                <input type="text" name="LnCourse[course_name]" id="course_name" style="width:100%" value="<?=$model->course_name?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'course_name')]) ?>" maxlength="152" />
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'course_desc')?></td>
            <td>
                <textarea id="course_desc_nohtml" name="LnCourse[course_desc_nohtml]" style="display: none;"><?=$model->course_desc_nohtml?></textarea>
                <textarea id="course_desc" name="LnCourse[course_desc]" style="height: 150px;"><?=$model->course_desc?></textarea>
                <!--?= $form->field($model, 'course_desc')->textarea(['rows' => 6,'style'=>'visibility:hidden;','id'=>'course_desc'])->label(false) ?-->
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'theme')?></td>
            <td>
                <p style="margin: 0;">
                    <input type="text" name="LnCourse[theme_url]" id="theme" style="width:60%; margin-right:2%;" readonly placeholder="<?= Yii::t('frontend', 'click_for_{value}',['value'=>Yii::t('common', 'upload')]) ?>" value="<?=$model->theme_url?>">
                    <input type="button" id="upload" class="btn btn-success btn-sm" value="<?= Yii::t('common', 'upload') ?>" style="max-width: 120px;zoom:1">
                </p>
                <div class="c"></div>
                <div class="upload-info"></div>
                <div class="pic-display" style="<?= $model->theme_url ? '' : 'display: none;' ?> text-align: center;border: 1px dotted #ccc;">
                    <?= $model->theme_url ? '<img src="'.$model->theme_url.'" style="max-width:500px;"/>' : ''?>
                </div>
                <div class="text-info"></div>
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'course_level')?></td>
            <td>
                <?=$form->field($model, 'course_level')->dropDownList($dictionary_level_list,['style'=>'width:100%'])->label(false)?>
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'category_id')?></td>
            <td>
                <?= $form->field($model, 'category_id')->hiddenInput()->label(false)?>
                <div id="jsTree"></div>
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'course_max_attempt')?></td>
            <td>
                <input type="text" name="LnCourse[max_attempt]" id="course_max_attempt" style="width:100%;" onkeyup="this.value=this.value.replace(/\D+/,'');" onblur="this.value=this.value.replace(/\D+/,'');" value="<?=$model->max_attempt?>" placeholder="<?=Yii::t('common', 'course_max_attempt_tips')?>" />
                <div class="clearfix"></div>
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'course_period')?></td>
            <td>
                <input type="text" name="LnCourse[course_period]" id="course_period" style="width:100%;" onkeyup="replaceToFloat(this,1);" onblur="replaceToFloat(this,1);" value="<?=$model->course_period?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'course_period')]) ?>" />
                <div class="clearfix"></div>
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'course_period_unit')?></td>
            <td>                                                            
                <?=$form->field($model, 'course_period_unit')->dropDownList($course_period_unit_list,['style'=>'width:100%'])->label(false)?>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'course_default_credit')?></td>
            <td>
                <input type="text" name="LnCourse[default_credit]" id="default_credit" style="width:100%;" onkeyup="replaceToFloat(this,1);" onblur="replaceToFloat(this,1);" value="<?=$model->default_credit?>" />
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'course_language')?></td>
            <td>
                <?=$form->field($model, 'course_language')->dropDownList($dictionary_lang_list,['style'=>'width:100%'])->label(false)?>
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'course_price')?></td>
            <td>
                <input type="text" style="width:100%;ime-mode:disabled;" name="LnCourse[course_price]" id="course_price" value="<?=$model->course_price?>" data-mode="COMMON" data-condition="^[0-9]*(\.[0-9]{1,2})?$" data-alert="<?=Yii::t('frontend', '{value}_2_decimal',['value'=>Yii::t('common', 'course_price')])?>" />
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'currency')?></td>
            <td>
                <?=$form->field($model, 'currency')->dropDownList($dictionary_currency_list,['style'=>'width:100%'])->label(false)?>
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'time_validity')?></td>
            <td>
                <input type="text" id="lncourse-start_time" name="LnCourse[start_time]" data-type="rili" readonly="readonly" value="<?=$model->start_time?>" style="width:25%; padding: 0 12px;" data-mode="COMMON" data-condition="required" data-alert="re">
                <span style="width:6%;"><?=Yii::t('common', 'to2')?></span>
                <input type="text" id="lncourse-end_time" name="LnCourse[end_time]" data-type="rili" readonly="readonly" style="width:25%; padding: 0 12px;" value="<?=$model->end_time?>">
                <a href="javascript:;" class="btn glyphicon glyphicon-remove resetBtn" title="<?= Yii::t('frontend', 'reset') ?><?=Yii::t('frontend','time')?>" id="clear_end_time" onclick="$('#lncourse-end_time').val('');return false;"></a>
            </td>
        </tr>
        <?php if($model->course_type=='1'){?>
            <tr>
                <td><?= Yii::t('frontend', 'enroll') ?><?=Yii::t('frontend','time')?></td>
                <td>
                    <input id="enroll_start_time" type="text" name="LnCourse[enroll_start_time]" style="width:25%; padding: 0 12px;" data-type="rili" readonly="readonly" value="<?=!empty($model->enroll_start_time) ? date('Y-m-d', $model->enroll_start_time):''?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>"/><span style="width:6%;"><?=Yii::t('common', 'to2')?></span><input type="text" id="enroll_end_time" name="LnCourse[enroll_end_time]" style="width:25%; padding: 0 12px;" data-type="rili" readonly="readonly" value="<?=!empty($model->enroll_end_time) ? date('Y-m-d', $model->enroll_end_time):''?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', 'enroll') ?><?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')]) ?>"/>
                    <a href="javascript:;" class="btn glyphicon glyphicon-remove resetBtn" title="<?= Yii::t('frontend', 'reset') ?><?=Yii::t('frontend','time')?>" id="clear_end_time" onclick="document.getElementById('enroll_end_time').value='';return false;"></a>
                </td>
            </tr>
        <?}?>
        <tr>
            <td><?=Yii::t('common', 'relate_{value}', ['value'=>Yii::t('common','domain')])?></td>
            <td>
                <?php
                foreach ($domain as $key=>$val){
                    ?>
                    <label><input type="checkbox" name="domain_id[]" value="<?=$val->kid?>" <?php echo in_array($val->kid, $domain_id) ? 'checked' : ''; ?> style="height: auto; width: auto;"> <?= Html::encode($val->domain_name)?></label>
                    <?php
                }
                ?>
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'display_name')?></td>
            <td>
                <label style="margin-right:20px">
                    <input type="checkbox" name="LnCourse[is_display_pc]" value="1" style="width: auto;" <?=$model->is_display_pc==\common\models\learning\LnCourse::DISPLAY_PC_YES ? 'checked' : ''?>> <?=Yii::t('common', 'position_pc')?>
                </label>
                <label>
                    <input type="checkbox" name="LnCourse[is_display_mobile]" value="1" style="width: auto;" <?=$model->is_display_mobile==\common\models\learning\LnCourse::DISPLAY_MOBILE_YES ? 'checked' : ''?>> <?=Yii::t('common', 'position_mobile')?>
                </label>
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('common', 'tag')?></td>
            <td>
                <input type="text" class="popInput" id="tag" style="width:100%; margin-right:2%;" placeholder="<?= Yii::t('frontend', 'can_choose_more_{value}',['value'=> Yii::t('common', 'tag') ]) ?>" data-url="<?=Url::to(['/student/get-tag','cate_code'=>'course','format'=>'new'])?>" data-option="1" data-mult="1" autocomplete="off" />
                <div id="tagDiv"></div>
            </td>
        </tr>
        <tr>
            <td><?= Yii::t('common', 'lecturer') ?></td>
            <td>
                <input type="text" class="popInput" id="teacher" style="width:100%; margin-right:2%;" placeholder="<?= Yii::t('frontend', 'can_choose_more_{value}',['value'=> Yii::t('common', 'lecturer') ]) ?>" data-url="<?=Url::toRoute(['/common/get-teacher','format'=>'new'])?>" data-mult="1" autocomplete="off" />
                <div id="teacherDiv"></div>
            </td>
        </tr>
        <tr>
            <td><?= Yii::t('common', 'serial') ?></td>
            <td>
                <input type="text" class="popInput" id="certification" style="width:100%; margin-right:2%;" onclick="this.value=this.value.replace(/\s+/g,'');" placeholder="<?= Yii::t('frontend', 'can_choose_one_{value}',['value'=> Yii::t('common', 'serial') ]) ?>" data-url="<?=Url::toRoute(['/common/get-certification','course_type'=> '0', 'format'=>'new'])?>" autocomplete="off" />
                <div id="certificationDiv"></div>
            </td>
        </tr>
    </table>
</div>
<hr/>
<div class="form-group">
    <?= Html::submitButton(Yii::t('common', 'submit'), ['id'=>'saveCourse','class' => 'btn btn-success pull-right' ]) ?>
</div>
<input type="hidden" name="course_time" value="<?=$course_time?>" />
<?php ActiveForm::end(); ?>
<script>
    var select_node = '<?=$tree_node_id?>';
    function jsTreeBack(target, data, textStatus){
        $("#" + target).html(data);
        if (typeof select_node != 'undefined' && select_node != ""){
            var t = setInterval(function() {
                var lilen = $("#jsTree_tree").find('li').length;
                if (lilen > 1){
                    $("#jsTree_tree li.jstree-node").attr('aria-selected', 'false');
                    $("#jsTree_tree a.jstree-anchor").removeClass('jstree-clicked');
                    $("#" + select_node).attr('aria-selected', 'true').find("a#" + select_node + '_anchor').addClass('jstree-clicked');
                    $("#jsTree_tree_changed_result").val('["' + select_node + '"]');
                    $("#jsTree_tree_selected_result").val('["' + select_node + '"]');
                    $("#jsTree_tree").attr('aria-activedescendant', select_node);
                    //$("#-1").attr();
                    clearInterval(t);
                }
            },500);
        }
    }
    function TreeCallback(){

    }
    function loadTree(){
        var ajaxUrl = "<?=Url::toRoute(['tree-node/tree',
        'TreeType'=>'course-category',
        'ContentName'=>'tree-node',
        'ListRoute'=> $ListRoute])?>";
        ajaxGet(ajaxUrl, "jsTree", jsTreeBack);
    }
    var validation = app.creatFormValidation($("#edit-form"));
    <?php
    if (!empty($tag)){
    ?>
    window.after_tags = app.queryList("#tag", '<?=$tag?>');
    <?php
    }else{
    ?>
    window.after_tags = app.queryList("#tag");
    <?php
    }

    if (!empty($teacher)){
    ?>
    window.after_teacher = app.queryList("#teacher", '<?=$teacher?>');
    <?php
    }else{
    ?>
    window.after_teacher = app.queryList("#teacher");
    <?php
    }

    if (!empty($certification)){
    ?>
    window.after_certification = app.queryList("#certification", '<?=$certification?>');
    <?php
    }else{
    ?>
    window.after_certification = app.queryList("#certification");
    <?php
    }
    ?>
    jQuery(document).ready(function () {
        loadTree();
        $("#saveCourse").click(function() {
            var err = 0;
            var start_time = $("input[name='LnCourse[start_time]']").val();
            var end_time = $("input[name='LnCourse[end_time]']").val();
            var start_time1 = new Date(start_time.replace(/\-/g, "\/"));
            var end_time1 = new Date(end_time.replace(/\-/g, "\/"));
            var beginDate1=$('#lncourse-start_time').val();
            if (beginDate1==""){
                validation.showAlert($('#enroll_start_time'), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>");
                err ++;
                return false;
            }
            var endDate1=$('#lncourse-end_time').val();
            var d3 = new Date(beginDate1.replace(/\-/g, "\/"));
            var d4 = new Date(endDate1.replace(/\-/g, "\/"));
            if(beginDate1!="" && endDate1!="" && d3 > d4)
            {
                validation.showAlert($('#start_time'), "<?= Yii::t('frontend', 'start_time_beyond_end_time') ?>");
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
                validation.showAlert($('#enroll_start_time'), "<?= Yii::t('frontend', 'enroll_time_beyond_end_time') ?>");
                err ++;
                return false;
            }

            if (( start_time!="" && d1<start_time1) || (end_time!="" && d2>end_time1)){
                validation.showAlert($('#enroll_start_time'), "<?= Yii::t('frontend', 'enroll_time_and_end_time_in_range') ?>");
                err++;
                return false;
            }
            validation.hideAlert($('#enroll_start_time'));
            <?php }?>
            var course_name = $("#course_name").val();
            course_name = course_name.replace(/(^\s*)|(\s*$)/g,'');
            if (course_name == ""){
                validation.showAlert($("#course_name"), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'course_name')]) ?>");
                return false;
            }
            if (app.stringLength(course_name) > 150){
                validation.showAlert($("#course_name"), "<?= Yii::t('frontend', '{value}_limit_50_word',['value'=>Yii::t('common', 'course_name')]) ?>");
                return false;
            }
            var select_category = $("#jsTree_tree_selected_result").val().split(',');
            if (select_category.length > 1){
                app.showMsg("<?= Yii::t('frontend', 'can_choose_one_{value}',['value'=> Yii::t('common', 'category_id') ]) ?>");
                return false;
            }
            var course_category_id = $("#jsTree_tree_selected_result").val();
            course_category_id = course_category_id.replace('[', '');
            course_category_id = course_category_id.replace(/\"/g, '');
            course_category_id = course_category_id.replace(']', '');
            course_category_id = course_category_id.split(',')[0];
            if (course_category_id == "" || course_category_id == '-1') {
                app.showMsg('<?= Yii::t('common', 'select_{value}',['value'=>Yii::t('common', 'category_id')]) ?>');
                return false;
            }
            $("#lncourse-category_id").val(course_category_id);
            if ($("#course_period").val()==""){
                validation.showAlert($("#course_period"), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'class_hour')])?>");
                return false;
            }
            var course_price = $("#course_price").val();
            if (course_price!="" && !/^[0-9]*(\.[0-9]{1,2})?$/.test(course_price)){
                validation.showAlert($("#course_price"), "<?=Yii::t('frontend', '{value}_2_decimal',['value'=>Yii::t('common', 'course_price')])?>");
                return false;
            }
            var beginDate=$("#lncourse-start_time").val();
            var endDate=$("#lncourse-end_time").val();
            var d1 = new Date(beginDate.replace(/\-/g, "\/"));
            var d2 = new Date(endDate.replace(/\-/g, "\/"));
            if(beginDate!="" && endDate!="" && d1 > d2)
            {
                validation.showAlert($("#lncourse-start_time"), "<?= Yii::t('frontend', 'alert_warning_time') ?>");
                return false;
            }
            var domainSelected = $("input[name='domain_id[]']:checked").length;
            if (domainSelected == 0) {
                app.showMsg("<?= Yii::t('common', 'select_{value}',['value'=>Yii::t('common', 'domain_id')]) ?>！");
                return false;
            }
            var is_display_pc = $("input[name='LnCourse[is_display_pc]']:checked").val();
            var is_display_mobile = $("input[name='LnCourse[is_display_mobile]']:checked").val();
            if (typeof is_display_pc == 'undefined' && typeof is_display_mobile == 'undefined'){
                app.showMsg('<?= Yii::t('frontend', 'course_visibility') ?>！');
                return false;
            }
            $("#course_desc_nohtml").val(editor.text().replace(/(^\s*)|(\s*$)/g,''));
            $("#tagDiv").empty();
            var tags = [];
            var tag_json = after_tags.get();
            if (typeof tag_json != 'undefined'){
                var tag_length  = tag_json.length;
                if (tag_length > 0){
                    for (var i = 0; i < tag_length; i++){
                        tags.push(tag_json[i]['title']);
                        $("#tagDiv").append('<input type="hidden" name="tag[]" value="'+tag_json[i]['title']+'" />');
                    }
                }
            }
            if (tags.length == 0){
                app.showMsg('<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'tag')]) ?>');
                return false;
            }else if (tags.length > 5){
                app.showMsg('<?= Yii::t('frontend', 'tag_less_5') ?>');
                return false;
            }
            $("#teacherDiv").empty();
            var teacher_json = after_teacher.get();
            if (typeof teacher_json != 'undefined'){
                var teacher_length = teacher_json.length;
                if (teacher_length > 0){
                    for (var j = 0; j < teacher_length; j++){
                        $("#teacherDiv").append('<input type="hidden" name="teacher_id[]" value="'+teacher_json[j]['kid']+'" />');
                    }
                }
            }

            $("#certificationDiv").empty();
            var certification_json = after_certification.get();
            if (typeof certification_json != 'undefined' && typeof certification_json['kid'] != 'undefined'){
                $("#certificationDiv").append('<input type="hidden" name="certification_id" value="'+certification_json['kid']+'" />');
            }
        });
    });

    var ajaxUploadUrl = "<?=Url::toRoute(['/common/upload'])?>";
    var g_oJCrop = null;
    //alert(ajaxUploadUrl);
    //异步上传文件
    new AjaxUpload("#upload", {
        action: ajaxUploadUrl,
        type: "POST",
        name: 'myfile',
        data: {'_csrf': '<?= Yii::$app->request->csrfToken ?>'},
        onSubmit: function (file, ext) {
            if ($(".text-info img").length > 0) {
                $(".upload-info").html("<div style='color:#ff0000;margin:5px;'>" + "<?=Yii::t('common', 'file_cropped')?>" + "</div>");
                return false;
            }
            $(".upload-info").html("<div style='color:#008000;margin:5px;'>" + "<?=Yii::t('common', 'uploading')?>" + "</div>");
        },
        onComplete: function (file, response) {
            if (g_oJCrop != null) {
                g_oJCrop.destroy();
            }
            if (response == "<?=Yii::t('common', 'file_type_error')?>" || response == "<?=Yii::t('common', 'upload_error')?>") {
                $(".upload-info").html("<div style='color:#ff0000;margin:5px;'>" + response + "</div>");
            }
            else {
                //生成元素
                $(".pic-display").show().html("<div class='thum'><img id='target' src='" + response + "'/></div>");
                //传递参数上传
                $("#theme").val(response);
                //更新提示信息
                $(".upload-info").html("<div style='color:#008000;margin:5px;'>" + "<?=Yii::t('common', 'upload_completed')?>" + "</div>");
            }
        }
    });
    var editor;
    KindEditor.ready(function (K) {
        editor = K.create('#course_desc', {
            filterMode: true,
            allowFileManager: false,
            allowImageRemote : false,
            autoHeightMode: true,
            width:'100%',
            items: [
                'justifyleft', 'justifycenter', 'justifyright',
                'formatblock', 'fontsize', 'forecolor', 'hilitecolor', 'bold',
                'italic', 'underline', 'image', 'link', 'unlink'
            ],
            afterCreate: function () {
                this.sync();
            },
            afterBlur: function () {
                this.sync();
            }
        });
    });
</script>

