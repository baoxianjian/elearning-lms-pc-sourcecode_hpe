<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/29
 * Time: 16:57
 */
use yii\helpers\Url;
use yii\helpers\Html;
use components\widgets\TBreadcrumbs;
use components\widgets\TUploadifive;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

$componentName = str_replace('resource/','',$this->context->id);
$this->pageTitle = Yii::t('common','upload_courseware');
$this->params['breadcrumbs'][] = ['label' => Yii::t('common','resource_management'), 'url' => ['/resource/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','courseware_management'),'url'=>['/resource/courseware/manage']];
$this->params['breadcrumbs'][] = $this->pageTitle;
$this->params['breadcrumbs'][] = '';
?>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<style>
    .uploadCourse input, .uploadCourse span{float:none !important;}
    /*select,input[type='text']{width: 100% !important}*/
    .form-control{float: none}
    #queue {
        border: 1px solid #E5E5E5;
        min-height: 100px;
        margin-bottom: 10px;
        padding: 0 3px 3px;
        width:100%;
    }
    .uploadFile .uploadifive-button {
        display: inline-block;
        overflow: hidden;
        position: relative;
        color: #fff;
        background: #00993a;
        margin: 0 20px 0 0;
        border: 1px solid transparent;
        border-radius: 4px;
        font-size: 12px;
        line-height: 18px;
        float: left;
        height: 31px;
    }
    .uploadFile .uploadifive-button:hover {
        background-color: #00993a;
        background-position: center bottom;
        background-image: none;
    }
    .uploadFileTable button {margin-left: 5px;}
    input.error {border: 1px solid #a94442!important;}
    label.error {color: #a94442;}

    #uploadifive-uploadScorm{
        height: 35px !important;
        line-height: 35px !important;
    }
    #queue_list{
        border-color: #ccc !important;
    }
    .glyphicon-remove{
        top: 3px;
    }
    .btn-danger{
        padding: 2px 8px;
    }
</style>
<script type="text/javascript">
    GetLength = function(str)
    {
        var realLength = 0;
        for (var i = 0; i < str.length; i++)
        {
            charCode = str.charCodeAt(i);
            if (charCode >= 0 && charCode <= 128)
                realLength += 1;
            else
                realLength += 2;
        }
        return realLength;
    };
    /*增加文件列表判断是否存在*/
    function checkFrm(){
        var file_len = $("input[name='file_id[]']").length;
        if (file_len == 0){
            app.showMsg("<?=Yii::t('common', 'select_{value}',['value'=>Yii::t('common', 'upload_files')])?>.");
            return false;
        }
        var error = 0;
        /*判断课件名称是否为空*/
        $("input[name^=courseware_name]").each(function(){
            var courseware_name = $(this).val().replace(/(^\s*)|(\s*$)/g,'');
            var xss_courseware_name = filterXSS(courseware_name);
            if (courseware_name == ""){
                error ++;
                $(this).addClass('error').focus();
                app.showMsg('<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'courseware_name')])?>');
                return false;
            }else if (app.stringLength(courseware_name) > 150) {
                error ++;
                $(this).addClass('error').focus();
                app.showMsg('<?=Yii::t('frontend', '{value}_limit_50_word',['value'=>Yii::t('common', 'courseware_name')])?>');
                return false;
            }else if (courseware_name != xss_courseware_name){
                error ++;
                $(this).addClass('error').focus();
                app.showMsg('<?=Yii::t('frontend', '{value}_lillegal_char',['value'=>Yii::t('common', 'courseware_name')])?>');
                return false;
            }else{
                $.ajax({
                    url: '<?=Url::to(['/resource/courseware/check-courseware'])?>',
                    data: {courseware_name: courseware_name},
                    async: false,
                    dataType: 'json',
                    type: 'get',
                    success: function(e) {
                        if (e.result == 'success') {
                            /*检测通过*/
                            $(this).removeClass('error');
                        } else {
                            /*有重复名称课件*/
                            error++;
                            $("input[name^=courseware_name]").eq(index).addClass('error').focus();
                            app.showMsg('<?=Yii::t('frontend', 'courseware_name_isset')?>');
                            return false;
                        }
                    },
                    error: function(r){
                        /**/
                    }
                });
            }
        });
        if (error > 0) return false;
        /*入口地址*/
        $("input[name^=entrance_address]").each(function(){
            var entrance_address = $(this).val().replace(/(^\s*)|(\s*$)/g,'');
            var xss_entrance_address = filterXSS(entrance_address);
            if (entrance_address != xss_entrance_address){
                error ++;
                $(this).addClass('error').focus();
                app.showMsg('<?=Yii::t('frontend', '{value}_lillegal_char',['value'=>Yii::t('common', 'entrance_address')])?>');
                return false;
            }
        });
        if (error > 0) return false;
        /*判断课件完全重名*/
        var arr = $("input[name^=courseware_name]").map(function(){
            return this.value;
        }).get();
        $("tr[id^=row_").each(function(){
            var default_credit = $(this).find("input[name^=default_credit]").val();
            if (default_credit == "") {
                error ++ ;
                $(this).find("input[name^=default_credit]").addClass('error').css('background', '#ffcdcd');
                app.showMsg('<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'default_credits')])?>');
                return false;
            }else{
                $(this).find("input[name^=default_credit]").removeClass('error').css('background', '#fff');
            }
            var is_display_pc = $(this).find("input[name^=is_display_pc]").is(":checked") ? 1 : 0;
            var is_display_mobile = $(this).find("input[name^=is_display_mobile]").is(":checked") ? 1 : 0;
            if (is_display_pc == 0 && is_display_mobile == 0){
                error ++;
                $(this).find("input[name^=is_display_pc]").parent().addClass('error');
                $(this).find("input[name^=is_display_mobile]").parent().addClass('error');
                app.showMsg('<?=Yii::t('frontend', 'must_select_one_visibility')?>');
                return false;
            }else{
                $(this).find("input[name^=is_display_pc]").parent().removeClass('error');
                $(this).find("input[name^=is_display_mobile]").parent().removeClass('error');
            }
        });
        if (error > 0) {
            return false;
        }
        return true;
        //submitForm('selectComponent');
    }
    /*移出一行数据*/
    function removeRow(id, num){
        NotyConfirm('<?=Yii::t('common', 'operation_confirm')?>',  function(data){
            var file_name = $("#row_"+id+'_'+num).find('td').eq(0).text();
            $(".uploadifive-queue-item").each(function(){
               if ($(this).find('.filename').html() == file_name) {
                   $(this).remove();
               }
            });
            $("#row_"+id+'_'+num).remove();
            $("#file_"+num).remove();
            $("#file_name_"+num).remove();
            if ($("#file_list > tr").length == 1){
                $("#file_list > tr").eq(0).show();
                $("#nextButton").attr("disabled",true);
            }
        });
    }
