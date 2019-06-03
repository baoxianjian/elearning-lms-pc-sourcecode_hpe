<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/6/15
 * Time: 16:57
 */
use components\widgets\TBreadcrumbs;
use yii\helpers\Url;

$componentName = str_replace('resource/','',$this->context->id);
$this->pageTitle = $model->courseware_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('common','resource_management'), 'url' => ['/resource/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('common','{value}_management',['value'=>Yii::t('common',$componentName)]), 'url' => ['/resource/'.$componentName.'/manage']];
$this->params['breadcrumbs'][] = $this->pageTitle;


?>
<script type="text/javascript">
    $(document).ready(function(){
        loadList();
    });
    function loadList(){
        var ajaxUrl = "<?=Url::toRoute(['resource/status'])?>";
        ajaxGet(ajaxUrl, "resourceStatus");
    }
</script>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-8 col-sm-8">
            <div class="panel panel-default hotNews">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-bookmark"></i><?=$model->courseware_name?>
                </div>
                <div class="panel-body uploadCourse">
                    <div style="text-align:center">
                        <?
                        $TFileModelHelper = new \common\helpers\TFileModelHelper();
                        $TFileModelHelper->Play($model->file_id,$download);
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-4">
            <div class="panel panel-default hotNews">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-dashboard"></i> <?=Yii::t('common', 'resource_status')?>
                </div>
                <div class="panel-body resourceStatu">
                    <ul style="zoom:0.75" id="resourceStatus"></ul>
                </div>
            </div>
        </div>
    </div>
</div>
