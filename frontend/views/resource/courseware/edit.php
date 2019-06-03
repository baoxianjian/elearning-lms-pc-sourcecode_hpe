<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use components\widgets\TDatePicker;
$ListRoute = '/../resource/courseware/empty';
?>
<?=Html::jsFile("/static/common/js/common.js")?>
<?=Html::jsFile('/components/noty/packaged/jquery.noty.packaged.min.js')?>
<style>
    .label-domain {width: 32%; font-size: 14px;}
    .label-domain input {width: auto; height: auto;}
    .field-lncourseware-courseware_category_id {margin-bottom: 0;}
    .field-lncourseware-start_at,.field-lncourseware-end_at {float: left; width: 180px; margin: 0px;}
    #lncourseware-start_at,#lncourseware-end_at {width: 100px; height: 30px; margin-right: 0px;}
    .field-lncourseware-span {float:left; width: 30px; height: 30px; text-align: center; line-height: 30px;}
    #edit-form .control-label{float:left; width: 100px;}
    #jsTree>div:nth-child(2){overflow-x:auto;}
    .-selected-list span {width: auto;}
    #edit-form .-query-list{display:inline-block;width:70%}
</style>
<script>
    $(document).ready(function(){
        loadTree();
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
        var ajaxUrl = "<?=Url::toRoute(['tree-node/tree','TreeType'=>'courseware-category','ContentName'=>'tree-node', 'ListRoute'=> $ListRoute, 'OpenAllNode' => 'True'])?>";
        ajaxGet(ajaxUrl, "jsTree", jsTreeBack);
    }

    function checkFrm(){
        var courseware_name = $("#courseware_name").val().replace(/\s+/g,'');
        if (courseware_name == "") {
            app.showMsg('<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','course_name')])?>');
            return false;
        }
        if (app.stringLength(courseware_name) > 150) {
            app.showMsg('<?=Yii::t('frontend', '{value}_limit_50_word',['value'=>Yii::t('common', 'courseware_name')])?>');
            return false;
        }
        var error = 0;
        /*课件名称检测*/
        $.ajax({
            url: '<?=Url::to(['/resource/courseware/check-courseware'])?>',
            data: {courseware_name: courseware_name, id: '<?=$model->kid?>'},
            async: false,
            dataType: 'json',
            type: 'get',
            success: function(e) {
                if (e.result == 'success') {
                    /*检测通过*/
                } else {
                    /*有重复名称课件*/
                    error ++;
                    app.showMsg('<?=Yii::t('frontend', 'courseware_name_isset')?>');
                    return false;
                }
            },
            error: function(r){
                /**/
            }
        });
        if (error > 0) return false;
        if ($("#default_credit").val() == "") {
            app.showMsg('<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common','default_credits')])?>');
            return false;
        }
        var domain_id = $("input[name='domain_id[]']:checked").length;
        if (domain_id == 0){
            app.showMsg('<?=Yii::t('frontend', 'choose_courseware_domain')?>');
            return false;
        }
        var select_category = $("#jsTree_tree_selected_result").val().split(',');
        if (select_category.length > 1){
            app.showMsg('<?=Yii::t('frontend', '{value}_only_one',[''=>Yii::t('common', 'courseware_category')])?>');
            return false;
        }
        var cat_id=$("#jsTree_tree_selected_result").val();
        if (typeof cat_id == 'undefined'){
            app.showMsg('<?= Yii::t('frontend', 'loading') ?>');
            return false;
        }
        cat_id = eval(cat_id);//转换成数组
        if (cat_id.length > 1 || cat_id[0] == -1){
            app.showMsg('<?=Yii::t('common', 'select_{value}',['value'=>Yii::t('common','courseware_category')])?>');
            return false;
        }
        $("#lncourseware-courseware_category_id").val(cat_id);
        $("#supplierDiv").empty();
        var supplier_json = common_supplier.get();
        if (typeof supplier_json != 'undefined' && typeof supplier_json['kid'] != 'undefined'){
            var supplier_title = supplier_json['title'].replace(/(\(.*?\))/g, '');
            $("#supplierDiv").append('<input type="hidden" name="LnCourseware[vendor]" value="'+supplier_title+'" /><input type="hidden" name="LnCourseware[vendor_id]" value="'+supplier_json['kid']+'" />');
        }else{
            if ($("#supplier").val() != ""){
                app.showMsg("<?=Yii::t('frontend', 'input_supplie')?>");
                return false;
            }
            $("#supplierDiv").append('<input type="hidden" name="LnCourseware[vendor]" value="" /><input type="hidden" name="LnCourseware[vendor_id]" value="" />');
        }
        /*判断可见性必须选择一个*/
        var is_display_pc = $("input[name='is_display_pc']").is(":checked") ? 1 : 0;
        var is_display_mobile = $("input[name='is_display_mobile']").is(":checked") ? 1 : 0;
        if (is_display_pc == 0 && is_display_mobile == 0){
            app.showMsg('<?=Yii::t('frontend', 'must_select_one_visibility')?>');
            return false;
        }
        var results = null;
        $.ajax({
           url: $("#edit-form").attr('action'),
            data: $("#edit-form").serialize(),
            type: 'post',
            dataType: 'json',
            async: false,
            success: function(data){
                if (data.result == 'success'){
                    results = data.result;
                    $("#edit-form").html('');
                    window.parent.loadTree();
                }else if (data.result == 'relpy'){
                    app.showMsg('<?=Yii::t('frontend', 'same_courseware_name')?>');
                    results = data.result;
                    return false;
                }else{
                    results = data.result;
                }
            },
            error: function(e){
                /**/
            }
        });
        return results;
    }
</script>
<div class="courseInfo">
    <div role="tabpanel" class="tab-pane active" id="teacher_info">
        <div class=" panel-default scoreList">
            <div class="panel-body">
                <div class="infoBlock">
