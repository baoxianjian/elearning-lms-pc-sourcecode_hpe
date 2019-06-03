<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/23
 * Time: 11:22
 */
use yii\helpers\Url;
use yii\helpers\Html;
use common\models\learning\LnExamination;
?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?=!empty($model->kid)?Yii::t('frontend', 'editor_text'):Yii::t('frontend', 'build')?><?=Yii::t('frontend', 'exam_kaoshi')?></h4>
</div>
<div class="content" style="padding:0;">
    <form class="row" name="form1" id="form1" action="<?=Url::toRoute(['/exam-manage-main/exam-add','id'=>$model->kid,'category_id'=>$category_id])?>">
        <input type="hidden" name="LnExamination[category_id]" value="<?=$category_id?>" />
        <div class="infoBlock">
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_moshi')?></label>
                        <div class="col-sm-9">
                            <div class="form-group field-courseservice-course_type">
                                <select class="form-control" name="LnExamination[examination_mode]" id="examination_mode_add" data-type="0">
                                    <option value="0" <?=$model->examination_mode==LnExamination::EXAMINATION_MODE_TEST?'selected':''?>><?=Yii::t('frontend', 'exam_ceshimoshi')?></option>
                                    <option value="1" <?=$model->examination_mode==LnExamination::EXAMINATION_MODE_EXERCISE?'selected':''?>><?=Yii::t('frontend', 'exam_lianximoshi')?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'tag_mingcheng')?></label>
                        <div class="col-sm-9">
                            <input name="LnExamination[title]" id="title" class="form-control" type="text" value="<?=$model->title?>" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('frontend', 'question_title')])?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_kaoshimiaoshu')?></label>
                        <div class="col-sm-9">
                            <textarea name="LnExamination[description]" style="min-height:50px;"><?=$model->description?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_kaoqianmiaoshu')?></label>
                        <div class="col-sm-9">
                            <textarea name="LnExamination[pre_description]" style="min-height:50px;"><?=$model->pre_description?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_kaohoumiaoshu')?></label>
                        <div class="col-sm-9">
                            <textarea name="LnExamination[after_description]" style="min-height:50px;"><?=$model->after_description?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_shijuan')?></label>
                        <div class="col-sm-9">
                            <div class="form-group field-courseservice-course_type">
                                <select name="LnExamination[examination_paper_id]" id="examination_paper_id" class="form-control pull-left" style="width:80%"></select>
                                <a class="btn btn-sm pull-left" id="paperInfo"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm">
                        <label class="col-sm-3 control-label"><?= Yii::t('common', 'investigation_range_') ?></label>
                        <div class="col-sm-9">
                            <div class="btn-group" data-toggle="buttons">
                                <label style="margin-right:68px;">
                                    <input type="radio" name="LnExamination[examination_range]" value="0" <?=$model->examination_range==LnExamination::EXAMINATION_RANGE_SELF?'checked':''?>> <?=Yii::t('frontend', 'exam_dulishiyong')?>
                                </label>
                                <label>
                                    <input type="radio" name="LnExamination[examination_range]" value="1" <?=$model->examination_range==LnExamination::EXAMINATION_RANGE_COURSE?'checked':''?>> <?=Yii::t('frontend', 'exam_kechengneibu')?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="mode_0" class="examType">
                <div class="row <?=$model->examination_range==LnExamination::EXAMINATION_RANGE_COURSE?'hidden':''?>" id="examination_time">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_ruchangshijian')?></label>
                            <div class="col-sm-9">
                                <input type="text" name="LnExamination[start_at]" class="form-control pull-left" id="start_at" placeholder="<?=Yii::t('common', 'action_start_at')?>" style="width:32%; margin-right:6%;" data-type="rili" data-full="1" value="<?=!empty($model->start_at) ? date('Y-m-d H:i:s', $model->start_at) : ''?>" data-hms="<?=!empty($model->start_at)?date('H:i:s', $model->start_at):''?>" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'action_start_at')])?>" data-delay="1" readonly />
                                <span class="pull-left" style="line-height: 2.5rem; margin-right:20px;"><?=Yii::t('common', 'to2')?></span>
                                <input type="text" name="LnExamination[end_at]" class="form-control pull-left" placeholder="<?=Yii::t('frontend', 'end_time')?>" id="end_at" style="width:32%; margin-right:6%;" data-type="rili" data-full="1" value="<?=!empty($model->end_at) ? date('Y-m-d H:i:s', $model->end_at) : ''?>"  data-hms="<?=!empty($model->end_at)?date('H:i:s', $model->end_at):''?>" readonly data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'end_time')])?>" data-delay="1"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_xianzhishichang')?></label>
                            <div class="col-sm-9">
                                <input type="text" name="LnExamination[limit_time]" class="form-control pull-left" id="limit_time" placeholder="60<?=Yii::t('frontend', 'exam_time_m')?>" style="width:32%; margin-right:6%;" value="<?=$model->limit_time?>" onkeyup="this.value=this.value.replace(/\D+/,'')" data-mode="COMMON" data-condition="^([1-9]\d*|[0]{1,1})|$" data-alert="<?=Yii::t('frontend', 'exam_xianzhishichang')?>" autocomplete="off">
                                <span id="limit_time_number" class="pull-left" style="<?=$model->limit_time > 0 ? '' : 'display: none;'?> line-height: 2.5rem;"><font ><?=$model->limit_time?></font><?=Yii::t('frontend', 'exam_fenzhonghouzidongjiaojuan')?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_daanjiexi')?></label>
                            <div class="col-sm-9">
                                <div class="btn-group" data-toggle="buttons">
                                    <label style="margin-right:68px;">
                                        <input type="radio" name="LnExamination[answer_view]" value="1" <?=$model->answer_view==LnExamination::ANSWER_VIEW_YES?'checked':''?> /> <?=Yii::t('frontend', 'exam_yunxuchakan')?>
                                    </label>
                                    <label>
                                        <input type="radio" name="LnExamination[answer_view]" value="0" <?=$model->answer_view!=LnExamination::ANSWER_VIEW_YES?'checked':''?> /> <?=Yii::t('frontend', 'exam_buyunxuchakan')?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('common', 'random_mode')?></label>
                            <div class="col-sm-9">
                                <div class="form-group">
                                    <select class="form-control" name="LnExamination[random_mode]" id="random_mode" style="width: 80%;">
                                        <option value="<?=LnExamination::RANDOM_MODE_NO?>"><?=Yii::t('common', 'no')?></option>
                                        <option value="<?=LnExamination::RANDOM_MODE_YES?>" <?=$model->random_mode==LnExamination::RANDOM_MODE_YES?'selected="selected"':''?>><?=Yii::t('common', 'yes')?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row disorder" style="<?=($model->random_mode==LnExamination::RANDOM_MODE_NO||empty($model->random_mode))?'':'display: none;'?>">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_shitipaixu')?></label>
                            <div class="col-sm-9">
                                <div class="btn-group" data-toggle="buttons">
                                    <label style="margin-right:68px;">
                                        <input type="checkbox" name="LnExamination[question_disorder]" value="1" <?=$model->question_disorder == LnExamination::QUESTION_DISORDER_YES ? 'checked' :'' ?>> <?=Yii::t('frontend', 'exam_timuluanxu')?>
                                    </label>
                                    <label>
                                        <input type="checkbox" name="LnExamination[option_disorder]" value="1" <?=$model->option_disorder == LnExamination::OPTIOIN_DISORDER_YES ? 'checked' :'' ?>> <?=Yii::t('frontend', 'exam_xuanxiangluanxu')?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row randYes" style="<?=$model->random_mode==LnExamination::RANDOM_MODE_YES?'':'display: none;'?>">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_suijixianshi')?></label>
                            <div class="col-sm-9">
                                <input name="random_number_0" id="random_number_0" class="form-control pull-left" type="text" value="<?=!empty($model->kid)?$model->random_number : 30?>" style="width:80%; margin-right:10px;" onkeyup="this.value=this.value.replace(/\D+/,'')" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_suijixianshibitian')?>">
                                <span class="pull-left" style="line-height: 2.5rem;"><?=Yii::t('frontend', 'exam_ti')?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row randYes" style="<?=$model->random_mode==LnExamination::RANDOM_MODE_YES?'':'display: none;'?>">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_meiyexianshi')?></label>
                            <div class="col-sm-9">
                                <input name="each_page_number_0" id="each_page_number_0" class="form-control pull-left" type="text" value="<?=!empty($model->kid)?$model->each_page_number : 15?>" style="width:80%; margin-right:10px" onkeyup="this.value=this.value.replace(/\D+/,'')"  data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_meiyexianshibitian')?>">
                                <span class="pull-left" style="line-height: 2.5rem;"><?=Yii::t('frontend', 'exam_ti')?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row <?=$model->examination_range==LnExamination::EXAMINATION_RANGE_COURSE?'hidden':''?>" id="limit_attempt_number_row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_t_times')?></label>
                            <div class="col-sm-9">
                                <input type="text" name="LnExamination[limit_attempt_number]" id="limit_attempt_number" class="form-control" value="<?=!empty($model->kid)?$model->limit_attempt_number:1?>" onkeyup="this.value=this.value.replace(/\D+/,'')" data-mode="COMMON" data-condition="^[0-9]\d*$" data-alert="<?=Yii::t('frontend', 'exam_changshicishubitian')?>" style="width: 80%;" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_pingfenmoshi')?></label>
                            <div class="col-sm-9">
                                <div class="form-group field-courseservice-course_type">
                                    <?php
                                    if (!empty($model->kid) ){
                                    ?>
                                    <select name="LnExamination[attempt_strategy]" class="form-control" disabled style="width: 80%;">
                                        <option value="0" <?=$model->attempt_strategy==LnExamination::ATTEMPT_STRATEGY_TOP?'selected':''?>><?= Yii::t('common', 'exam_attempt_strategy_0') ?></option>
                                        <option value="1" <?=empty($model->kid) || $model->attempt_strategy==LnExamination::ATTEMPT_STRATEGY_LAST?'selected':''?>><?= Yii::t('common', 'exam_attempt_strategy_1') ?></option>
                                        <option value="2" <?=$model->attempt_strategy==LnExamination::ATTEMPT_STRATEGY_AVG?'selected':''?>><?= Yii::t('common', 'exam_attempt_strategy_2') ?></option>
                                        <option value="3" <?=$model->attempt_strategy==LnExamination::ATTEMPT_STRATEGY_FIRST?'selected':''?>><?= Yii::t('common', 'exam_attempt_strategy_3') ?></option>
                                    </select>
                                    <?php
                                    }else{
                                    ?>
                                    <select name="LnExamination[attempt_strategy]" class="form-control" readonly style="width: 80%;">
                                        <option value="1" selected><?=Yii::t('frontend', 'exam_zuihouyicidefen')?></option>
                                    </select>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_jige')?></label>
                            <div class="col-sm-9">
                                <input name="LnExamination[pass_grade]" id="pass_grade" class="form-control" type="text" value="<?=$model->pass_grade?>" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_bitianziduan_detailed')?>" style="width: 80%">%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="mode_1" class="examType hide">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_suijixianshi')?></label>
                            <div class="col-sm-9">
                                <input name="random_number_1" id="random_number_1" class="form-control pull-left" type="text" value="<?=!empty($model->kid)?$model->random_number : 30?>" style="width:80%; margin-right:10px;" onkeyup="this.value=this.value.replace(/\D+/,'')" data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_suijixianshibitian')?>">
                                <span class="pull-left" style="line-height: 2.5rem;"><?=Yii::t('frontend', 'exam_ti')?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm">
                            <label class="col-sm-3 control-label"><?=Yii::t('frontend', 'exam_meiyexianshi')?></label>
                            <div class="col-sm-9">
                                <input name="each_page_number_1" id="each_page_number_1" class="form-control pull-left" type="text" value="<?=!empty($model->kid)?$model->each_page_number : 15?>" style="width:80%; margin-right:10px" onkeyup="this.value=this.value.replace(/\D+/,'')"  data-mode="COMMON" data-condition="required" data-alert="<?=Yii::t('frontend', 'exam_meiyexianshibitian')?>">
                                <span class="pull-left" style="line-height: 2.5rem;"><?=Yii::t('frontend', 'exam_ti')?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="c"></div>

            <div class="centerBtnArea">
                <a href="###" class="btn btn-default btn-sm centerBtn hide" id="preview" onclick="showPreview('<?=$model->kid?>');" style="width:20%"><?= Yii::t('common', 'preview_button') ?></a>
                <?php
                if ($model->release_status != LnExamination::STATUS_FLAG_NORMAL) {
                    ?>
                    <a href="###" class="btn btn-default btn-sm centerBtn" id="temp" onclick="save(0)"  style="width:20%"><?= Yii::t('common', 'save_temp') ?></a>
                    <?php
                }
                ?>
                <a href="###" class="btn btn-default btn-sm centerBtn" id="publish" style="width:20%" onclick="save(1)"><?= Yii::t('common', 'art_publish') ?></a>
                <input type="hidden" name="LnExamination[release_status]" id="release_status" value="<?=$model->release_status?>" />
            </div>
        </div>

        <div class="c"></div>
    </form>
    <div class="c"></div>
