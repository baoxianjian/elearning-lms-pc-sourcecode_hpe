<?php
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TGridView;
use common\models\learning\LnInvestigation;
use common\services\learning\InvestigationService;
use common\models\learning\LnCourse;
$isCourseType = $params['isCourseType'];
?>
<style>
    .searchForm, .search-result {margin: 8px;}
    .component-list li.component-thead {border: 1px solid #ddd; background-color: #eee !important;}
    .component-list li.component-thead a {color: #333;}
    .component-list li:nth-of-type(odd) {background-color: transparent;}
    .component-list li {border: 1px solid #ddd; padding: 3px 8px; line-height: 24px;}
    .component-list li:not(:first-child) {border-top: 0 none;}
    .component-list li .addAction {width: 10%; text-align: center;}
    .component-list .component-tbody {display: block; width: 90%; border: 0 none;}
    .component-list .component-tbody font {display: block; float: left; text-overflow: ellipsis;}
    .component-list .component-tbody font:first-child { width: 35%;}
    .component-list .component-tbody font:nth-child(2) { width: 30%;}
    .component-list .component-tbody font:nth-child(3) { width: 30%;}
    .createnew{float:left; margin-left: 4%;}
</style>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?= Yii::t('common', '{pop}_ln_component',['pop'=>Yii::t('common','investigation')]) ?></h4>
</div>
<div class="content">
    <input type="hidden" name="sequence_number" id="sequence_number" value="<?=$params['sequence_number']?>">
    <?php
    if ($params['from'] != 'teacher'){
    ?>
    <a class="btn btn-success pull-left createnew" href="<?=Yii::$app->urlManager->createUrl(['investigation/index'])?>" target="_blank"><?= Yii::t('frontend', 'build') ?></a>
    <?php
    }
    ?>
    <div class="panel-body">
        <div class="col-md-12 searchForm">
            <div class="pull-left">
                <?= Yii::t('common', 'component_type') ?>： <select id="investigation_type" name="investigation_type">
                    <option value=""><?= Yii::t('common', 'select_{value}',['value'=>'']) ?></option>
                    <option value="<?=InvestigationService::INVESTIGATION_TYPE_SURVEY?>" <?=isset($params['investigation_type']) && $params['investigation_type']==InvestigationService::INVESTIGATION_TYPE_SURVEY?'selected':''?>><?= Yii::t('frontend', 'questionnaire') ?></option>
                    <option value="<?=InvestigationService::INVESTIGATION_TYPE_VOTE?>" <?=isset($params['investigation_type']) && $params['investigation_type']==InvestigationService::INVESTIGATION_TYPE_VOTE?'selected':''?>><?= Yii::t('frontend', 'vote') ?></option>
                </select>
            </div>
            <div class="input-group pull-right" style="width:250px;">
                <input type="text" class="form-control" placeholder="<?= Yii::t('frontend', 'fuzzy_search') ?>" aria-describedby="basic-addon2" name="keyword" id="searchText" value="<?=isset($params['keyword']) ? $params['keyword'] : ''?>">
                <a class="btn input-group-addon" id="basic-addon2"><?= Yii::t('frontend', 'top_search_text') ?></a>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="panel-default search-result">
        <?php
        if ($dataProvider['pages']->totalCount > 0){
        ?>
        <ul class="component-list">
            <li class="component-thead clearfix">
                <a href="javascript:;" class="pull-left component-tbody">
                    <font><?=Yii::t('common', 'investigation_title')?></font>
                    <font><?=Yii::t('common', 'investigation_type')?></font>
                    <font><?=Yii::t('common', 'created_by_name')?></font>
                </a>
                <div class="addAction pull-right"><?= Yii::t('common', 'action') ?></div>
            </li>
            <?php
            $componentService = new \common\services\learning\ComponentService();
            $component = $componentService->getCompoentByComponentKid($params['component_id']);
            $icon = !empty($component->icon) ? $component->icon : '';
            $action_url = !empty($component->action_url) ? Url::toRoute([$component->action_url]) : '';
            foreach ($dataProvider['data'] as $item){
                $investigationModel = new LnInvestigation();
                $created_by = $investigationModel->getCreatedBy($item->created_by);
            ?>
            <li id="ware_<?=$item->kid?>" data-id="<?=$item->kid?>" data-title="<?=$item->title?>" onclick="ToggleComponent(this);" class="component clearfix">
                <a href="javascript:;" class="pull-left component-tbody" data-uri="<?=$action_url?>">
                    <font><?=$icon?>&nbsp;<?=$item->title?></font>
                    <font><?=$item->investigation_type == '0' ? Yii::t('frontend', 'questionnaire') : Yii::t('frontend', 'vote');?></font>
                    <font><?=Html::encode($created_by)?></font>
                </a>
                <div class="addAction pull-right">
                    <i class="glyphicon glyphicon-plus"></i>
                </div>
                <input type="hidden" class="componentid" data-modnum="<?=$params['mod_num']?>" data-restitle="<?=$item->title?>" data-compnenttitle="<?=$component->title?>" data-completerule="<?=$component->complete_rule?>" data-isscore="<?=$component->is_record_score?>" name="resource[<?=$params['sequence_number']?>][activity][investigation][]" value="<?=$item->kid?>"/>
            </li>
            <?php
            }
            ?>
        </ul>
        <div class="clearfix"></div>
        <div class="col-md-12">
            <nav class="text-right">
                <?php
                echo \components\widgets\TLinkPager::widget([
                    'id' => 'page',
                    'pagination' => $dataProvider['pages'],
                    'displayPageSizeSelect'=>false
                ]);
                ?>
            </nav>
        </div>
        <div class="clearfix"></div>
        <?php
        }else{
        ?>
        <p><?= Yii::t('common', 'no_data') ?>！</p>
        <?php
        }
        ?>
    </div>
    </div>
</div>
<div class="actions">
    <?= Html::button( Yii::t('frontend', 'choose_component'), ['id' => 'saveBtn', 'class' => 'btn btn-primary']) ?>
</div>
<script>
    $(function(){
        $("#basic-addon2").click(function() {
            reloadForm();
        });
        $("#investigation_type").change(function(){
            reloadForm();
        });
        $("#searchText").keypress(function(event){
            var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
            if (keyCode == 13){
                return false;
            }
        });
        $(".pagination").on('click', 'a', function(e){
            e.preventDefault();
            var ajaxUrl = $(this).attr('href');
            app.get(ajaxUrl, function(r){
                if (r){
                    $("#addModal").html(r);
                }
            });
        });
        $(".component-list .component").each(function(){
            var check = $("li[data-id='"+$("#addModal").attr('data-li')+"']").parent().find("#"+$(this).attr('id')).length;
            if (check > 0){
                $(this).find('.addAction').html('<i class="glyphicon glyphicon-ok"></i>');
                $(this).toggleClass('componentSelected');
            }
        });

        $("#saveBtn").click(function(){
            var liSelected = $('#addModal').find('.componentSelected');
            if(liSelected.length == 0){
                //app.showMsg("请选择资源！");
                //return false;
            }
            var url = null;
            var code = $("#addModal").attr('data-code');
            var type = $("#addModal").attr('data-type');
            var componentId = $("#addModal").attr('data-componentid');
            var item = $("#addModal").attr('data-id');
            $('#addModal').find('.component').each(function(){
                if ($(this).hasClass('componentSelected')){
                    if ($('.ulEditContent').eq(item-1).find("#"+$(this).attr('id')).length > 0){
                        /*列表已经存在*/
                    }else {
                        var data_uri = $(this).find("a").attr('data-uri');
                        if (typeof data_uri != 'undefined' && data_uri.length > 0){
                            url = $(this).find("a").attr('data-uri');
                        }
                        $(this).find("a").attr('onclick', 'loadModalFormData(\'addModal\',\''+url+'?component_id='+componentId+'&sequence_number='+item+'&domain_id='+domain_id+'&component_code='+code+'\',this,\''+type+'\',\''+code+'\',0);');

                        var btn = '<a href="javascript:;" class="glyphicon glyphicon-remove del" title="<?= Yii::t('common', 'delete_button') ?>"></a>';
                        <?php 
                        if ($isCourseType == LnCourse::COURSE_TYPE_ONLINE){
                        ?>
                        btn += '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'/resource/component/config.html?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&component_code=<?=$params['component_code']?>&id='+$(this).attr('data-id')+'&title='+$(this).attr('data-title')+'\',this,\''+type+'\',\''+code+'\',\'0\');"><?= Yii::t('frontend', 'configuration') ?></a>';
                        <?php
                        }else if ($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE){
                    	?>
                    	btn += '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'/resource/component/config.html?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&component_code=<?=$params['component_code']?>&id='+$(this).attr('data-id')+'&title='+$(this).attr('data-title')+'\',this,\''+type+'\',\''+code+'\',\'0\');"><?= Yii::t('frontend', 'configuration') ?></a>';
                    	<?php                                	
                        }
                    	?>
                        
                        $(this).unbind('click').find('.addAction').html(btn);
                        var html = '<li id="'+$(this).attr('id')+'" class="component componentSelected"  data-component="<?=$params['component_code']?>">'+$(this).html()+'</li>';
                        var sequence = $('.ulEditContent').eq(item-1).find(".componentSelected").length;
                        html = html.replace('[]', '['+(sequence+1)+']');
                        $('.ulEditContent').eq(item-1).append(html);
                        html = '';
                    }
                }else{
                    $('.ulEditContent').eq(item-1).find("#"+$(this).attr("id")).remove();
                }
            });
            $("li[data-empty='"+$("#addModal").attr('data-li')+"_empty']").remove();
            $("#addModal").attr('data-id','').attr('data-li','').attr('data-code','').attr('data-type','');
            app.hideAlert($("#addModal"));
            $('#addModal').empty();
        });
    });

    function reloadForm()
    {
        var ajaxUrl = "<?=Url::toRoute(['resource/component/investigation','component_id'=>$params['component_id'],'sequence_number'=>$params['sequence_number'],'domain_id'=>$params['domain_id'],'component_code'=>$params['component_code'], 'isCourseType' => $isCourseType,'is_copy'=>$params['is_copy'],'companyId'=>$params['companyId']])?>";
        /*关键词查询*/
        var error = 0;
        var keywords = $("#searchText").val().replace(/(^\s*)|(\s*$)/g,'');
        var xss_keywords = filterXSS(keywords);
        if (keywords != xss_keywords){
            error ++;
            $("#searchText").focus();
            app.showMsg('<?= Yii::t('common', 'input_xss_error') ?>');
            return false;
        }
        if (error > 0) return false;
        ajaxUrl = urlreplace(ajaxUrl,'keyword',encodeURIComponent(keywords));
        ajaxUrl = urlreplace(ajaxUrl,'investigation_type',$('#investigation_type').val());
        app.get(ajaxUrl,function(r){
            if (r){
                $("#addModal").html(r);
            }
        });
    }
    function resetForm(){
        $("#searchText").val('');
    }
</script>
