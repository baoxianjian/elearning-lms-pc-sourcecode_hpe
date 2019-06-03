<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/17
 * Time: 11:09
 */

use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TGridView;
use common\helpers\TStringHelper;
use common\services\learning\ExaminationQuestionService;
use common\models\learning\LnExaminationQuestion;

$gridColumns = [
    [
        'header' => Yii::t('common', 'exam_question_code'),
        'value' => function($model){
            return $model->code;
        },
        'contentOptions' => function(){
            return ['style' => 'width: 15%;'];
        },
    ],
    [
        'header' => Yii::t('common', 'exam_question_title'),
        'value' => function ($model,$key){
            return '<a href="'.Url::toRoute(['/exam-manage-main/question-view','id'=>$key]).'" class="preview">'.$model->title.'</a>';
        },
        'contentOptions' => function($model, $key, $index, $column){
            return ['title' => $model->title,'style' => 'width: 30%;text-align:left;'];
        },
        'format' => 'html',
    ],
    [
        'header' => Yii::t('common', 'exam_question_type'),
        'value' => function($model){
            return $model->getExamQuestionCategoryName();
        },
        'contentOptions' => function(){
            return ['style' => 'width: 10%;'];
        },
    ],
    [
        'header' => Yii::t('common', 'exam_question_create_by'),
        'value' => function($model){
            return $model->getExamQuestionCreateBy();
        },
        'contentOptions' => function(){
            return ['style' => 'width: 15%;'];
        },
    ],
    [
        'header' => Yii::t('common', 'exam_question_create_time'),
        'value' => function($model){
            return date('Y-m-d',$model->created_at);
        },
        'contentOptions' => function(){
            return ['style' => 'width: 15%;'];
        },
    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'header' => Yii::t('common', 'operation_button'),
        'template' =>'{updatepop} {deletepop}',
        'buttons' => [
            'updatepop' => function ($url, $model, $key) {
                return Html::a('&#x1001;', 'javascript:;', ['title'=>Yii::t('common','edit_button'), 'class'=>'btn-xs icon iconfont','onclick' => 'editButton("'.$key.'")']);
            },
            'deletepop' => function ($url, $model, $key) {
                $service = new ExaminationQuestionService();
                $result = $service->isPaperRelated($key);
                return  Html::a('&#x1006;', 'javascript:;', ['id' => 'DeleteButton', 'class'=>'btn-xs icon iconfont', 'title'=> Yii::t('common', 'delete_button'),'onclick' => 'deleteButton("' . $key . '", '.($result ? 'true' : 'false' ).');']);
            },
        ],
        'contentOptions' => function(){
            return ['style' => 'width: 16%;'];
        },
    ],
];
?>
<div class="actionBar fc-clear" style="margin-top: 20px;">
    <div class="btn-group">
        <button type="button" class="btn btn-success dropdown-toggle pull-left" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?=Yii::t('frontend', 'exam_zengjiashiti')?> <span class="caret stBtn"></span>
        </button>
        <ul class="dropdown-menu" id="choiceBtn">
            <li><a href="###" class="select"><?=Yii::t('common', 'question_type_radio_checkbox')?></a></li>
            <li><a href="###" class="judge"><?=Yii::t('common', 'question_type_judge')?></a></li>
            <!-- <li><a href="###" data-toggle="modal" data-target=".fillBlank">填空题</a></li> -->
        </ul>
        <a class="btn btn-default pull-left" id="question_import" href="<?=Url::toRoute(['/exam-manage-main/question-import'])?>"><?=Yii::t('common', 'import')?></a>
    </div>
    <div class="form-inline pull-right" id="searchForm">
        <div class="form-group">
                <select id="examination_question_type" name="examination_question_type" class="form-control" name="">
                        <option value=""><?=Yii::t('frontend', 'exam_quanbutixing')?></option>
                        <option value="<?=LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO?>" <?=isset($params['examination_question_type']) && $params['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_RADIO ? 'selected' : ''?>><?=Yii::t('common', 'question_type_radio')?></option>
                        <option value="<?=LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX?>" <?=isset($params['examination_question_type']) && $params['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_CHECKBOX ? 'selected' : ''?>><?=Yii::t('common', 'question_type_checkbox')?></option>
                        <option value="<?=LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE?>" <?=isset($params['examination_question_type']) && $params['examination_question_type'] == LnExaminationQuestion::EXAMINATION_QUESTION_TYPE_JUDGE ? 'selected' : ''?>><?=Yii::t('common', 'question_type_judge')?></option>
                </select>
            <input type="text" id="keywords" value="<?=$keywords?>" class="form-control" placeholder="<?=Yii::t('frontend', 'exam_qingshurubiaoti')?>">
            <button type="reset" id="resetBtn" class="btn btn-default pull-right"><?=Yii::t('common', 'reset')?></button>
            <button type="button" class="btn btn-primary pull-right" id="searchBtn" style="margin-left:10px;"><?=Yii::t('common', 'search')?></button>
        </div>
    </div>
</div>
<div style="clear: both;"></div>
<div style="text-align:right">
    <?= TGridView::widget([
        'id'=>'grid',
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
        'pjax'=>false,
        'pjaxSettings'=>[
            'neverTimeout'=>true,
        ]
    ]); ?>
</div>
<script>
    var edit_url = '<?=Yii::$app->urlManager->createUrl(['/exam-manage-main/exam-question-edit'])?>';
    var del_url = '<?=Yii::$app->urlManager->createUrl(['/exam-manage-main/exam-question-delete'])?>';
    $(function(){
        $(".pagination").on('click', 'a', function(e){
            e.preventDefault();
            app.get($(this).attr('href'), function(data){
                if (data){
                    $("#rightList").html(data);
                }
            });
        });
        $("#choiceBtn a").click(function(e){
            e.preventDefault();
            var cat_id=$("#jsTree_tree_selected_result").val();
            if (typeof cat_id == 'undefined'){
                app.showMsg('<?=Yii::t('frontend', 'exam_page_loading')?>');
                return false;
            }
            cat_id = eval(cat_id);//转换成数组
            if (cat_id.length > 1 || cat_id[0] == -1){
                app.showMsg("<?=Yii::t('frontend', 'exam_qingxuanzetiku')?>");
                return;
            }
            var class_name = $(this).attr('class');
            $.get("<?=Url::toRoute(['/exam-manage-main/new-exam-question'])?>",{tree_node_id: cat_id[0], new_exam_question: class_name},function(e){
                if (e){
                    $("#new_exam_question").html(e);
                    app.alert($("#new_exam_question"));
                    window.common_tags = app.queryList("#tags");
                }else{
                    app.showMsg('<?=Yii::t('common', 'network_error')?>');
                    return false;
                }
            });
        });
        $("#resetBtn").click(function(){
            $("#examination_question_type").find('option').attr('selected', false);
            $("#examination_question_type").find('option').eq(0).attr('selected', true);
            $("#keywords").val('');
        });
        $("#searchBtn").click(function(){
            reloadForm();
        });
        $(".preview").on('click', function(e){
            e.preventDefault();
            $.get($(this).attr('href'), function(html){
                if (html){
                    $("#new_exam_question").html(html);
                    app.alert($("#new_exam_question"));
                }else{
                	app.showMsg('<?Yii::t('common', 'network_error')?>');
                    return false;
                }
            });
        });
        $("#question_import").on('click', function(e){
            e.preventDefault();
            var cat_id=$("#jsTree_tree_selected_result").val();
            if (typeof cat_id == 'undefined'){
                app.showMsg('<?=Yii::t('frontend', 'exam_page_loading')?>');
                return false;
            }
            cat_id = eval(cat_id);//转换成数组
            if (cat_id.length > 1 || cat_id[0] == -1){
                app.showMsg("<?=Yii::t('frontend', 'exam_qingxuanzetiku')?>");
                return;
            }
            $.get($(this).attr('href'), {treeNodeId: cat_id[0]}, function(html){
                if (html){
                	$("#import_exam_question").html(html);
                	app.alert($("#import_exam_question"));
                } else {
                	app.showMsg('<?Yii::t('common', 'network_error')?>');
                    return false;
                }
            });
        });
    });
    function editButton(id){
        if (id){
            window.common_modify_tags = '';
            $.get(edit_url, {id: id}, function(data){
                if (data){
                    $("#new_exam_question").html(data);
                    app.alert($("#new_exam_question"));
                    window.common_tags = app.queryList("#tags", common_modify_tags);
                }else{
                    app.showMsg('<?=Yii::t('frontend', 'exam_network_err')?>');
                    return false;
                }
            });
        }else{
            app.showMsg('<?=Yii::t('frontend', 'exam_network_err')?>');
            return false;
        }
    }
    function deleteButton(id, isRelated){
        var msg = isRelated ? '<?=Yii::t('frontend', 'exam_note5')?>' : '<?=Yii::t('frontend', 'exam_shifoushanchu')?>';
        $("#msm_alert_content").html(msg);
        app.alert("#foo",
            {
                ok: function ()
                {

                    if (id) {
                        $.get(del_url, {id: id}, function (response) {
                            if (response.result == 'success') {
                                app.showMsg('<?=Yii::t('frontend', 'exam_del_succeed')?>');
                                reloadForm();
                            } else {
                                app.showMsg(response.errmsg);
                                return false;
                            }
                        }, 'json');
                    }else{
                        app.showMsg('<?=Yii::t('frontend', 'exam_network_err')?>');
                        return false;
                    }
                    return true;
                },
                cancel: function ()
                {

                    return true;
                }
            }
        );

    }
</script>
