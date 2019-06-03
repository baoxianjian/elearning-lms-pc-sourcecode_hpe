<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/23
 * Time: 11:48
 */

use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TGridView;
use common\helpers\TStringHelper;
use common\models\learning\LnExamination;

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
        'header' => Yii::t('common', 'examination_title'),
        'value' => function ($model,$key){
            return '<a href="'.Url::toRoute(['exam-manage-main/preview-exam', 'id' => $key, 'preview' => 'list']).'" class="preview">'.$model->title.'</a>';
        },
        'contentOptions' => function($model, $key, $index, $column){
            return ['title' => $model->title,'style' => 'width: 25%;text-align:left;'];
        },
        'format' => 'html',
    ],
    [
        'header' => Yii::t('common', 'examination_question_number'),
        'value' => function($model){
            $allNumbers = $model->getPaperQuestionNumber();
            return $model->random_mode == LnExamination::RANDOM_MODE_YES ? $model->random_number.'/'. $allNumbers : $allNumbers ;
        },
        'contentOptions' => function(){
            return ['style' => 'width: 10%;'];
        },
    ],
    [
        'header' => Yii::t('common', 'exam_question_create_by'),
        'value' => function($model){
            return $model->getExaminationCreateBy();
        },
        'contentOptions' => function(){
            return ['style' => 'width: 15%;'];
        },
    ],
    [
        'header' => Yii::t('common', 'examination_mode'),
        'value' => function($model){
            return $model->examination_mode == LnExamination::EXAMINATION_MODE_EXERCISE ? Yii::t('frontend', 'exam_lianxi') : Yii::t('frontend', 'exam_ceshi');
        },
        'contentOptions' => function(){
            return ['style' => 'width: 10%;'];
        },
    ],
    [
        'header' => Yii::t('common', 'status'),
        'value' => function($model){
            return Yii::t('common', 'status_'.$model->release_status);
        },
        'contentOptions' => function(){
            return ['style' => 'width: 10%;'];
        },
    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'header' => Yii::t('common', 'operation_button'),
        'template' =>'{publishpop} {seepop} {updatepop} {deletepop} {viewpop}',
        'buttons' => [
            'publishpop' => function($url, $model, $key){
                return $model->release_status == LnExamination::STATUS_FLAG_TEMP ? Html::a('&#x1004;', 'javascript:;', ['class'=>'btn-xs icon iconfont', 'title'=> Yii::t('common', 'art_publish'), 'onclick' => 'publishButton("'.$key.'")']) : '';
            },
            'seepop' => function($url, $model, $key){
                return Html::a('&#x1007;', 'javascript:;', [ 'onclick' => "seeModal('detail','" .Yii::$app->urlManager->createUrl(['/exam-manage-main/detail','id'=>$key]) . "');", 'class'=>'btn-xs icon iconfont', 'title' => Yii::t('common', 'view_button')]);
            },
            'updatepop' => function ($url, $model, $key) {
                return $model->release_status == LnExamination::STATUS_FLAG_TEMP ? Html::a('&#x1001;', 'javascript:;', [ 'class'=>'btn-xs icon iconfont', 'title'=> Yii::t('common', 'edit_button'), 'onclick' => 'editButton("'.$key.'")']) : '';
            },
            'deletepop' => function ($url, $model, $key) {
                return $model->release_status == LnExamination::STATUS_FLAG_TEMP ? Html::a('&#x1006;', 'javascript:;', ['id' => 'DeleteButton', 'class'=>'btn-xs icon iconfont', 'title'=> Yii::t('common', 'delete_button'),'onclick' => 'deleteExam("' . $key . '");']) : '';
            },
            'viewpop' => function($url, $model, $key){
                return ($model->release_status != LnExamination::STATUS_FLAG_TEMP && $model->examination_range == LnExamination::EXAMINATION_RANGE_SELF) ? Html::a(Yii::t('frontend', 'exam_view_score'), 'javascript:;',  [ 'onclick' => "viewButton('".$key."');", 'title' => Yii::t('common', 'view_button')]) : '';
            },
        ],
        'contentOptions' => function(){
            return ['style' => 'width: 15%; text-align:center;'];
        },
    ],
];
?>
<div class="actionBar fc-clear" style="margin-top: 20px;">
    <a class="btn btn-success pull-left" href="###" id="new_exam_btn"><?=Yii::t('frontend', 'exam_new_exam')?></a>
    <div class="form-inline pull-right">
        <div class="form-group field-courseservice-course_type">
            <select id="examination_mode" class="form-control">
                <option value=""><?=Yii::t('frontend', 'exam_pls_choose')?></option>
                <option value="0" <?=(isset($params['examination_mode']) && $params['examination_mode'] == LnExamination::EXAMINATION_MODE_TEST) ? 'selected' : ''?>><?=Yii::t('frontend', 'exam_ceshimoshi')?></option>
                <option value="1" <?=(isset($params['examination_mode']) && $params['examination_mode'] == LnExamination::EXAMINATION_MODE_EXERCISE) ? 'selected' : ''?>><?=Yii::t('frontend', 'exam_lianximoshi')?></option>
            </select>
            <div class="help-block"></div>
        </div>
        <div class="form-group">
            <input type="text" class="form-control" id="keywords" value="<?=isset($params['keywords']) ? $params['keywords'] :''?>" placeholder="<?= Yii::t('frontend', 'input_{value}',['value'=>Yii::t('common','examination_title')]) ?>">
            <button type="button" class="btn btn-default pull-right" id="reset"><?=Yii::t('frontend', 'reset')?></button>
            <button type="button" class="btn btn-primary pull-right" onclick="reloadForm();" style="margin-left:10px;"><?=Yii::t('frontend', 'tag_query')?></button>
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
    app.extend('alert');
    $(function(){
        $(".pagination").on('click', 'a', function(e){
            e.preventDefault();
            app.get($(this).attr('href'), function(data){
                if (data){
                    $("#rightList").html(data);
                }
            });
        });
        $("#new_exam_btn").on('click', function(e){
            e.preventDefault();
            $("#new_exam").empty();
            var cat_id=$("#jsTree_tree_selected_result").val();
            if (typeof cat_id == 'undefined'){
                app.showMsg('<?=Yii::t('frontend', 'exam_page_loading')?>');
                return false;
            }
            cat_id = eval(cat_id);//转换成数组
            if (cat_id.length > 1 || cat_id[0] == -1){
                app.showMsg("<?=Yii::t('frontend', 'exam_pls_choose_exam_cate')?>");
                return false;
            }
            $.get('<?=Url::toRoute(['/exam-manage-main/new-exam'])?>', {tree_node_id: cat_id[0]},function(data){
               if (data){
                   $("#new_exam").html(data);
                   app.alertWideAgain($("#new_exam"));
                   app.refreshAlert("#new_exam");
               }
            });
        });
        $("#reset").on('click', function(){
            $("#examination_mode option").eq(0).attr('selected', true);
           $("#keywords").val('');
        });

        $(".preview").on('click', function(e){
            e.preventDefault();
            $.get($(this).attr('href'), function(html){
                if (html){
                    $("#new_exam").html(html);
                    app.alertWide($("#new_exam"));
                    app.refreshAlert("#new_exam");
                }else{
                    app.showMsg('<?=Yii::t('frontend', 'exam_network_err')?>');
                    return false;
                }
            });
        });
    });
</script>
