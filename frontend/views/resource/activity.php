<?php
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TGridView;
use common\services\learning\InvestigationService;

?>
<input type="hidden" name="sequence_number" id="sequence_number" value="<?=$params['sequence_number']?>">
<script>
    $(document).ready(function(){
        $("#basic-addon2").click(function() {
            reloadForm();
            return false;
        });
        $("#investigation_type").change(function(){
            reloadForm();
            return false;
        })
    });
    function reloadForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['resource/activity','component_id'=>$params['component_id'],'sequence_number'=>$params['sequence_number'],'domain_id'=>$params['domain_id'],'component_code'=>$params['component_code']])?>";
        ajaxUrl = urlreplace(ajaxUrl,'keyword',$('#searchText').val());
        ajaxUrl = urlreplace(ajaxUrl,'investigation_type',$('#investigation_type').val());
        app.get(ajaxUrl,function(r){
            if (r){
                $("#componentList").html(r);
            }
        });
    }
    function resetForm(){
        $("#searchText").val('');
    }
</script>
<style>
    .summary,#grid thead/*,#grid #pageSizeSelect_grid*/ {display: none;}
    #grid .table-bordered {border: 0 none;}
    #grid .table-responsive {overflow-x: inherit;}
    #grid .table-striped>tbody>tr:nth-of-type(odd) {background-color: transparent;}
    #grid .table>tbody>tr>td {border-top: 1px dotted #000; padding: 3px 8px; line-height: 24px;}
    #grid .table-bordered>tbody>tr>td {border: 1px dotted #000;}
    #grid .table-bordered>tbody>tr>td[colspan='1'] {border: 0 none;}
    #grid .table-bordered>tbody>tr>td font {display: block; float: left;}
    #grid .table-bordered>tbody>tr>td font:first-child { width: 30%;}
    #grid .table-bordered>tbody>tr>td font:nth-child(2) { width: 30%;}
    #grid .table-bordered>tbody>tr>td font:nth-child(3) { width: 30%;}
</style>
<div class="panel-body">
    <div class="col-md-12" style="border-bottom:1px solid #eee;">
        <form action="" method="get">
        <div class="pull-left">
            <?=Yii::t('common', 'component_type')?> ： <select id="investigation_type" name="investigation_type">
                <option value=""><?=Yii::t('common', 'select_{value}',['value'=>''])?></option>
                <option value="<?=InvestigationService::INVESTIGATION_TYPE_SURVEY?>" <?=isset($params['investigation_type']) && $params['investigation_type']==InvestigationService::INVESTIGATION_TYPE_SURVEY?'selected':''?>><?=Yii::t('frontend', 'questionnaire')?></option>
                <option value="<?=InvestigationService::INVESTIGATION_TYPE_VOTE?>" <?=isset($params['investigation_type']) && $params['investigation_type']==InvestigationService::INVESTIGATION_TYPE_VOTE?'selected':''?>><?=Yii::t('frontend', 'vote')?></option>
            </select>
        </div>
        <div class="input-group pull-right" style="width:250px;">
            <input type="text" class="form-control" placeholder="<?=Yii::t('frontend', 'fuzzy_search')?>" aria-describedby="basic-addon2" name="keyword" id="searchText" value="<?=isset($params['keyword']) ? $params['keyword'] : ''?>">
            <a class="btn input-group-addon" id="basic-addon2"><?=Yii::t('frontend', 'top_search_text')?></a>
        </div>
        </form>
        <div class="clearfix"></div>
    </div>
    <div class="clearfix"></div>
    <div role="tabpanel" style="margin-top: 10px;">
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active">
                <div class="AddtaskList">
                    <?php
                    $gridColumns = [
                        [
                            'header' => '',
                            'value' => function($model,$key){
                                return '<a href="javascript:;"><font><i class="glyphicon glyphicon-question-sign" style="color:#f48325"></i>&nbsp;'.$model->title.'</font><font>'.Yii::t('frontend', 'release_at').date('Y年m月d日',$model->created_at).'</font><font>'.$model->getCreatedBy().'</font></a><div class="addAction pull-right"><i class="glyphicon glyphicon-plus"></i></div><input type="hidden" class="componentid" name="activity[]" value="'.$model->kid.'"/>';
                            },
                            'contentOptions' => function($model, $key, $index, $column){
                                return [
                                    'id' => 'ware_'.$model->kid,
                                    'onclick' => 'ToggleComponent(this)',
                                    'class' => 'component clearfix',
                                ];
                            },
                            'format' => 'html',
                        ]
                    ];
                    ?>
                    <?= TGridView::widget([
                        'id'=>'grid',
                        'dataProvider' => $result,
                        'columns' => $gridColumns,
                        'pjax'=>false,
                        'pjaxSettings'=>[
                            'neverTimeout'=>true,
                        ]
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $(".pagination").on('click', 'a', function(e){
            e.preventDefault();
            var ajaxUrl = $(this).attr('href');
            app.get(ajaxUrl, function(r){
                if (r){
                    $("#componentList").html(r);
                }
            });
        });
        $(".AddtaskList .component").each(function(){
            var check = $("li[data-id='"+$("#saveBtn").attr('data-li')+"']").parent().find("#"+$(this).attr('id')).length;
            if (check > 0){
                $(this).find('.addAction').html('<i class="glyphicon glyphicon-ok"></i>');
                $(this).toggleClass('componentSelected');
            }
        });
    });
</script>
