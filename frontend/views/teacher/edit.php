<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use components\widgets\TBreadcrumbs;
use components\widgets\TModal;
use common\models\learning\LnCourse;

$this->pageTitle = $model->course_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('frontend','teacher_home'), 'url' => ['teacher/index']];
$this->params['breadcrumbs'][] = Yii::t('frontend', 'lecturer_course_maintenance');
$this->params['breadcrumbs'][] = $this->pageTitle;
?>
<style>
    #addModal .componentSelected { background: #ffc;}
    #addModal .componentSelected a {color: red !important;}
    .courseInfoInput table{width: 100%;}
    a.blockClose {
        position: inherit;
        float: right;
        font-size: 1.6rem;
    }
    a.glyphicon-chevron-up {
        text-decoration: none;
        float: right;
        font-size: 1.6rem;
        margin-right: 6px;
    }
    a.glyphicon-chevron-down {
        text-decoration: none;
        float: right;
        font-size: 1.6rem;
        margin-right: 6px;
    }
</style>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<?= html::jsFile('/static/frontend/js/jquery.ui.touch-punch.min.js')?>
<div class="container">
    <div class="row" style="position: relative;">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-8" style="min-height: 800px">
            <div class="panel panel-default hotNews">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-random"></i> <?= Yii::t('frontend', 'course_resouce') ?>
                </div>
                <div class="panel-body courseInfoInput">
                    <?php $form = ActiveForm::begin([
                        'id' => 'courseForm',
                    ]); ?>
                    <div class="guideBlock resourceBlock">
                        <h5><?= Yii::t('frontend', 'drag_component') ?></h5>
                        <input type="hidden" name="action" value="content"/>
                        <ul class="blockArray" id="modList">
                            <?php
                            $i = 0;
                            if(!empty($modules)){
                                foreach($modules as $module){
                                    ?>
                                    <li data-id="<?=$i+1?>" class="courseModule">
                                        <div style="display: none">
                                            <input type="hidden" name="resource[<?=$module['mod_num']?>][kid]" class="kid" value="<?=$module['kid']?>">
                                            <input type="hidden" name="resource[<?=$module['mod_num']?>][mod_num]" class="mod_num" value="<?=$module['mod_num']?>"/>
                                        </div>
                                        <h3><?= Yii::t('frontend', 'mod') ?><font class="sequence_number"><?=$module['mod_num']?></font>
                                            <a href="javascript:;" class="glyphicon glyphicon-remove blockClose" onclick="ToggleCourseMod(1,this)" <?php if ($i == 0) { ?> style="display:none" <?php } ?> role="button"></a>
                                            <a href="javascript:;" class="glyphicon glyphicon-chevron--up" onclick="ToggleCourseUp(this)" role="button"></a>
                                            <a href="javascript:;" class="glyphicon glyphicon-chevron--down" onclick="ToggleCourseDown(this)" role="button"></a>
                                        </h3>

                                        <table class="table">
                                            <tr>
                                                <td><?= Yii::t('frontend', 'mod_title') ?></td>
                                                <td class="name">
                                                    <input type="text" name="resource[<?=$module['mod_num']?>][mod_name]" class="mod_name" value="<?=$module['mod_name']?>" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','mod_title')]) ?>" />
                                            </tr>
                                            <tr>
                                                <td><?=Yii::t('frontend', 'module_description')?></td>
                                                <td class="desc">
                                                    <textarea class="mod_desc" rows="3" name="resource[<?=$module['mod_num']?>][mod_desc]"><?=$module['mod_desc']?></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="100px"><?= Yii::t('frontend', 'mod_res') ?></td>
                                                <td class="area">
                                                    <ul class="componentArea ulEditContent" id="mod_<?=$module['kid']?>"><?=$module['resource']?></ul>
                                                </td>
                                            </tr>
                                        </table>
                                    </li>
                                    <?
                                    $i ++;
                                }
                            }
                            ?>
                        </ul>
                        <input type="hidden" name="preview" value="" />
                        <input type="hidden" name="LnCourse[status]" id="lncourse-status" value="" />

                        <div class="centerBtnArea col-md-12 col-sm-12">
                            <a href="javascript:ToggleCourseMod(0,this);" class="btn btn-default btn-sm centerBtn additionBtn"><?= Yii::t('frontend', 'mod_add') ?></a>
                            <!-- <a href="###" class="btn" style="position:absolute;right:80px;top:5px"  onclick="loadModalFormData('addModal','<?=Yii::$app->urlManager->createUrl(['resource/component/config-list'])?>',this,'other','other','0');">完成配置</a>
                            <a href="###" class="btn" style="position:absolute;right:0;top:5px"  onclick="loadModalFormData('addModal','<?=Yii::$app->urlManager->createUrl(['resource/component/final-score'])?>',this,'other','other','0');">最终成绩</a> -->
                        </div>
                    </div>
                    <div class="pull-right">
                        <?= Html::button( Yii::t('frontend','view_course'), [
                            'class' => 'btn btn-default',
                            'name' => 'temp-button',
                            'onclick'=>'saveCourse(1,1)',
                        ]) ?>
                        <?= Html::button(Yii::t('frontend','save_course'), [
                            'class' => 'btn btn-success',
                            'name' => 'submit-button',
                            'onclick'=>'saveCourse(1,0)',
                        ]) ?>
                    </div>
                    <div id="configlist" class="hide">
                        <?php
                        if(!empty($modules)){
                            $newmodules = $modules;
                            array_values($newmodules);
                            foreach ($newmodules as $k => $v) {
                                if(isset($v['courseitems'])){
                                    foreach ($v['courseitems'] as $key => $value) {
                                        $array_value = array(
                                            'kid' => $value['itemId'],
                                            'iscore' => $value['modRes']->is_record_score,
                                            'isfinish' => $value['modRes']->direct_complete_course,
                                            'score' => $value['modRes']->pass_grade,
                                            'title' => $value['itemName'],
                                            'componet' => $value['componentName'],
                                        );
                                      //  if($value['modRes']->direct_complete_course){
                                            ?>
                                            <input data-componet="<?=$value['componentName']?>" id="con_<?=$k?>_<?=$value['itemId']?>" data-kid="<?=$value['itemId']?>" data-title="<?=$value['itemName']?>" data-iscore="<?=$value['modRes']->is_record_score?>" data-score="<?=$value['modRes']->pass_grade?>" data-isfinish="<?=$value['modRes']->direct_complete_course?>" data-res-time="<?=$value['modRes']->res_time?>" data-name="config" name="resource[<?=$k?>][config][<?=$value['modRes']->kid?>]" value='<?=json_encode($array_value)?>'>
                                            <?php
                                       // }
                                    }
                                }
                            }
                        }
                        ?>
                    </div>
                    <div id="finalscorelist" class="hide">
                        <?php
                        if(!empty($modules)){
                            foreach ($newmodules as $k => $v) {
                                if(isset($v['courseitems'])){
                                    foreach ($v['courseitems'] as $key => $value) {
                                        if(!empty($value['modRes']->score_scale)){
                                            $array_json = array(
                                                'score' => $value['modRes']->score_scale,
                                                'id' => $value['itemId'],
                                                'modnum' => $k,
                                                'comrul' => $value['modRes']->complete_rule,
                                            );
                                            ?>
                                            <input data-componet="<?=$value['componentName']?>" id="socl_<?=$k?>_<?=$value['itemId']?>" data-title="<?=$value['itemName']?>" value='<?=json_encode($array_json)?>' name="resource[<?=$k?>][rescore][<?=$value['itemId']?>]" data-modnum="<?=$k?>" data-id="<?=$value['itemId']?>" data-score="<?=$value['modRes']->score_scale?>">
                                            <?php
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 resourcePanel miniestTools" id="componentPanel"></div>
    </div>
</div>
<!-- 选择课件资源组建div -->
<div id="addModal" class="ui modal"></div>
<li id="courseModule" data-id="" style="display: none;">
    <div style="display: none">
        <input type="hidden" value="" class="kid" name="resource[_numbers_][kid]">
        <input type="hidden" value="_numbers_" name="resource[_numbers_][mod_num]" class="mod_num">
    </div>
    <h3><?= Yii::t('frontend', 'mod') ?><font class="sequence_number">_numbers_</font>
        <a href="javascript:;" class="glyphicon glyphicon-remove blockClose" onclick="ToggleCourseMod(1,this)" role="button"></a>
        <a href="javascript:;" class="glyphicon glyphicon-chevron--up" onclick="ToggleCourseUp(this)" role="button"></a>
        <a href="javascript:;" class="glyphicon glyphicon-chevron--down" onclick="ToggleCourseDown(this)" role="button"></a>
    </h3>
    <table class="table">
        <tr>
            <td><?= Yii::t('frontend', 'mod_title') ?></td>
            <td class="name">
                <input type="text" name="resource[_numbers_][mod_name]" class="mod_name" maxlength="true" data-mode="COMMON" data-condition="required" data-alert="<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','mod_title')]) ?>" />
            </td>
        </tr>
        <tr>
            <td><?=Yii::t('frontend', 'module_description')?></td>
            <td class="desc">
                <textarea rows="3" class="mod_desc" name="resource[_numbers_][mod_desc]"></textarea>
            </td>
        </tr>
        <tr>
            <td width="100px"><?= Yii::t('frontend', 'mod_res') ?></td>
            <td class="area">
                <ul class="componentArea ulEditContent"></ul>
            </td>
        </tr>
    </table>
</li>
<div id="previewModal" class="ui modal">
    <div class="content">
        <div class="modal-body-view"></div>
    </div>
</div>
<div id="configlist_tmp" style="display: none;"></div>
<div id="scorelist_tmp" style="display: none;"></div>
<script>
    var id = '<?=$model->kid?>';
    app.extend("alert");
    var validation = app.creatFormValidation($("#courseForm"));
    var domain_id = '<?=is_array($domain_id) ? join(',',$domain_id) : $domain_id?>';
    var coursewares = "<?=Yii::$app->urlManager->createUrl(['resource/coursewares'])?>";
    var activity = "<?=Yii::$app->urlManager->createUrl(['resource/activity'])?>";
    <?php
    if (!empty($is_setting_component)){
    ?>
    var isRecordComponent = <?=json_encode($is_setting_component)?>;
    <?php
    }else{
    ?>
    var isRecordComponent = [];
    <?php
    }
    ?>
    function contains(arr, str) {
        var i = arr.length;
        if (i < 1) return false;
        while (i--) {
            if (arr[i] == str) {
                return true;
            }
        }
        return false;
    }
    $(function() {
        $.get("<?=Url::toRoute(['resource/component/get-component'])?>",function(html){
            if (html){
                $("#componentPanel").html(html);
                initDroppable();
            }else{
                $("#componentPanel").html("<?= Yii::t('frontend', 'none_choosed_component') ?>");
            }
        });
        $("#modList").on("click", ".del", function() {
            var area = $(this).parents(".ulEditContent");
            var object_id = $(this).parents("li.component").find(".componentid").val();
            var mod_num = $(this).parents("li.component").find(".componentid").attr('data-modnum');
            $("input[id^=con_"+mod_num+"_"+object_id+"]").remove();
            $("input[id^=socl_"+mod_num+"_"+object_id+"]").remove();
            var componentCode = $(this).parents("li.component").attr('data-component');
            if (typeof componentCode != 'undefined' && contains(isRecordComponent, componentCode)){
                $("#finalscorelist").empty();
            }
            $(this).parent().parent().remove();
            area.find("li").each(function(){
                var component_input = $(this).find(".componentid").attr('name');
                component_input = component_input.replace(/\]\[\d+\]$/g,']['+($(this).index()+1)+']');
                $(this).find(".componentid").attr('name',component_input);
            });
        });
        $(".close").on('click', function(){
            $("#addModal").attr('data-id', '').attr('data-li', '').attr('data-type', '').attr('data-code', '').attr('data-componentid', '');
        });
        <?php
        if (empty($modules)){
        ?>
        ToggleCourseMod();
        $(".courseModule").eq(0).find(".blockClose").remove();
        <?php
        }
        ?>
    });
    var click_mod = true;
    //模块检查
    function saveCourse(status, preview){
        if (!click_mod){
            app.showMsg('<?= Yii::t('frontend', 'data_get_ready') ?>...');
            return false;
        }
        var url = '<?=Yii::$app->urlManager->createUrl(['/resource/course/teacher-save'])?>?id='+id;
        $("input[name='preview']").val(preview);
        $("#courseForm").attr('action',url);
        var temp = [];
        var error = 0;
        app.showLoadingMsg('<?=Yii::t('frontend', 'operation_is_in_progress')?>');
        if($('#modList').find('input.mod_num').length > 0) {
            $(".courseModule").each(function () {
                var kid = $(this).find('.kid').val();
                var mod_num = $(this).find('.mod_num').val();
                var mod_name = $(this).find('.mod_name').val().replace(/(^\s*)|(\s*$)/g,'');
                if (mod_name == "") {
                    error++;
                    $(this).find('.mod_name').focus();
                    validation.showAlert($(this).find('.mod_name'), "<?= Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend','mod_title')]) ?>");
                    //return false;
                }
                if (app.stringLength(mod_name) > 75) {
                    error++;
                    $(this).find('.mod_name').focus();
                    validation.showAlert($(this).find('.mod_name'), "<?= Yii::t('frontend', '{value}_limit_25_word',['value'=>Yii::t('frontend','mod_title')]) ?>");
                    //return false;
                }
                var xss_mod_name = filterXSS(mod_name);
                if (mod_name != xss_mod_name){
                    error ++;
                    $(this).find('.mod_name').focus();
                    validation.showAlert($(this).find('.mod_name'), '<?= Yii::t('frontend', '{value}_lillegal_char',['value'=>Yii::t('frontend','mod_title')]) ?>');
                    app.hideLoadingMsg();
                    return false;
                }
                var mod_desc = $(this).find('.mod_desc').val().replace(/(^\s*)|(\s*$)/g,'');
                var xss_mod_desc = filterXSS(mod_desc);
                if (mod_desc != xss_mod_desc){
                    error ++;
                    $(this).find('.mod_desc').focus();
                    app.showMsg('<?= Yii::t('frontend', '{value}_lillegal_char',['value'=>Yii::t('frontend','module_description')]) ?>');
                    app.hideLoadingMsg();
                    return false;
                }
            });
            if (error > 0) {
                app.hideLoadingMsg();
                return false;
            }
        }
        $("#lncourse-status").val(status);
        click_mod = false;
        $.ajax({
            url: url,
            data: $("#courseForm").serialize(),
            type: 'POST',
            dataType: 'json',
            async: false,
            success: function(response){
                app.hideLoadingMsg();
                click_mod = true;
                if (response.result == 'success') {
                    if (preview == 0) {
                        app.showMsg('<?= Yii::t('frontend', 'issue_sucess') ?>', 'center');
                        setTimeout(function () {
                            location.href = '<?=Yii::$app->urlManager->createUrl(['teacher/index'])?>';
                        }, 1000);
                    }else{
                        url = '<?=Yii::$app->urlManager->createUrl(['/resource/course/preview'])?>?id='+id;
                        app.get(url, function (r){
                            if(r)
                            {
                                $('#previewModal').find(".modal-body-view").html(r);
                                app.alertFull('#previewModal');
                            }
                        });
                        return false;
                    }
                } else {
                    app.showMsg('<?= Yii::t('frontend', 'course_save_failed') ?>');
                    return false;
                }
            },
            error: function(){
                app.showMsg('<?= Yii::t('frontend', 'network_anomaly') ?>');
                return false;
            }
        });
        return false;
    }
    //弹出资源选择界面
    function loadModalFormData(modalId, url, obj, type, code, window) {
        $('#'+modalId).empty().attr('class', 'ui modal');
        var data_id = $(obj).parents('.courseModule').attr('data-id');
        var date = (new Date).getTime();
        $(obj).parent().attr('data-id',date);
        if (window == "") window = 1;
        var mod_num = $(obj).parents(".courseModule").find('.mod_num').val();
        if (url.indexOf('mod_num')<0){
            if(mod_num != undefined){
                url += '&mod_num='+mod_num;
            }
        }
        if (url.indexOf('?') > 0){
            url += '&isCourseType=<?=LnCourse::COURSE_TYPE_FACETOFACE?>';
        }else{
            url += '?isCourseType=<?=LnCourse::COURSE_TYPE_FACETOFACE?>';
        }
        url += '&from=teacher';
        app.get(url, function(data){
            var r = url.match(/component_id=(.*?)&/);
            if (r !=  null) {
                $("#addModal").attr('data-id', data_id).attr('data-li', date).attr('data-code', code).attr('data-type', type).attr('data-componentid', r[1]);
            }
            $('#'+modalId).html(data);
            app.isIE && $('#first_desc').focus();
            if(window == 1){
                app.alertWide("#"+modalId);

            }else if(window == 0){
                app.alert("#"+modalId);

            }
            if (typeof $(obj).parent().attr('id') == 'undefined' || $(obj).parent().attr('id').indexOf('ware_')<0){
                $(obj).parent().attr('data-empty', date+'_empty');
            }
        });
    }

    //资源选择界面组件控制
    function ToggleComponent(obj){
        if($(obj).parent().hasClass('ulEditContent')){
            $(obj).remove();
        }else{
            if($(obj).find('.addAction').html().match(/glyphicon-plus/g)){
                $(obj).find('.addAction').html('<i class="glyphicon glyphicon-ok"></i>');
            }else{
                $(obj).find('.addAction').html('<i class="glyphicon glyphicon-plus"></i>');
            }
            $(obj).toggleClass('componentSelected');
        }
    }


    //模块前移
    function ToggleCourseUp(obj) {
        ToggleCourseSwap(obj, -1);
    }

    //模块后移
    function ToggleCourseDown(obj) {
        ToggleCourseSwap(obj, 1);
    }

    //模块移动
    function ToggleCourseSwap(obj, upDown) {
        if (!obj) return;
        var li = $(obj).parents("li.courseModule");
        if (!li) return;
        var l = null, r = null;
        if (upDown == 1) {
            l = li;
            r = li.next("li.courseModule");
        } else {
            r = li;
            l = li.prev("li.courseModule");
        }
        if (l.length === 0 || r.length === 0) {
            return;
        }

        l.remove();
        l.insertAfter(r);
        assignCourseIndex(l, r, upDown);
    }

    function assignCourseIndex(li, r, upDown) {
        $("#configlist_tmp").empty();
        $("#scorelist_tmp").empty();
        var org_num = li.find('.sequence_number').html();
        var num = r.find('.sequence_number').html();
        //var con_1 = $("#configlist").find("input[id^=con_"+org_num+"]").clone();
        var con_configlist = $("#configlist").find("input[id^=con_"+num+"]").clone();
        var con_configlist2 = $("#configlist").find("input[id^=dir_"+num+"]").clone();
        var con_scorelist = $("#finalscorelist").find("input[id^=socl_"+num+"]").clone();
        $("#configlist").find("input[id^=con_"+num+"]").remove();
        $("#configlist").find("input[id^=dir_"+num+"]").remove();
        $("#finalscorelist").find("input[id^=socl_"+num+"]").remove();
        $("#configlist_tmp").append(con_configlist);
        $("#configlist_tmp").append(con_configlist2);
        $("#scorelist_tmp").append(con_scorelist);
        $("#configlist").html($("#configlist").html().replace(new RegExp("resource\\["+org_num+"\\]", "g"), 'resource['+num+']'));
        $("#configlist").html($("#configlist").html().replace(new RegExp("con_"+org_num+"_", "g"), 'con_'+num+'_'));
        $("#configlist").html($("#configlist").html().replace(new RegExp("dir_"+org_num+"_", "g"), 'dir_'+num+'_'));

        $("#configlist_tmp").html($("#configlist_tmp").html().replace(new RegExp("resource\\["+num+"\\]", "g"), 'resource['+org_num+']'));
        $("#configlist_tmp").html($("#configlist_tmp").html().replace(new RegExp("con_"+num+"_", "g"), 'con_'+org_num+'_'));
        $("#configlist_tmp").html($("#configlist_tmp").html().replace(new RegExp("dir_"+num+"_", "g"), 'dir_'+org_num+'_'));
        $("#configlist").append($("#configlist_tmp").html());
        /*排序*/
        var configlist = $("#configlist").find("input[id^=con_]").toArray().sort(function(a, b){
            return parseInt($(a).attr('id').split('_')[1]) - parseInt($(b).attr('id').split('_')[1]);
        });
        $(configlist).appendTo('#configlist');

        //
        $("#finalscorelist").html($("#finalscorelist").html().replace(new RegExp("resource\\["+org_num+"\\]", "g"), 'resource['+num+']'));
        $("#finalscorelist").html($("#finalscorelist").html().replace(new RegExp("socl_"+org_num+"_", "g"), 'socl_'+num+'_'));

        $("#scorelist_tmp").html($("#scorelist_tmp").html().replace(new RegExp("resource\\["+num+"\\]", "g"), 'resource['+org_num+']'));
        $("#scorelist_tmp").html($("#scorelist_tmp").html().replace(new RegExp("socl_"+num+"_", "g"), 'socl_'+org_num+'_'));
        $("#finalscorelist").append($("#scorelist_tmp").html());

        li.find('.sequence_number').html(num);
        li.attr('data-id',num);
        li.find('.kid').attr('name', 'resource['+num+'][kid]');
        li.find('.mod_num').val(num).attr('name', 'resource['+num+'][mod_num]');
        li.find('.mod_name').attr('name', 'resource['+num+'][mod_name]');
        li.find('.mod_desc').attr('name', 'resource['+num+'][mod_desc]');
        li.find('.componentid').each(function(){
            $(this).attr('data-modnum', num).attr('name', $(this).attr('name').replace(/resource\[(.*?)\]/g, 'resource['+num+']'));
        });

        r.find('.sequence_number').html(org_num);
        r.attr('data-id',org_num);
        r.find('.kid').attr('name', 'resource['+org_num+'][kid]');
        r.find('.mod_num').val(org_num).attr('name', 'resource['+org_num+'][mod_num]');
        r.find('.mod_name').attr('name', 'resource['+org_num+'][mod_name]');
        r.find('.mod_desc').attr('name', 'resource['+org_num+'][mod_desc]');
        r.find('.componentid').each(function(){
            $(this).attr('data-modnum', org_num).attr('name', $(this).attr('name').replace(/resource\[(.*?)\]/g, 'resource['+org_num+']'));
        });
        $("#configlist_tmp").empty();
        $("#scorelist_tmp").empty();
        $(".courseModule").each(function(index){
            if (index == 0) {
                $(this).find('.blockClose').hide();
            } else {
                $(this).find('.blockClose').show();
            }
        });
        initDroppable();
    }

    /*增加模块*/
    function ToggleCourseMod(removeit,obj){
        if(removeit){
            var mod_num = $(obj).prev().html();
            $(obj).parents(".courseModule").remove();
            $("input[id^=con_"+mod_num+"_]").remove();
            $("input[id^=socl_"+mod_num+"_]").remove();
            $("#configlist").find("input[name^='resource["+mod_num+"][config]']").remove();
            $("#finalscorelist").find("input[name^='resource["+mod_num+"][rescore]']").remove();
            $(".courseModule").each(function(index){
                var num = index + 1;
                var org_num = parseInt($(this).find('.sequence_number').html());
                $("#configlist").html($("#configlist").html().replace(new RegExp("resource\\["+org_num+"\\]", "g"), 'resource['+num+']'));
                $("#finalscorelist").html($("#finalscorelist").html().replace(new RegExp("resource\\["+org_num+"\\]", "g"), 'resource['+num+']'));
                $("#configlist").html($("#configlist").html().replace(new RegExp("con_"+org_num+"_", "g"), 'con_'+num+'_'));
                $("#configlist").html($("#configlist").html().replace(new RegExp("dir_"+org_num+"_", "g"), 'dir_'+num+'_'));
                $("#finalscorelist").html($("#finalscorelist").html().replace(new RegExp("socl_"+org_num+"_", "g"), 'socl_'+num+'_'));
                $(this).find('.sequence_number').html(num);
                $(this).attr('data-id',num);
                $(this).find('.kid').attr('name', 'resource['+num+'][kid]');
                $(this).find('.mod_num').val(num).attr('name', 'resource['+num+'][mod_num]');
                $(this).find('.mod_name').attr('name', 'resource['+num+'][mod_name]');
                $(this).find('.mod_desc').attr('name', 'resource['+num+'][mod_desc]');
                $(this).find('.componentid').each(function(){
                    $(this).attr('data-modnum', num).attr('name', $(this).attr('name').replace(/resource\[(.*?)\]/g, 'resource['+num+']'));
                });
            });
        }else{
            var module = $("#courseModule").clone().attr('id','').addClass('courseModule').css('display','block');
            var lastNum = $(".courseModule").length+1;
            module.attr('data-id', lastNum);
            module.html(module.html().replace(/_numbers_/g, lastNum));
            $('#modList').append(module);
        }
        initDroppable();/*重新初始化加载*/
    }
    //拖动组件脚本
    function initDroppable(){
        $(".resourcePart li").draggable({
            appendTo: "body",
            helper: "clone",
            scroll: false
        });
        $(".table .ulEditContent").droppable({
            activeClass: "ulActive",
            hoverClass: "ulEdit ",
            accept: ":not(.ui-sortable-helper)",
            drop: function(event, ui) {
                var modulCate = ui.draggable;
                var type = modulCate.attr('data-type');
                var code = modulCate.attr('data-code');
                var title = modulCate.attr('title');
                var window = modulCate.attr('data-window');
                var url = null;
                if (modulCate.attr('data-uri').length > 0){
                    url = modulCate.attr('data-uri');
                }
                if (url == null){
                    app.showMsg('<?= Yii::t('frontend', 'component_improvement') ?>');
                    return false;
                }
                if ($(this).find('li[data-type="'+type+'_'+code+'"]').length > 0){
                    app.showMsg('<?= Yii::t('frontend', 'component_improvement') ?>');
                    return false;
                }
                var index = $(".table .ulEditContent").index(this) + 1;
                var modulLink = $('<a href="javascript:;"></a>').text(' '+modulCate.attr('title')).attr( 'onclick','loadModalFormData(\'addModal\',\''+url+'?component_id='+modulCate.attr('kid')+'&sequence_number='+index+'&domain_id='+domain_id+'&component_code='+code+'\',this,\''+type+'\',\''+code+'\',\''+window+'\');').prepend(modulCate.find('i').clone());
                var modulLi = $('<li data-type="'+type+'_'+code+'">').append(modulLink).append('<div class="addAction pull-right"><a href="javascript:;" class="glyphicon glyphicon-remove del" title="<?= Yii::t('common', 'delete_button') ?>"></a></div>');
                modulLi.appendTo(this);
            }
        });
        $('.ui-droppable').sortable({
            stop: function(event, ui){
                $(this).find("li").each(function(){
                    var component_input = $(this).find(".componentid").attr('name');
                    if (typeof component_input != 'undefined') {
                        component_input = component_input.replace(/\]\[\d+\]$/g, '][' + ($(this).index() + 1) + ']');
                        $(this).find(".componentid").attr('name', component_input);
                    }
                });
            }
        })
    }
    function closePreview(){
        app.hideAlert($('#previewModal'));
        $("#previewModal").find(".modal-body-view").html('');
    }
</script>