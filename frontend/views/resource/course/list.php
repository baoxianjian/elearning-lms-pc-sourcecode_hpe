<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/29
 * Time: 16:56
 */

use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TGridView;
use common\helpers\TStringHelper;
use common\models\learning\LnCourse;
global $companyId;
$add_url = Url::toRoute(['resource/course/edit']);
$companyId = Yii::$app->user->identity->company_id;
$gridColumns = [
    'course_code',
    [
        'header' => Yii::t('common', 'course_name'),
        'value' => function ($model,$key){
            $text = TStringHelper::subStr(str_replace(' ', '', $model->course_name), 10, 'utf-8', 0, '...');
            //$text = $model->course_name;
            return Html::a($text, Yii::$app->urlManager->createUrl(['resource/course/preview','id'=>$model->kid]), ['class' => 'preview']);
        },
        'contentOptions' => function($model, $key, $index, $column){
            return ['title' => $model->course_name,'style' => 'text-align:left;'];
        },
        'format' => 'html',
    ],
    [
        'header' => Yii::t('common', 'status'),
        'value' => function($model){
            return $model->status == LnCourse::STATUS_FLAG_TEMP ? '<i class="glyphicon glyphicon-ok-circle tobe"></i>' : '<i class="glyphicon glyphicon-ok-sign be"></i>';
        },
        'contentOptions' => function($model, $key, $index, $column){
            return ['title' => Yii::t('common', 'status_'.$model->status)];
        },
        'format' => 'html',
    ],
    [
        'header' => Yii::t('common', 'relate_{value}', ['value'=>Yii::t('common','domain')]),
        'value' => function($model, $key, $index, $column){
            return $model->getDomainNameByText("", 10);
        },
        'contentOptions' => function($model, $key, $index, $column){
            return ['title' => $model->getDomainNameByText()];
        },
        'format' => 'html',
    ],
    [
        'header' => Yii::t('common','time_validity'),
        'value' => function ($model, $key, $index, $column) {
            $str = !empty($model['end_time']) ? date("Y-m-d",$model['end_time']) : Yii::t('frontend','forever');
            return $str;
        }
    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'header' => Yii::t('common', 'operation_button'),
        'template' =>'{view} {updatepop} {copy} {deletepop} {manage}',
        'buttons' => [
            'view' => function ($url, $model, $key) {
                return ($model->status == LnCourse::STATUS_FLAG_TEMP ? Html::a('&#x1004;', 'javascript:;',['title' => Yii::t('common', 'art_publish'),'class' => 'btn-xs icon iconfont', 'onclick' => 'publishCourse(\''.Yii::$app->urlManager->createUrl(['resource/course/publish','id'=>$model->kid]).'\')']) : '').Html::a('&#x1007;', 'javascript:;', [ 'onclick' => "seeModal('courseDetails','" .Yii::$app->urlManager->createUrl(['resource/course/see','id'=>$model->kid]) . "');", 'class'=>'btn-xs icon iconfont', 'title' => Yii::t('common', 'view_button')]);
            },
            'updatepop' => function ($url, $model, $key) {
                return
                    $model->status == LnCourse::STATUS_FLAG_NORMAL && $model->IsCourseReg()  ? Html::a('&#x1001;', Yii::$app->urlManager->createUrl(['resource/course/afteredit-view','id'=>$model->kid]),['id'=>'EditButton', 'class' => 'btn-xs icon iconfont', 'title'=>Yii::t('common', 'edit_button')])  : Html::a('&#x1001;', Yii::$app->urlManager->createUrl(['resource/course/edit','id'=>$model->kid]), ['id'=>'EditButton','title'=>Yii::t('common','edit_button'), 'class'=>'btn-xs icon iconfont']);
            },
            'copy' => function($url, $model,$key){
                return '<a href="javascript:;" class="btn-xs icon iconfont copyBtn" data-id="'.$model->kid.'" title="'.Yii::t('common','copy_button').'">&#x1005;</a>';
            },
            'deletepop' => function ($url, $model, $key) {
                return $model->status == LnCourse::STATUS_FLAG_NORMAL && $model->IsCourseReg()  ? '' : Html::a('&#x1006;', 'javascript:;', ['id' => 'DeleteButton', 'class'=>'btn-xs icon iconfont', 'title'=> Yii::t('common', 'delete_button'),'onclick' => 'deleteButton("' . $model->kid . '","' . Yii::$app->urlManager->createUrl(['resource/course/delete', 'id' => $model->kid]) . '");']);
            },
            'manage' => function ($url, $model, $key) {
                return $model->status == LnCourse::STATUS_FLAG_TEMP ? '' : '<a href="' . Yii::$app->urlManager->createUrl(['resource/course/online-detail', 'id' => $model->kid]) . '" class="btn-xs icon iconfont" title="' . Yii::t('common', 'manage_button') . '">&#x1003;</a>';
            },
        ],
    ],
];

?>
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['resource/course/manage']);?>"/>
<div id="publish_div" style="display: none;"></div>
<style>
    #grid .summary {display: none;}
    .table > thead:first-child > tr:first-child > th,.table-bordered > tbody > tr > td {text-align: center;}
    .btn-xs {padding: 5px 5px;}
    #grid table td:last-child {text-align: left;}
</style>
<div class="actionBar" style="margin-top: 0px;">
    <?=Html::a(Yii::t('frontend','add').Yii::t('frontend', 'course_online'),$add_url,['class'=>'btn btn-success  pull-left'])?>
    <?php  echo $this->render('_search', [
        'model' => $searchModel,
        'TreeNodeKid' => $TreeNodeKid,
        'visable' => $visable,
    ]); ?>
</div>
<div style="clear: both"></div>
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
    $(function(){
        $(".pagination").on('click', 'a', function(e){
            e.preventDefault();
            ajaxGet($(this).attr('href'), "rightList");
            $(".copyMenu").css({top: 0, left: 0, display: 'none'}).attr('data-courseid', '');
        });
        $(".preview").on('click', function(e){
            e.preventDefault();
            preView('previewModal', $(this).attr('href'));
        });
        $("#previewModal .modal-body-view").css('minHeight', $(window).height()+'px');
    });
</script>