</script>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12 col-sm-12"  id="uploadCourse">
            <div class="panel panel-default hotNews">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-cloud-upload"></i><?=Yii::t('common', 'upload_files')?>
                </div>
                <?php $form = ActiveForm::begin([
                    'id' => 'selectComponent',
                    'method' => 'post',
                    'action' => Yii::$app->urlManager->createUrl([$this->context->id.'/common']),
                    'enableAjaxValidation' => false,
                    'enableClientValidation' => true,
                ]); ?>
                <input type="hidden" name="action" value="common">
                <input type="hidden" name="domain_id" value="<?= isset($request['domain_id']) ? join(',', $request['domain_id']) : ''?>">
                <input type="hidden" name="LnCourseware[courseware_category_id]" value="<?=$model->courseware_category_id?>">
                <input type="hidden" name="LnCourseware[vendor]" value="<?=$model->vendor?>">
                <input type="hidden" name="LnCourseware[vendor_id]" value="<?=$model->vendor_id?>">
                <input type="hidden" name="LnCourseware[start_at]" value="<?=$model->start_at?>">
                <input type="hidden" name="LnCourseware[end_at]" value="<?=$model->end_at?>">
                <div class="panel-body uploadCourse">
                    <h4><?=Yii::t('frontend', 'choose_courseware_upload')?></h4>
                    <hr/>
                    <div class="uploadFile">
                        <?php
                        if (!empty($tempArr)){
                            foreach ($tempArr as $key=>$val){
                                echo '<input type="hidden" name="file_id[]" id="file_'.$key.'" value="'.$val['file_id'].'">';
                                echo '<input type="hidden" name="file_name['.$val['file_id'].']" id="file_name_'.$key.'" value="'.$val['file_name'].'">';
                            }
                        }
                        ?>
                        <div id="queue"></div>
                        <div class="des" id="queue_list" style="float: left;background-color: white; line-height: 36px; border: 1px solid #A9A9A9;"><?=Yii::t('common', 'select_{value}',['value'=>Yii::t('common','upload_files')])?>.....</div>
                        <input type="file" id="uploadScorm" />
                    </div>
                    <div class="clearfix"></div>
                    <p style="color: #ff0000;"><?=Yii::t('frontend', 'tip_for_pass_grade')?></p>
                    <div class="uploadFileTable">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td align="center" style="width:15%"><?=Yii::t('common','filename')?></td>
                                <td align="center" style="width:15%"><?=Yii::t('common','courseware_name')?></td>
                                <td align="center" style="width:15%"><?=Yii::t('common','entrance_address')?></td>
                                <td align="center" style="width:10%"><?=Yii::t('common','courseware_time')?>（<?=Yii::t('frontend', 'point')?>）</td>
                                <td align="center" style="width:10%"><?=Yii::t('common','courseware_default_credit')?></td>
                                <td align="center" style="width:15%"><?=Yii::t('common','display_name')?></td>
                                <td align="center" style="width:10%"><?=Yii::t('common','is_allow_download')?></td>
                                <td align="center" style="width:10%"><?=Yii::t('common','action')?></td>
                            </tr>
                            </thead>
                            <tbody id="file_list">
                            <tr <?php echo !empty($tempArr) ? 'style="display:none;"' : '';?>>
                                <td colspan="8"><?=Yii::t('common', 'select_{value}',['value'=>Yii::t('common','upload_files')])?></td>
                            </tr>
                            <?php
                            if (!empty($tempArr)){
                                foreach ($tempArr as $i=>$t){
                                    $is_display_pc_checked = "";
                                    if ($t['is_display_pc']){
                                        $is_display_pc_checked = 'checked="checked"';
                                    }
                                    $is_display_mobile_checked = "";
                                    if ($t['is_display_mobile']){
                                        $is_display_mobile_checked = 'checked="checked"';
                                    }
                                    $is_allow_download_yes_checked = "";
                                    $is_allow_download_no_checked = "";
                                    if ($t['is_allow_download']){
                                        $is_allow_download_yes_checked = 'checked="checked"';
                                    }else{
                                        $is_allow_download_no_checked = 'checked="checked"';
                                    }
                            ?>
                                <tr id="row_<?=$t['file_id']?>_<?=$i?>">
                                    <td title="<?=urldecode($lncomponent_title[$t['component_id']])?>"><?=$t['icon']?><?=$t['file_name']?></td>
                                    <td align="center"><input type="text" name="courseware_name[<?=$t['file_id']?>]" value="<?=$t['courseware_name']?>" style="width:250px;"></td>
                                    <td align="center"><input type="text" name="entrance_address[<?=$t['file_id']?>]" value="<?=$t['entrance_address']?>" /></td>
                                    <td align="center"><input type="text" name="courseware_time[<?=$t['file_id']?>]" value="<?=$t['courseware_time']?>" style="width:40px;" onkeyup="this.value=this.value.replace(/\D+/g,'');" onblur="this.value=this.value.replace(/\D+/g,'');" maxlength="5"><?=Yii::t('common', 'time_minute')?></td>
                                    <td align="center"><input type="text" name="default_credit[<?=$t['file_id']?>]" value="<?=$t['default_credit']?>" style="width:60px;" onkeyup="this.value=this.value.replace(/\D+/g,'');" onblur="this.value=this.value.replace(/\D+/g,'');" maxlength="5"></td>
                                    <td align="center"><label><input type="checkbox" name="is_display_pc[<?=$t['file_id']?>]" value="1" <?=$is_display_pc_checked?> /><?=Yii::t('common', 'position_pc')?></label>&nbsp;<label><input type="checkbox" name="is_display_mobile[<?=$t['file_id']?>]" value="1" <?=$is_display_mobile_checked?> /><?=Yii::t('common', 'position_mobile')?></label></td>
                                    <td align="center"><label><input type="radio" name="is_allow_download[<?=$t['file_id']?>]" value="1" <?=$is_allow_download_yes_checked?> /><?=Yii::t('common', 'yes')?></label>&nbsp;<label><input type="radio" name="is_allow_download[<?=$t['file_id']?>]" value="0" <?=$is_allow_download_no_checked?> /><?=Yii::t('common', 'no')?></label></td>
                                    <td align="center"><!--<button type="button" class="btn btn-primary" onclick="reviewWare('<?=$t['file_id']?>//',this);">预览</button>--><button type="button" class="btn btn-danger" onclick="removeRow('<?=$t['file_id']?>',<?=$i?>);"><i class="glyphicon glyphicon-remove" style="color:#fff;"></i></button></td>
                                </tr>
                            <?php
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <hr/>
                    <?= Html::button(Yii::t('common','next'), ['type'=>'submit','disabled'=>'','id'=>'nextButton','class' => 'btn btn-success pull-right', 'onclick'=>'return checkFrm();']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
<script>
var component = <?=urldecode(json_encode($lncomponent_title))?>;
</script>
<?=TUploadifive::widget([
    'name' => 'uploadScorm',
    'scriptinit' => 'var fileQueue = [];var num = '.count($tempArr).';',
    'core' => [
        'auto' => true,
        'fileID' => count($tempArr),
        'buttonText'=>Yii::t('common','select_files'),
        'uploadScript' => Yii::$app->urlManager->createUrl([$this->context->id.'/save-file','uploadBatch'=>$uploadBatch]),
        'onUploadComplete' => new JsExpression(
            "function(file, data) {
                var result = JSON.parse(data);
                if(result.result == 'Completed'){
                    var file_id = result.file_id;
                    if (file_id == null || file_id == 'null' || file_id == ''){
                        NotyWarning(\"".Yii::t('frontend','upload_file_failed').".\");
                        return false;
                    }else{
                        var file_name = result.file_name;
                        var file_icon = result.file_icon;
                        $(\"#queue_list\").html(file_name);
                        $(\"#queue\").before('<input type=\"hidden\" name=\"file_id[]\" id=\"file_'+num+'\" value=\"'+result.file_id+'\"><input type=\"hidden\" name=\"file_name['+file_id+']\" id=\"file_name_'+num+'\" value=\"'+file_name+'\">');
                        var courseware_name = result.courseware_name;
                        var entrance_address = result.entrance_address == null ? '' : result.entrance_address;
                        var courseware_time = result.default_time;
                        var default_credit = result.default_credit;
                        var is_display_pc_checked = '';
                        if (result.is_display_pc == '1'){
                            is_display_pc_checked = 'checked=\"checked\"';
                        }
                        var is_display_mobile_checked = '';
                        if (result.is_display_mobile == '1'){
                            is_display_mobile_checked = 'checked=\"checked\"';
                        }
                        var is_allow_download_yes_checked = '';
                        var is_allow_download_no_checked = '';
                        if (result.is_allow_download == '1'){
                            is_allow_download_yes_checked = 'checked=\"checked\"';
                        }else{
                            is_allow_download_no_checked = 'checked=\"checked\"';
                        }
                        var row = '<tr id=\"row_'+file_id+'_'+num+'\">';
                        row += '<td title=\"'+component[result.component_id]+'\" style=\"width:250px;\">'+file_icon+file_name+'</td>';
                        row += '<td align=\"center\"><input type=\"text\" name=\"courseware_name['+file_id+']\" value=\"'+courseware_name+'\" style=\"width:250px;\"></td>';
                        row += '<td align=\"center\"><input type=\"text\" name=\"entrance_address['+file_id+']\" value=\"'+entrance_address+'\"></td>';
                        row += '<td align=\"center\"><input type=\"text\" name=\"courseware_time['+file_id+']\" value=\"'+courseware_time+'\" onkeyup=\"this.value=this.value.replace(/\\\D+/g,\'\');\" onblur=\"this.value=this.value.replace(/\\\D+/g,\'\');\" style=\"width:40px;\" maxlength=\"5\">分钟</td>';
                        row += '<td align=\"center\"><input type=\"text\" name=\"default_credit['+file_id+']\" value=\"'+default_credit+'\" onkeyup=\"this.value=this.value.replace(/\\\D+/g,\'\');\" onblur=\"this.value=this.value.replace(/\\\D+/g,\'\');\" style=\"width:60px;\" maxlength=\"5\"></td>';
                        row += '<td align=\"center\"><label><input type=\"checkbox\" name=\"is_display_pc['+file_id+']\" value=\"1\" '+is_display_pc_checked+' />电脑端</label>&nbsp;<label><input type=\"checkbox\" name=\"is_display_mobile['+file_id+']\" value=\"1\" '+is_display_mobile_checked+' />移动端</label></td>';
                        row += '<td align=\"center\"><label><input type=\"radio\" name=\"is_allow_download['+file_id+']\" value=\"1\" '+is_allow_download_yes_checked+' />".Yii::t('frontend', 'yes')."</label>&nbsp;<label><input type=\"radio\" name=\"is_allow_download['+file_id+']\" value=\"0\" '+is_allow_download_no_checked+' />".Yii::t('frontend', 'no')."</label></td>';
                        row += '<td align=\"center\"><!--<button type=\"button\" class=\"btn btn-primary\" onclick=\"reviewWare(\''+file_id+'\',this);\">".Yii::t('common', 'preview_button')."</button>--><button type=\"button\" class=\"btn btn-danger\" onclick=\"removeRow(\''+file_id+'\', '+num+');\"><i class=\"glyphicon glyphicon-remove\" style=\"color:#fff;\"></i></button></td>';
                        row += \"</tr>\";
                        $(\"#nextButton\").removeAttr(\"disabled\");
                        $(\"#file_list > tr \").eq(0).hide();
                        $(\"#file_list\").append(row);
                    }
                    num++;
                }
            }"
        )
    ]
]);?>
<script>
    <?php
    if (!empty($tempArr)){
    echo '$("#nextButton").removeAttr("disabled")';
    }
    ?>
</script>