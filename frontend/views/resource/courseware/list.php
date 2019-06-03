<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/6/19
 * Time: 16:56
 */

use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TGridView;
use common\helpers\TStringHelper;
use common\helpers\TFileModelHelper;
use common\models\learning\LnCourseware;

$componentName = str_replace('resource/','',$this->context->id);
$gridColumns = [
    [
        'header' => Yii::t('common','courseware_code'),
        'attribute' => 'courseware_code',
    ],
    [
        'header' => Yii::t('common','type'),
        'value' => function ($model, $key, $index, $column) {
            return $model->getCoursewareIcon();
        },
        'contentOptions' => function ($model, $key, $index, $column) {
            return ['title' => $model->getCoursewareComponentTitle()];
        },
        'format' => 'html',
    ],
    [
        'header' => Yii::t('common', 'courseware_name'),
        'attribute' => 'courseware_name',
        'value' => function ($model, $key, $index, $column) {
            $link = Yii::$app->urlManager->createUrl([$this->context->id.'/preview','coursewareId'=>$key]);
            $text = TStringHelper::subStr($model->courseware_name, 10, 'utf-8', 0, '...');
            return  Html::a($text, $link, ['class'=>'preview']);
        },
        'contentOptions' => function ($model, $key, $index, $column) {
            return ['title' => $model->courseware_name,'style' => 'text-align:left;'];
        },
        'format' => 'html',
    ],
    [
        'header' => Yii::t('common', 'filename'),
        'attribute' => 'file_id',
        'value' => function($model, $key){
            return TStringHelper::subStr($model->getFileName(), 18, 'utf-8', 0, '...');
        },
        'contentOptions' => function ($model, $key, $index, $column) {
            return ['title' => $model->getFileName(),'style' => 'text-align:left;'];
        },
        'format' => 'html',
    ],
    [
        'header' => Yii::t('common', 'time_validity'),
        'attribute' => 'start_at',
        'value' => function ($model, $key, $index, $column) {
            $str = $model->end_at ? date("Y-m-d",$model->end_at) : Yii::t('frontend', 'forever');
            return $str;
        },
    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'header' => Yii::t('common', 'operation_button'),
        'template' => '{down}{view}{update}{delete}',
        'buttons' => [
            'down' => function ($url, $model, $key) {
                //$link = Yii::$app->urlManager->createUrl([$this->context->id.'/view','id'=>$key,'download'=>true]);
                $link = TFileModelHelper::getFileSecureLink($model->file_id);/*防盗链*/
                return  $model->is_allow_download == LnCourseware::ALLOW_DOWNLOAD_YES ? Html::a('&#4105;', $link, ['class' => 'btn-xs icon iconfont','title'=>Yii::t('common', 'download'),'target' => '_blank']): '';
            },
            'view' => function ($url, $model, $key) {
                $link = Yii::$app->urlManager->createUrl([$this->context->id.'/see','id'=>$key,'ajax'=>true]);
                return
                    Html::a('&#x1007;', "javascript:;", ['class' => 'btn-xs icon iconfont', 'id'=> 'ViewButton', 'title'=> Yii::t('common', 'view_button'), 'onclick' => "seeModal('SeeModal','" .$link . "');",]);
            },
            'update' => function ($url, $model, $key) {
                $link = Yii::$app->urlManager->createUrl([$this->context->id.'/edit', 'id' => $key, 'ajax' => true]);
                return Html::a('&#x1001;', 'javascript:;',
                    [
                        'class' => 'btn-xs icon iconfont',
                        'id' => 'EditButton',
                        'title'=> Yii::t('common', 'edit_button'),
                        'onclick' => "loadModalFormData('NoPermissionModal','NoPermissioniframe','" .$link . "','".$key."');"
                    ]);
            },
            'delete' => function ($url, $model, $key) {
                $isUesd = $model->IsUsed($key);
                return
                    $isUesd ? '' : Html::a('&#x1006;', 'javascript:;',['class' => 'btn-xs icon iconfont DeleteButton', 'title'=> Yii::t('common', 'delete_button'), 'onclick'=>"delItem('".Yii::$app->urlManager->createUrl([$this->context->id.'/delete', 'id' => $key])."');" ]);
            },
        ]
    ],
];
?>
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl([$this->context->id.'/manage']);?>"/>
<style>
    #grid .summary {display: none;}
    .table > thead:first-child > tr:first-child > th,.table-bordered > tbody > tr > td {text-align: center;}
    .btn-xs {padding: 5px 5px;}
    #grid table td:last-child {text-align: left;}
</style>
<div class="actionBar" style="margin-top: 0px;">
    <?=Html::a(Yii::t('common','upload_{value}',['value'=>Yii::t('common',$componentName)]),[$this->context->id.'/upload'],['class'=>'btn btn-success  pull-left'])?>
    <?php  echo $this->render('_search', [
        'model' => $searchModel,
        'componentArray'=>$componentArray,
        'domain' => $domain,
        'TreeNodeKid' => $TreeNodeKid,
    ]); ?>
    <div style="clear: both"></div>
    <div style="text-align:right">
        <?= TGridView::widget([
        'id'=>'grid',
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns]); ?>
    </div>
</div>
<script>
    $(function(){
        $(".pagination").on('click', 'a', function(e){
            e.preventDefault();
            app.get($(this).attr('href'), function(e){
                if (e){
                    $("#rightList").html(e);
                }
            });
        });
    })
</script>