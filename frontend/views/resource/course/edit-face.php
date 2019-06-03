<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/29
 * Time: 16:56
 */

use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
use yii\widgets\ActiveForm;
use common\models\learning\LnCourse;

$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','face_to_face').Yii::t('common','course_management'),'url'=>['/resource/course/manage-face']];
if($model->kid){
    $this->params['breadcrumbs'][] = Yii::t('frontend', 'edit_course');
    $this->params['breadcrumbs'][] = ['label' => Html::decode($model->course_name)];
}else{
    $this->params['breadcrumbs'][] = Yii::t('common', 'create_{value}',['value'=>Yii::t('common','course')]);
    $this->params['breadcrumbs'][] = '';
}
$ListRoute = '/../resource/courseware/empty';
?>
<style>
    .form-control{float: none}
     .pic-display {
         display: block;
         margin: 20px 0;
         width: auto;
     }
    input.form-control {padding: 0 12px;}
    select.form-control {padding: 0 0 0 12px;}
    .field-lncourse-course_desc_nohtml,.field-lncourse-category_id,.field-courseservice-category_id,.field-lncourse-theme {margin:0;}
    .label-domain {float: left; font-size: 12px;}
    .label-domain input {width: auto; height: auto; }
    .field-lncourse-course_period {float: left; width: 75%;}
    .field-lncourse-course_period input {width: 100%;}
    .well {position: relative;}
    .auto {background-color: white;}
    #jsTree>div:nth-child(2) {overflow-x: hidden;}
    .field-lncourse-theme_url {display: none;}
    .ke-statusbar .ke-statusbar-center-icon {background-image: none;}
    .courseInfoInput{
        padding: 0 20px;
    }
    .ke-toolbar{
        /*display: none !important;*/
    }
    .ke-toolbar span {width: 22px!important;margin: 0!important;}
    .-selected-list span {width: auto;}
    .-query-selected-list {
        z-index: 9!important;
    }
