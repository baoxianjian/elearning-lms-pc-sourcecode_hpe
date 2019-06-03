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

$add_url = Url::toRoute(['resource/course/edit-face']);
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
            return $model->status == '0' ? '<i class="glyphicon glyphicon-ok-circle tobe"></i>' : '<i class="glyphicon glyphicon-ok-sign be"></i>';
        },
        'contentOptions' => function($model, $key, $index, $column){
            return ['title' => $model->status == '0' ? Yii::t('frontend','publish_status_no') : Yii::t('frontend','publish_status_no')];
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
            $str = $model['end_time'] ? date("Y-m-d",$model['end_time']) : Yii::t('frontend', 'forever');
            return $str;
        }
    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'header' => Yii::t('common', 'operation_button'),
        'template' =>'{view} {manage} {copy} {updatepop} {deletepop}',
        'buttons' => [
            'view' => function ($url, $model, $key) {
                return ($model->status == LnCourse::STATUS_FLAG_NORMAL ? '':
                    Html::a('&#x1004;', 'javascript:;',['class' => 'btn-xs icon iconfont', 'title'=>Yii::t('common', 'art_publish'), 'onclick' => 'publishCourse(\''.Yii::$app->urlManager->createUrl(['resource/course/publish','id'=>$model->kid]).'\')'])).Html::a('&#x1007;', 'javascript:;',  [ 'onclick' => "seeModal('courseDetails','" .Yii::$app->urlManager->createUrl(['resource/course/see','id'=>$model->kid]) . "');", 'class'=>'btn-xs icon iconfont', 'title' => Yii::t('common', 'view_button')])
                    ;
            },
            'manage' => function($url, $model,$key){
                return '<a href="'.Yii::$app->urlManager->createUrl(['resource/course/offline-sub-detail','id'=>$model->kid]).'" class="btn-xs icon iconfont" title="'.Yii::t('common','manage_button').'">&#x1003;</a>';
            },
            'copy' => function($url, $model,$key){
                return '<a href="javascript:courseCopy(\''.$model->kid.'\');" class="btn-xs icon iconfont" data-id="'.$model->kid.'" title="'.Yii::t('common','copy_button').'">&#x1005;</a>';
            },
            'updatepop' => function ($url, $model, $key) {
                return $model->status == LnCourse::STATUS_FLAG_NORMAL ? Html::a('&#x1001;', Yii::$app->urlManager->createUrl(['resource/course/afteredit-face-view','id'=>$model->kid]),['id'=>'EditButton', 'class' => 'btn-xs icon iconfont', 'title'=>Yii::t('common', 'edit_button')]) : Html::a('&#x1001;', Yii::$app->urlManager->createUrl(['resource/course/edit-face','id'=>$model->kid]),['id'=>'EditButton', 'class' => 'btn-xs icon iconfont', 'title'=>Yii::t('common', 'edit_button')]);
            },
            'deletepop' => function ($url, $model, $key) {
                return $model->status == LnCourse::STATUS_FLAG_NORMAL ? '' : Html::a('&#x1006;', 'javascript:;', ['id' => 'DeleteButton', 'title'=> Yii::t('common', 'delete_button'), 'class'=>'btn-xs icon iconfont','onclick' => 'deleteButton("' . $model->kid . '","' . Yii::$app->urlManager->createUrl(['resource/course/delete', 'id' => $model->kid]) . '");']);
            },
        ],
    ],
];

?>
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['resource/course/manage-face']);?>"/>
<div id="publish_div" style="display: none;"></div>
<style>
    #grid .summary {display: none;}
    .table > thead:first-child > tr:first-child > th,.table-bordered > tbody > tr > td {text-align: center;}
    .btn-xs {padding: 5px 5px;}
    #grid table td:last-child {text-align: left;}
</style>
<div class="actionBar" style="margin-top: 0px;">
    <?=Html::a(Yii::t('frontend','add').Yii::t('frontend', 'course_face'),$add_url,['class'=>'btn btn-success  pull-left'])?>
    <?php  echo $this->render('_search-face', [
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
        });
        $(".preview").on('click', function(e){
            e.preventDefault();
            preView('previewModal', $(this).attr('href'));
        });
        $("#previewModal .modal-body-view").css('minHeight', $(window).height()+'px');
    });
</script>

