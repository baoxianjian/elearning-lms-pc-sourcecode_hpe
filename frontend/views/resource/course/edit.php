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

$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','resource_management'),'url'=>['/resource/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','online').Yii::t('common','course_management'),'url'=>['/resource/course/manage']];
if($model->kid){
    $this->params['breadcrumbs'][] = Yii::t('frontend', 'edit_course');
    $this->params['breadcrumbs'][] = Html::decode($model->course_name);
}else{
    $this->params['breadcrumbs'][] = Yii::t('common', 'create_{value}',['value'=>Yii::t('common','course')]);
    $this->params['breadcrumbs'][] = '';
}
?>
<style>
    .form-control{float: none}
</style>
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
                <div class="panel-body uploadCourse" style="text-align:center">
                    <h4><?=Yii::t('common', 'please_input_base_info_at_this_panel')?></h4>
                    <hr/>
                    <div class="ln-course-create">
                        <?= $this->render('_form', [
                            'model' => $model,
                            'origin_course_id' => $origin_course_id,
                            'domain' => $domain,
                            'domain_id' => $domain_id,
                            'tree_node_id' => $tree_node_id,
                            'dictionary_level_list' => $dictionary_level_list,
                            'dictionary_lang_list' => $dictionary_lang_list,
                            'dictionary_currency_list' => $dictionary_currency_list,
                            'resource' => $resource,
                            'tag' => $tag,
                            'teacher' => $teacher,
                            'certification' => $certification,
                            'course_time' => $course_time,
                            'course_period_unit_list'=>$course_period_unit_list,
                        	'dictionary_approval_list' => $dictionary_approval_list,
                            'is_copy' => $is_copy,
                            'audience' => $audience,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>