<?php $form = ActiveForm::begin([
    'id'=>'edit-form',
    'method' => 'post',
]); ?>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="form-group form-group-sm">
                <label class="col-sm-3 control-label"><?=Yii::t('common', 'courseware_code')?></label>
                <div class="col-sm-9">
                    <?=$model->courseware_code?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="form-group form-group-sm">
                <label class="col-sm-3 control-label"><?=Yii::t('common', 'filename')?></label>
                <div class="col-sm-9">
                    <?=$fileMod->file_title?>
                    <?php
                    if ($model->is_allow_download == \common\models\learning\LnCourse::IS_ALLOW_OVER_YES) {
                    ?>
                    <?= $model->getFileLink(Yii::t('common', 'down_originfile')) ?>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="form-group form-group-sm">
                <label class="col-sm-3 control-label"><?=Yii::t('common', 'courseware_name')?></label>
                <div class="col-sm-9">
                    <input type="text"t class="form-control pull-left" id="courseware_name" name="LnCourseware[courseware_name]" value="<?=$model->courseware_name?>" style="width: 70%;">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="form-group form-group-sm">
                <label class="col-sm-3 control-label"><?=Yii::t('common', 'courseware_type')?></label>
                <div class="col-sm-9">
                    <?=$component->title?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="form-group form-group-sm">
                <label class="col-sm-3 control-label"><?=Yii::t('common','courseware_time')?></label>
                <div class="col-sm-9">
                    <input type="text" style="width: 50%;" class="form-control pull-left" name="LnCourseware[courseware_time]" onkeyup="this.value=this.value.replace(/\D+/g,'');" onblur="this.value=this.value.replace(/\D+/g,'');" value="<?=$model->courseware_time?>"><?=Yii::t('frontend', 'time_minute')?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="form-group form-group-sm">
                <label class="col-sm-3 control-label"><?=Yii::t('common','courseware_default_credit')?></label>
                <div class="col-sm-9">
                    <input type="text" id="default_credit" class="form-control" width="100px" style="width:100px" name="LnCourseware[default_credit]" onkeyup="this.value=this.value.replace(/\D+/g,'');" onblur="this.value=this.value.replace(/\D+/g,'');" value="<?=$model->default_credit?>">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="form-group form-group-sm">
                <label class="col-sm-3 control-label"><?=Yii::t('common', 'domain_name')?></label>
                <div class="col-sm-9">
                    <?php
                    foreach ($domain as $key=>$val){
                    ?>
                    <label class="label-domain"><input type="checkbox" name="domain_id[]" value="<?=$val->kid?>" <?php echo in_array($val->kid,$resource) ? 'checked':'';?>>&nbsp;<?=$val->domain_name?></label>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="form-group form-group-sm">
                <label class="col-sm-3 control-label"><?=Yii::t('common', 'courseware_category')?></label>
                <div class="col-sm-9">
                    <?= $form->field($model, 'courseware_category_id')->hiddenInput()->label(false)?>
                    <div id="jsTree"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="form-group form-group-sm">
                <label class="col-sm-3 control-label"><?=Yii::t('common', 'time_validity')?></label>
                <div class="col-sm-9">
                    <input type="text" class="form-control pull-left" id="lncourseware-start_at" name="LnCourseware[start_at]" value="<?=$model->start_at?>" data-type="rili" readonly style="width:25%;right:1%;">
                    <span class="pull-left" style="display: inline-block; margin-right: 1%!important;">&nbsp;<?=Yii::t('common', 'to2')?>&nbsp;</span>
                    <input type="text" class="form-control pull-left" readonly id="lncourseware-end_at" name="LnCourseware[end_at]" value="<?=$model->end_at?>" data-type="rili" style="width:25%; margin-right:6%;">
                    <a href="javascript:;" class="btn glyphicon glyphicon-remove resetBtn" title="<?=Yii::t('common', 'reset')?><?=Yii::t('common', 'time')?>" id="clear_end_time" onclick="$('#lncourseware-end_at').val('');return false;" style="left:-67px;top:-2px;"></a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="form-group form-group-sm">
                <label class="col-sm-3 control-label"><?=Yii::t('common','supplier')?></label>
                <div class="col-sm-9">
                    <input type="text" id="supplier" class="form-control" style="width: 100%;" onclick="this.value=this.value.replace(/\s+/g,'');" placeholder="<?=Yii::t('frontend', 'name_and_code')?>" data-url="<?=Url::toRoute(['/resource/courseware/get-vendor'])?>" autocomplete="off">
                    <div id="supplierDiv"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="form-group form-group-sm">
                <label class="col-sm-3 control-label"><?=Yii::t('common','display_name')?></label>
                <div class="col-sm-9">
                    <label>
                        <input type="checkbox" name="is_display_pc" value="1" <?=$model->is_display_pc ? 'checked' : ''?>>&nbsp;<?=Yii::t('common', 'position_pc')?>
                    </label>
                        &nbsp;&nbsp;
                    <label>
                        <input type="checkbox" name="is_display_mobile" value="1" <?=$model->is_display_mobile ? 'checked' : ''?>>&nbsp;<?=Yii::t('common', 'position_mobile')?>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="form-group form-group-sm">
                <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'download')?></label>
                <div class="col-sm-9">
                    <label><input type="checkbox" name="is_allow_download" value="1" <?=$model->is_allow_download == '1' ? 'checked' : ''?> /> <?=Yii::t('frontend', 'yes')?></label>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
<?php
if (!empty($supplier)){
?>
window.common_supplier = app.queryList("#supplier", '<?=$supplier?>');
<?php
}else{
?>
window.common_supplier = app.queryList("#supplier");
<?php
}
?>
</script>