</div>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<script>
    app.genCalendar();
    var validation = app.creatFormValidation($("#form1"));
    var examination_paper_id = '<?=$model->examination_paper_id?>';
    var mode = '<?=$model->examination_mode?>';
    function toDecimal(x) {
        var f = parseFloat(x);
        if (isNaN(f)) {
            return;
        }
        f = Math.round(x*100)/100;
        return f;
    }
    $(function(){
        if (mode != "" && mode == '1'){
            $("#mode_0").addClass('hide');
            $("#mode_1").removeClass('hide');
            $("#preview").removeClass('hide');
        }
        $("#new_exam").on('change', '#random_mode', function(e){
           if (parseInt($(this).val()) == 0){
               $(".disorder").show();
               $(".randYes").hide();
           } else {
               $(".disorder").hide();
               $(".randYes").show();
           }
        });
       $("#new_exam").on('change','#examination_mode_add', function(e){
           $(".examType").addClass('hide');
           $("#mode_"+$(this).val()).removeClass('hide');
           app.refreshAlert("#new_exam");
           if (parseInt($(this).val()) == 1){
               $("#preview").removeClass('hide');
           }else{
               $("#preview").addClass('hide');
           }
           $(this).attr('data-type', $(this).val());
       }) ;
        $("#examination_paper_id").change(function(){
            if ($(this).val() == ""){
                $("#paperInfo").html('');
            }else{
                $("#paperInfo").html($("#examination_paper_id option:selected").attr('data-info'));
            }
        });

        $("input[name='LnExamination[examination_range]']").on('click', function(){
            if ($("input[name='LnExamination[examination_range]']:checked").val() == 1){
                $("#examination_time").addClass('hidden');
                $("input[name='LnExamination[start_at]']").val('');
                $("input[name='LnExamination[end_at]']").val('');
                $("#limit_attempt_number").val(0);
                $("#limit_attempt_number_row").addClass('hidden');
            }else{
                $("#examination_time").removeClass('hidden');
                $("#limit_attempt_number_row").removeClass('hidden');
            }
        });

        $("#limit_time").on('keyup keypress blur', function(){
            if ($(this).val().length > 0 && $(this).val() > 0) {
                $("#limit_time_number").find('font').html($(this).val());
                $("#limit_time_number").show();
            }else{
                $("#limit_time_number").hide();
            }
        });
    });
    var click_mod = true;
    function save(status) {
        if (!click_mod){
            app.showMsg('<?=Yii::t('frontend', 'exam_submiting')?>');
            return false;
        }
        $("#release_status").val(status);
        var examination_mode = parseInt($("#examination_mode_add").val());
        var title = $("#title").val().replace(/(^\s*)|(\s*$)/g, '');
        if (title == "" ){
            //app.showMsg('名称不能为空');
            validation.showAlert($("#title"));
            return false;
        }
        if (app.stringLength(title) > 500) {
            //app.showMsg('名称不能超过250个汉字');
            validation.showAlert($("#title"), "<?=Yii::t('frontend', '{value}_limit_250_word',['value'=>Yii::t('common','name')])?>");
            return false;
        }
        var xss_title = filterXSS(title);
        if (title != xss_title){
            validation.showAlert($("#title"),"<?=Yii::t('frontend', '{value}_lillegal_char',['value'=>Yii::t('common','name')])?>");
            return false;
        }
        var examination_paper_id = $("#examination_paper_id").val();
        if (examination_paper_id == "") {
            app.showMsg('<?=Yii::t('frontend', 'exam_choose_paper')?>');
            return false;
        }
        var examination_range = $("input[name='LnExamination[examination_range]']:checked").val();
        if (typeof examination_range == 'undefined'){
            app.showMsg('<?=Yii::t('frontend', 'exam_choose_exam_range')?>');
            return false;
        }
        if (examination_mode == 0) {
            var start_at = $("#start_at").val();
            var end_at = $("#end_at").val();
            if (parseInt(examination_range) == 0){
                if (start_at == ""){
                    validation.showAlert($("#start_at"));
                    return false;
                }else{
                    validation.hideAlert($("#start_at"));
                }
                if (end_at == ""){
                    validation.showAlert($("#end_at"));
                    return false;
                }else{
                    validation.hideAlert($("#end_at"));
                }
            }
            if (start_at && end_at){
                start_at = new Date(start_at.replace(/\-/g, "\/"));
                end_at = new Date(end_at.replace(/\-/g, "\/"));
                if (start_at>end_at){
                    validation.showAlert($("#start_at"), "<?=Yii::t('frontend', 'exam_warn_start_lessthan_end')?>");
                    return false;
                }
            }
            var random_mode = parseInt($("#random_mode").val());
            if (random_mode == 1) {
                var random_number = $("#random_number_0").val();
                if (random_number == "") {
                    validation.showAlert($("#random_number_0"));
                    return false;
                }
                if (random_number == 0) {
                    validation.showAlert($("#random_number_0"), '<?=Yii::t('frontend', 'exam_warn_suijixianshidayuling')?>');
                    return false;
                }
                var each_page_number = $("#each_page_number_0").val();
                if (each_page_number == "") {
                    validation.showAlert($("#each_page_number_0"));
                    return false;
                }
                if (parseInt(each_page_number) == 0) {
                    validation.showAlert($("#each_page_number_0"), '<?=Yii::t('frontend', 'exam_warn_meiyexianshidayuling')?>');
                    return false;
                }
                if (parseInt(random_number) < parseInt(each_page_number)){
                    validation.showAlert($("#each_page_number_0"), '<?=Yii::t('frontend', 'exam_warn_shuliang')?>');
                    return false;
                }
                $("input[name='LnExamination[question_disorder]").attr('checked', false);
                $("input[name='LnExamination[option_disorder]").attr('checked', false);
            }else{
                $("#random_number_0").val('');
                $("#each_page_number_0").val('');
            }
            if (parseInt(examination_range) == 1 ) {
                $("#limit_attempt_number").val(0);//20160216
            }
            var limit_attempt_number = $("#limit_attempt_number").val();
            if (limit_attempt_number == "") {
                validation.showAlert($("#limit_attempt_number"));
                return false;
            }
            var pass_grade = $("#pass_grade").val().replace(/\s+/, '');
            var reg = /^[0-9]+([.]{1}[0-9]{1,2})?$/;
            if (pass_grade == "") {
                validation.showAlert($("#pass_grade"));
                return false;
            }
            if (!reg.test(pass_grade)) {
                validation.showAlert($("#pass_grade"));
                return false;
            }
            if (toDecimal(pass_grade) < 0){
                validation.showAlert($("#pass_grade"), '<?=Yii::t('frontend', 'exam_warn_jigexian_buneng_xiaoyuling')?>');
                return false;
            }
            if (toDecimal(pass_grade) > toDecimal(100)){
                validation.showAlert($("#pass_grade"), '<?=Yii::t('frontend', 'exam_warn_jigexian_buneng_dayu')?>');
                return false;
            }
        } else {
            var random_number = $("#random_number_1").val();
            if (random_number == "") {
                validation.showAlert($("#random_number_1"));
                return false;
            }
            if (random_number == 0) {
                validation.showAlert($("#random_number_1"),'<?=Yii::t('frontend', 'exam_warn_suijixianshi_dayuling')?>');
                return false;
            }
            var each_page_number = $("#each_page_number_1").val();
            if (each_page_number == "") {
                validation.showAlert($("#each_page_number_1"));
                return false;
            }
            if (each_page_number == 0) {
                validation.showAlert($("#each_page_number_1"),'<?=Yii::t('frontend', 'exam_warn_meiyexianshi_dayuling')?>');
                return false;
            }
            if (parseInt(random_number) < parseInt(each_page_number)){
                validation.showAlert($("#each_page_number_1"), '<?=Yii::t('frontend', 'exam_warn_shuliang')?>');
                return false;
            }
        }
        var url = $("#form1").attr('action');
        click_mod = false;
        $.ajax({
            url: url,
            data: $("#form1").serialize(),
            dataType: 'json',
            type: 'POST',
            async: false,
            success: function(data){
                click_mod = true;
                if (data.result == 'success'){
                    reloadForm();
                    app.showMsg('<?=Yii::t('frontend', 'exam_opt_succeed')?>');
                    $("#new_exam").empty();
                    app.hideAlert($("#new_exam"));
                    loadTree();
                    return false;
                }else{
                    app.showMsg('<?=Yii::t('frontend', 'exam_opt_failed')?>');
                    return false;
                }
            },
            error: function(error){
                click_mod = true;
                app.showMsg('<?= Yii::t('frontend', 'network_anomaly') ?>');
                return false;
            }
        });
        return false;
    }

    function showPreview(id){
        $("#release_status").val(status);
        var title = $("#title").val().replace(/\s+/, '');
        if (title == "" ){
            //app.showMsg('名称不能为空');
            validation.showAlert($("#title"));
            return false;
        }
        if (app.stringLength(title) > 500) {
            //app.showMsg('名称不能超过250个汉字');
            validation.showAlert($("#title"), "<?=Yii::t('frontend', 'exam_mingcheng_morethan_preset')?>");
            return false;
        }
        var examination_paper_id = $("#examination_paper_id").val();
        if (examination_paper_id == "") {
            app.showMsg('<?=Yii::t('frontend', 'exam_choose_paper')?>');
            return false;
        }
        var random_number = $("#random_number_1").val();
        if (random_number == "") {
            //app.showMsg('随机显示为必填项');
            validation.showAlert($("#random_number_1"));
            return false;
        }
        if (random_number == 0) {
            //app.showMsg('随机显示必须大于0');
            validation.showAlert($("#random_number_1"),'<?=Yii::t('frontend', 'exam_warn_suijixianshidayuling')?>');
            return false;
        }
        var each_page_number = $("#each_page_number_1").val();
        if (each_page_number == "") {
            //app.showMsg('每页显示为必填项');
            validation.showAlert($("#each_page_number_1"));
            return false;
        }
        if (parseInt(each_page_number) == 0) {
            //app.showMsg('每页显示必须大于0');
            validation.showAlert($("#each_page_number_1"),'<?=Yii::t('frontend', 'exam_warn_meiyexianshidayuling')?>');
            return false;
        }
        if (parseInt(random_number) < parseInt(each_page_number)){
            validation.showAlert($("#each_page_number_1"), '<?=Yii::t('frontend', 'exam_warn_shuliang')?>');
            return false;
        }
        var data = $("#form1").serialize();
        $("#previewExam").empty();
        $.ajax({
            url: '<?=Url::toRoute(['/exam-manage-main/preview-exam'])?>?preview=add&id='+id,
            data: data,
            type: 'POST',
            success: function(html){
                $("#previewExam").html(html);
                app.alertWideAgain("#previewExam");
            },
            error: function(){
                app.showMsg('<?=Yii::t('frontend', 'exam_network_err')?>');
                return false;
            }
        });
    }
    var load_paper = true;
    function loadPaper(examination_paper_id){
        if (!load_paper) return false;
        $("#paperInfo").html('');
        $("#examination_paper_id").html('<option value=""><?=Yii::t('frontend', 'exam_pls_choose')?></option>');
        var paper_url = '<?=Url::toRoute(['/exam-manage-main/get-paper'])?>';
        var examination_mode = $("#examination_mode_add").val();
        load_paper = false;
        $.getJSON(paper_url, {examination_mode: examination_mode}, function(data){
            if (data.result == 'success'){
                var list = data.data;
                var len = list.length;
                if (len > 0){
                    for (var i = 0; i < len; i++){
                        var html = '<option value="'+list[i]['kid']+'" data-info="'+list[i]['examination_question_number']+'<?=Yii::t('frontend', 'exam_ti')?>, '+list[i]['default_total_score']+'<?= Yii::t('frontend', 'point') ?>"'+(list[i]['kid']==examination_paper_id?'selected':'')+' data-total="'+list[i]['default_total_score']+'">'+list[i]['title']+'</option>';
                        $("#examination_paper_id").append(html);
                    }
                }else{
                    //
                }
                load_paper = true;
            }else{
                app.showMsg('<?=Yii::t('frontend', 'exam_err_paper_load_fail')?>');
                return false;
            }
        });
    }
    loadPaper(examination_paper_id);
</script>
