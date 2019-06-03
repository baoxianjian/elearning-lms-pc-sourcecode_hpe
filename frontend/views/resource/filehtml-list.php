<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use components\widgets\TGridView;
?>
<input type="hidden" name="sequence_number" id="sequence_number" value="<?=$sequence_number?>">
<script>
    $(document).ready(function(){
        $("#searchBtn").click(function() {
            reloadForm1();
            //return false;
        });
        $("#searchText2").keypress(function(event){
            var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
            if (keyCode == 13){
                return false;
            }
        });

    });
    function reloadForm1()
    {
        var ajaxUrl = "<?=Url::toRoute(['resource/file-html','component_id'=>$component_id,'sequence_number'=>$sequence_number,'domain_id'=>$domain_id,'courseware_type'=>$courseware_type,'entry_mode'=>$entry_mode])?>";
        var pagesize = $('#pageSizeSelect_grid').val();
        if (typeof pagesize == 'undefined') pagesize = 10;
        ajaxUrl = urlreplace(ajaxUrl,'PageSize', pagesize);
        ajaxUrl = urlreplace(ajaxUrl,'CoursewareService[courseware_name]',$('#searchText2').val());
        ajaxGet(ajaxUrl, 'containerType3');
    }
    function resetForm1(){
        $("input[name='CoursewareService[courseware_name]']").val('');
        $("#searchText").val('');
        /*reloadForm();*/
    }
</script>
<style>
    #FormModal .componentSelected {
        background: #ffc none repeat scroll 0 0;
    }
    #FormModal .componentSelected a {
        color: red !important;
    }
    .pull-left{
        margin-left:1%;
    }
    .searchForm, .scoreList {margin: 8px;}
    .searchForm .form-group{float: left;}
    .summary,#grid thead/*,#grid #pageSizeSelect_grid*/ {display: none;}
    #grid .table-bordered {border: 0 none;}
    #grid .table-responsive {overflow-x: inherit;}
    #grid .table-striped>tbody>tr:nth-of-type(odd) {background-color: transparent;}
    #grid .table>tbody>tr>td {border-top: 1px dotted #000; padding: 3px 8px; line-height: 24px;}
    #grid .table-bordered>tbody>tr>td {border: 1px dotted #000;}
    #grid .table-bordered>tbody>tr>td[colspan='1'] {border: 0 none;}
</style>
<a class="btn btn-success pull-left" href="<?=Yii::$app->urlManager->createUrl(['resource/courseware/upload']).'?code=html'?>" target="_blank"><?= Yii::t('frontend', 'build') ?></a>
<div class="form-inline pull-right searchForm">
    <?php $form = ActiveForm::begin([
        'id' => 'searchForm',
        'method' => 'get',
    ]); ?>
    <?= $form->field($searchModel, 'courseware_name')->textInput(['id'=>'searchText2','placeholder'=>Yii::t('frontend', 'input_what_you_want_to_search')])->label(false) ?>
    <?= Html::button(Yii::t('common', 'search'), ['id'=>'searchBtn','class' => 'btn btn-primary']) ?>
    <?= Html::button(Yii::t('common', 'reset'), ['onclick'=>'resetForm1()','class' => 'btn btn-default']) ?>
    <?php ActiveForm::end(); ?>
</div>
<div style="clear: both"></div>
<div class="panel-default scoreList">
    <?php
    $gridColumns = [
        [
            'header' => Yii::t('common', 'courseware_name'),
            'value' => function($model,$key){
                return '<a href="javascript:;"> <i class="icon iconfont">မ</i>&nbsp;'.$model->courseware_name.'</a><div class="addAction pull-right"><i class="glyphicon glyphicon-plus" ></i></div><input type="hidden" class="componentid" name="coursewares[]" value="'.$model->kid.'"/>';
            },
            'contentOptions' => function($model, $key, $index, $column){
                return [
                    'id' => 'ware_'.$model->kid,
                    'onclick' => 'ToggleComponent(this)',
                    'class' => 'component',
                ];
            },
            'format' => 'html',
        ]
    ];
    ?>
    <?= TGridView::widget([
        'id'=>'grid',
        'dataProvider' => $coursewares,
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
            var ajaxUrl = $(this).attr('href');
            var pageSize = $('#pageSizeSelect_grid').val();
            if(typeof pageSize != 'undefined'){
                ajaxUrl = urlreplace(ajaxUrl,'PageSize',pageSize);
            }
            ajaxGet(ajaxUrl, "containerType3");
        });
        $(".scoreList .component").each(function(){
            var check = $("li[data-id='"+$("#saveNewBtn").attr('data-li')+"']").parent().find("#"+$(this).attr('id')).length;
            if (check > 0){
                $(this).find('.addAction').html('<i class="glyphicon glyphicon-ok"></i>');
                $(this).toggleClass('componentSelected');
            }
        });
    });
</script>