</style>
<?= Html::jsFile('/static/common/js/ajaxupload.js') ?>
<?= Html::cssFile("/components/kindeditor/themes/default/default.css")?>
<?= Html::jsFile('/components/kindeditor/kindeditor-all-min.js')?>
<?= Html::jsFile('/static/frontend/js/key.replace.js') ?>
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
                <div class="panel-body courseInfoInput" style="text-align: center;">
                    <h4><?=Yii::t('common', 'please_input_base_info_at_this_panel')?></h4>
                    <hr/>
                    <div class="ln-course-create">
                        <?php $form = ActiveForm::begin([
                            'id'=>'edit-form',
                            'method' => 'post',
                            'action' => Url::toRoute(['/resource/course/offline-info','id'=>$model->kid]),
                        ]); ?>
                        <input type="hidden" id="lncourse-kid" class="form-control" name="LnCourse[kid]" value="<?=$model->kid?>">
                        <input type="hidden" id="lncourse-course_type" class="form-control" name="LnCourse[course_type]" value="<?=$model->course_type?>">
                        <input type="hidden" name="action" value="edit"/>
                        <?php
                        $service = new \common\services\learning\ResourceService();
                        $hiddenInput = $service->GetResourceInput($resource);
                        echo $hiddenInput;
                        ?>
                        <textarea style="display: none;" name="course_temp"><?=$course_temp?></textarea>
                        <div class="uploadFileTable" style="display: inline-block;width: 90%;margin: 0 auto;text-align: left;">
                            <table class="table noneBorder" style="width: 100%;">
                                <tr>
                                    <td><?=Yii::t('common', 'course_name')?></td>
                                    <td>
                                        <input type="text" name="LnCourse[course_name]" id="course_name" style="width:100%" value="<?=$model->course_name?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'course_name')]) ?>" maxlength="152" onblur="this.value=this.value.replace(/(^\s*)|(\s*$)/g,'');" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'course_desc')?></td>
                                    <td>
                                        <textarea id="course_desc_nohtml" name="LnCourse[course_desc_nohtml]" style="display: none;"><?=$model->course_desc_nohtml?></textarea>
                                        <textarea id="course_desc" name="LnCourse[course_desc]" style="height: 150px;"><?=$model->course_desc?></textarea>
                                        <!--textarea id="course_desc" name="LnCourse[course_desc]" style="visibility: hidden;"><//? =Html::decode($model->course_desc)?></textarea-->
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
                                        <div class="pic-display" style="<?= $model->theme_url ? '' : 'display: none;' ?> text-align: center;border: 1px dotted #ccc">
                                            <?= $model->theme_url ? '<img src="'.$model->theme_url.'" style="max-width:100%;"/>' : ''?>
                                        </div>
                                        <div class="text-info"></div>
                                    </td>
                                </tr>
                                <tr style="display: none;">
                                    <td><?=Yii::t('common', 'course_level')?></td>
                                    <td>
                                        <?=$form->field($model, 'course_level')->dropDownList($dictionary_level_list)->label(false)?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'category_id')?></td>
                                    <td>
                                        <?= $form->field($model, 'category_id')->hiddenInput()->label(false)?>
                                        <div id="jsTree"></div>
                                    </td>
                                </tr>
                                <tr style="display: none;">
                                    <td><?=Yii::t('common', 'course_max_attempt')?></td>
                                    <td>
                                        <input type="text" name="LnCourse[max_attempt]" id="course_max_attempt" style="width:100%;" onkeyup="this.value=this.value.replace(/\D+/,'');" onblur="this.value=this.value.replace(/\D+/,'');" value="<?=$model->max_attempt?>" placeholder="<?=Yii::t('common', 'course_max_attempt_tips')?>" />
                                        <div class="clearfix"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'course_period')?></td>
                                    <td>
                                        <input type="text" name="LnCourse[course_period]" id="course_period" style="width:90%;" onkeyup="replaceToFloat(this,1);" onblur="replaceToFloat(this,1);" value="<?=$model->course_period?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'course_period')]) ?>" />
                                        <span></span>
                                        <div class="clearfix"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'course_period_unit')?></td>
                                    <td>                                                            
                                        <?=$form->field($model, 'course_period_unit')->dropDownList($course_period_unit_list,[])->label(false)?>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'course_default_credit')?></td>
                                    <td>
                                        <input type="text" name="LnCourse[default_credit]" id="default_credit" style="width:90%;" onkeyup="replaceToFloat(this,0);" onblur="replaceToFloat(this,0);" value="<?=$model->default_credit?>" data-mode="COMMON" data-condition="^\d+$" data-alert="<?=Yii::t('frontend', '{value}_integer',['value'=>Yii::t('common', 'course_default_credit')])?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'course_language')?></td>
                                    <td>
                                        <?=$form->field($model, 'course_language')->dropDownList($dictionary_lang_list)->label(false)?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'course_price')?></td>
                                    <td>
                                        <input type="text" style="width:90%;ime-mode:disabled;" name="LnCourse[course_price]" id="course_price" value="<?=$model->course_price?>" data-mode="COMMON" data-condition="^[0-9]*(\.[0-9]{1,2})?$" data-alert="<?=Yii::t('frontend', '{value}_2_decimal',['value'=>Yii::t('common', 'course_price')])?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'currency')?></td>
                                    <td>
                                        <?=$form->field($model, 'currency')->dropDownList($dictionary_currency_list)->label(false)?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'time_validity')?></td>
                                    <td>
                                        <input type="text" id="lncourse-start_time" name="LnCourse[start_time]" data-type="rili" readonly="readonly" value="<?=$model->start_time?>" style="width:22%; padding: 0 12px;"  data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'start_time')]) ?>"/>
                                        <span style="width:6%;"><?=Yii::t('common', 'to2')?></span>
                                        <input type="text" id="lncourse-end_time" name="LnCourse[end_time]" data-type="rili" readonly="readonly" style="width:22%; padding: 0 12px;" value="<?=$model->end_time?>"/>
                                        <a href="javascript:;" class="btn glyphicon glyphicon-remove resetBtn" title="<?= Yii::t('frontend', 'reset') ?><?=Yii::t('frontend','time')?>" id="clear_end_time" onclick="$('#lncourse-end_time').val('');return false;"></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'relate_{value}', ['value'=>Yii::t('common','domain')])?></td>
                                    <td>
                                        <?php
                                        foreach ($domain as $key=>$val){
                                        ?>
                                            <label><input type="checkbox" name="domain_id[]" value="<?=$val->kid?>" <?php echo in_array($val->kid, $domain_id) ? 'checked' : ''; ?> style="height: auto; width: auto;"> <?=$val->domain_name?></label>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'display_name')?></td>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="LnCourse[is_display_pc]" value="1" style="height: auto; width: auto;" <?=$model->is_display_pc==\common\models\learning\LnCourse::DISPLAY_PC_YES ? 'checked' : ''?>> <?=Yii::t('common', 'position_pc')?>
                                        </label>
                                        <label>
                                            <input type="checkbox" name="LnCourse[is_display_mobile]" value="1" style="height: auto; width: auto;" <?=$model->is_display_mobile==\common\models\learning\LnCourse::DISPLAY_MOBILE_YES ? 'checked' : ''?>> <?=Yii::t('common', 'position_mobile')?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('frontend', 'audience')?></td>
                                    <td>
                                        <input type="text" class="popInput" id="audience" style="width:100%; margin-right:2%;" placeholder="<?= Yii::t('frontend', 'can_choose_more_{value}',['value'=> Yii::t('frontend', 'audience') ]) ?>" data-url="<?=Url::to(['/common/get-audience', 'companyId' => $model->company_id])?>" data-option="1" data-mult="1" autocomplete="off" />
                                        <div id="audienceDiv"></div>
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
                                    <td><?=Yii::t('common', 'serial')?></td>
                                    <td>
                                        <input type="text" class="popInput" id="certification" style="width:100%; margin-right:2%;" onclick="this.value=this.value.replace(/\s+/g,'');" placeholder="<?= Yii::t('frontend', 'can_choose_one_{value}',['value'=> Yii::t('common', 'serial') ]) ?>" data-url="<?=Url::toRoute(['/common/get-certification','course_type'=> '1', 'format'=>'new'])?>" autocomplete="off"  />
                                        <div id="certificationDiv"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'annony_view')?></td>
                                    <td>
                                        <label style="margin-right:20px">
                                            <input type="radio" name="LnCourse[is_annony_view]" value="1" style="width: auto; height: auto;" <?=$model->is_annony_view==LnCourse::IS_ANNONY_VIEW_YES ? 'checked' : ''?>> <?=Yii::t('common', 'yes')?>
                                        </label>
                                        <label>
                                            <input type="radio" name="LnCourse[is_annony_view]" value="0" style="width: auto; height: auto;" <?=(empty($model->is_annony_view) || $model->is_annony_view==LnCourse::IS_ANNONY_VIEW_NO) ? 'checked' : ''?>> <?=Yii::t('common', 'no')?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?=Yii::t('common', 'course_project')?></td>
                                    <td>
                                        <label style="margin-right:20px">
                                            <input type="radio" name="LnCourse[is_course_project]" value="1" style="width: auto; height: auto;" <?=$model->is_course_project==LnCourse::IS_ANNONY_VIEW_YES ? 'checked' : ''?>> <?=Yii::t('common', 'yes')?>
                                        </label>
                                        <label>
                                            <input type="radio" name="LnCourse[is_course_project]" value="0" style="width: auto; height: auto;" <?=(empty($model->is_course_project) || $model->is_course_project==LnCourse::IS_ANNONY_VIEW_NO) ? 'checked' : ''?>> <?=Yii::t('common', 'no')?>
                                        </label>
                                    </td>
                                </tr>
                                <?php 
                                if (empty($dictionary_approval_list)){
                                ?>
                                <tr><td colspan="2"><input type="hidden" name="LnCourse[approval_rule]" value="<?=LnCourse::COURSE_APPROVAL_DEFAULT?>" /></td></tr>
                                <?php
                                }else{
                                ?>
                                <tr>
                                    <td><?=Yii::t('common', 'course').Yii::t('common', 'approval')?></td>
                                    <td>
                                        <?=$form->field($model, 'approval_rule')->dropDownList($dictionary_approval_list,['style'=>'width:100%'])->label(false)?>
                                    </td>
                                </tr>
                                <?php
                                }
                                ?>
                            </table>
                        </div>
                        <hr/>
                        <div class="form-group">
                            <?= Html::submitButton(Yii::t('common', 'next'), ['id'=>'saveCourse','class' => 'btn btn-success pull-right' ]) ?>
                        </div>
                        <input type="hidden" name="course_time" value="<?=$course_time?>"/>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<script>
    var validation = app.creatFormValidation($("#edit-form"));
    <?php
if (!empty($tag)){
?>
    window.common_face_tags = app.queryList("#tag", '<?=$tag?>');
    <?php
    }else{
    ?>
    window.common_face_tags = app.queryList("#tag");
    <?php
    }
    if (!empty($certification)){
    ?>
    window.common_face_certification = app.queryList("#certification", '<?=$certification?>');
    <?php
    }else{
    ?>
    window.common_face_certification = app.queryList("#certification");
    <?php
    }
    if (!empty($audience)){
    ?>
    window.common_audience = app.queryList("#audience", '<?=$audience?>');
    <?php
    }else{
    ?>
    window.common_audience = app.queryList("#audience");
    <?php
    }
    ?>
    jQuery(document).ready(function () {
        loadTree();
        $("#saveCourse").click(function() {
            var course_name = $("#course_name").val().replace(/(^\s*)|(\s*$)/g,'');
            if (course_name == ""){
                validation.showAlert($("#course_name"), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'course_name')]) ?>");
                return false;
            }
            if (app.stringLength(course_name) > 150){
                validation.showAlert($("#course_name"), "<?= Yii::t('frontend', '{value}_limit_50_word',['value'=>Yii::t('common', 'course_name')]) ?>");
                return false;
            }
            var xss_course_name = filterXSS(course_name);
            if (course_name != xss_course_name){
                $("#course_name").focus();
                validation.showAlert($("#course_name"), "<?=Yii::t('frontend', 'input_course_name_error')?>");
                return false;
            }
            var re = 0;
            $.ajax({
                url: '<?=Url::toRoute(['resource/course/check-course-name','id'=>$model->kid, 'companyId' => $model->company_id])?>',
                data: {course_name: course_name},
                dataType: 'json',
                type: 'GET',
                async: false,
                success: function(response){
                    if (response.result == 'failure'){
                        re = 1;
                        validation.showAlert($("#course_name"), "<?=Yii::t('frontend', 'course_name_isset')?>");
                    }
                },
                error: function(){}
            });
            if (re > 0){
                return false;
            }else{
                validation.hideAlert($("#course_name"));
            }
            var select_category = $("#jsTree_tree_selected_result").val().split(',');
            if (select_category.length > 1){
                app.showMsg("<?= Yii::t('frontend', 'can_choose_one_{value}',['value'=> Yii::t('common', 'category_id') ]) ?>");
                return false;
            }
            var cat_id=$("#jsTree_tree_selected_result").val();
            if (typeof cat_id == 'undefined'){
                app.showMsg('<?= Yii::t('frontend', 'loading') ?>');
                return false;
            }
            cat_id = eval(cat_id);//转换成数组
            if (cat_id.length > 1 || cat_id[0] == -1){
                app.showMsg("<?=Yii::t('common', 'select_{value}', ['value' => Yii::t('common', 'category_id')])?>");
                return false;
            }
            $("#lncourse-category_id").val(cat_id);

            if ($("#course_period").val()==""){
                validation.showAlert($("#course_period"), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'class_hour')]) ?>");
                return false;
            }

            var default_credit = $("#default_credit").val().replace(/(^\s*)|(\s*$)/g,'');
            if (!/^\d+$/g.test(default_credit)){
                validation.showAlert($("#default_credit"));
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
            var domainSelected = $("input[name='domain_id[]']:checked").length;//
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
            $("#audienceDiv").empty();
            var audience_json = common_audience.get();
            if (typeof audience_json != 'undefined'){
                var audience_length = audience_json.length;
                if (audience_length > 0){
                    for (var m = 0; m < audience_length; m++){
                        $("#audienceDiv").append('<input type="hidden" name="audience_id[]" value="'+audience_json[m]['kid']+'" />');
                    }
                }
            }
            $("#tagDiv").empty();
            var tags = [];
            var tag_json = common_face_tags.get();
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

            $("#certificationDiv").empty();
            var certification_json = common_face_certification.get();
            if (typeof certification_json != 'undefined' && typeof certification_json['kid'] != 'undefined'){
                $("#certificationDiv").append('<input type="hidden" name="certification_id" value="'+certification_json['kid']+'" />');
            }
        });
    });

    var select_node = '<?=$tree_node_id?>';
    function jsTreeBack(target, data, textStatus){
        $("#" + target).html(data);
        if (typeof select_node != 'undefined' && select_node != ""){
            var t = setInterval(function() {
                var selectNode = $('#'+select_node);
                if (selectNode.length > 0){
                    $("#jsTree_tree li.jstree-node").attr('aria-selected', 'false');
                    $("#jsTree_tree a.jstree-anchor").removeClass('jstree-clicked');
                    $("#" + select_node).attr('aria-selected', 'true');
                    $("#" + select_node + '_anchor').addClass('jstree-clicked');
                    $("#jsTree_tree_changed_result").val('["' + select_node + '"]');
                    $("#jsTree_tree_selected_result").val('["' + select_node + '"]');
                    $("#jsTree_tree").attr('aria-activedescendant', select_node);
                    clearInterval(t);
                }
            },500);
        }
    }
    function TreeCallback(){

    }
    function loadTree(){
        var ajaxUrl = "<?=Url::toRoute([
        'tree-node/tree',
        'TreeType'=>'course-category',
        'ContentName'=>'tree-node',
        'ListRoute'=> $ListRoute,
        'OpenAllNode' => 'True'
        ])?>";
        ajaxGet(ajaxUrl, "jsTree", jsTreeBack);
    }
    var ajaxUploadUrl = "<?=Url::toRoute(['/common/upload'])?>";
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
            allowImageRemote: false,
            autoHeightMode: true,
            width:'100%',
            items: [
                'justifyleft', 'justifycenter', 'justifyright',
                'formatblock', 'fontsize', 'forecolor', 'hilitecolor', 'bold',
                'italic', 'underline', 'image', 'link', 'unlink'
            ],
            afterCreate: function () {
                this.sync();
                this.loadPlugin('autoheight');
            },
            afterBlur: function () {
                this.sync();
            }
        });
    });

</script>