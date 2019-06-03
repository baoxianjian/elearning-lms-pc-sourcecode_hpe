<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use components\widgets\TGridView;
use common\services\learning\ComponentService;
use common\models\learning\LnCourse;
use common\models\learning\LnCourseware;
$isCourseType = $params['isCourseType'];
?>
<style>
    .searchForm, .search-result {margin: 8px;}
    .component-list li {border: 1px solid #ddd; padding: 3px 8px; line-height: 24px;}
    .component-list li:not(:first-child) {border-top: 0 none;}
    .component-list .component-tbody {display: block; width: 90%; border: 0 none;}
    .component-list li .addAction {width: 8%; text-align: center;}
</style>
<?php
if ($params['component_code'] != 'html') {
?>
<div class="header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?= Yii::t('common', '{pop}_ln_component',['pop'=>Yii::t('frontend','add')]) ?></h4>
</div>
<?php
}
?>
<div class="content">
    <div class="modal-body-view" id="componentList">
    <input type="hidden" name="sequence_number" id="sequence_number" value="<?=$sequence_number?>">
    <?php
    if ($params['from'] != 'teacher'){
    ?>
    <a class="btn btn-success pull-left" href="<?=Yii::$app->urlManager->createUrl(['resource/courseware/upload','code'=> $component->component_code])?>" target="_blank"><?= Yii::t('frontend', 'build') ?></a>
    <?php
    }
    ?>
    <div class="form-inline pull-right searchForm">
        <?php $form = ActiveForm::begin([
            'id' => 'searchForm',
            'method' => 'get',
        ]); ?>
        <input type="hidden" name="component_code" value="<?=isset($params['component_code']) ? $params['component_code'] : ''?>" />
        <div class="form-control" style="width: auto!important;border: 0 none; background-color: transparent!important;box-shadow: none; -webkit-box-shadow: none;">
            <?=Yii::t('common', 'display_name')?>:
            <label><input type="checkbox" name="is_display_pc" id="is_display_pc" value="1" <?=$params['is_display_pc']==LnCourseware::DISPLAY_PC_YES?'checked' :''?> /> <?=Yii::t('common', 'position_pc')?></label>
            <label><input type="checkbox" name="is_display_mobile" id="is_display_mobile" value="1" <?=$params['is_display_mobile']==LnCourseware::DISPLAY_MOBILE_YES?'checked' :''?> /> <?=Yii::t('common', 'position_mobile')?></label>
        </div>
        <input type="text" class="form-control" id="searchText" name="courseware_name" placeholder="<?= Yii::t('common', 'list_code') ?>/<?= Yii::t('common', 'audience_name') ?>/<?= Yii::t('common', 'supplier') ?>" value="<?=isset($params['courseware_name'])?$params['courseware_name']:''?>" style="width: auto!important;" />
        <?= Html::button(Yii::t('common', 'search'), ['id'=>'searchBtn','class' => 'btn btn-primary']) ?>
        <?= Html::button(Yii::t('common', 'reset'), ['onclick'=>'resetForm1()','class' => 'btn btn-default']) ?>
        <?php ActiveForm::end(); ?>
    </div>
    <div style="clear: both"></div> 
    <div class="panel-default search-result">
        <?php
        if ($dataProvider['pages']->totalCount > 0){
            $componentService = new ComponentService();
            $component = $componentService->getCompoentByComponentKid($params['component_id']);
            $icon = !empty($component->icon) ? $component->icon : '';
            $action_url = !empty($component->action_url) ? Url::toRoute([$component->action_url]) : Url::toRoute(['/resource/coursewares']);
        ?>
        <ul class="component-list">
            <?php
            foreach ($dataProvider['data'] as $item){
            ?>
            <li id="ware_<?=$item->kid?>" data-id="<?=$item->kid?>" data-title="<?=$item->courseware_name?>" onclick="ToggleComponent(this);" class="component clearfix">
                <a href="javascript:;" data-uri="<?=$action_url?>" class="pull-left component-tbody"><?=$icon?>&nbsp;<?=$item->courseware_name?></a>
                <div class="addAction pull-right"><i class="glyphicon glyphicon-plus"></i></div>
                <input type="hidden" class="componentid" data-modnum="<?=$sequence_number?>"  data-restitle="<?=$item->courseware_name?>" data-compnenttitle="<?=$component->title?>" data-completerule="<?=$component->complete_rule?>" data-isscore="<?=$component->is_record_score?>"   name="resource[<?=$sequence_number?>][coursewares][<?=$component->component_code?>][]" value="<?=$item->kid?>"/>
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
    <div class="c"></div>
</div>

<div class="actions" style="<?=$params['component_code'] == 'html'?'display:none;':''?>">
    <?= Html::button( Yii::t('frontend', 'choose_component'), ['id' => 'saveBtn', 'class' => 'btn btn-primary']) ?>
</div>

<script>
    var ajaxBox = '<?=$params['component_code'] == 'html' ? 'containerType3' : 'addModal'?>';
    $(document).ready(function(){
        $("#searchBtn").click(function() {
            reloadForm1();
        });
        $("#searchText").keypress(function(event){
            var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
            if (keyCode == 13){
                return false;
            }
        });
        $(".pagination").on('click', 'a', function(e){
            e.preventDefault();
            $.get($(this).attr('href'), function(data){
                if (data){
                    $("#"+ajaxBox).html(data);
                }
            });
        });
        $(".component-list .component").each(function(){
            var componentArea = $("li[data-id='"+$("#addModal").attr('data-li')+"']").parents(".ulEditContent");
            if (componentArea.find("li#"+$(this).attr("id")).length > 0){
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
                        $(this).find("a").attr('onclick', 'loadModalFormData(\'addModal\',\''+url+'?component_id='+componentId+'&sequence_number='+item+'&domain_id='+domain_id+'&component_code='+code+'&typeno='+$(".htmlType").val()+'\',this,\''+type+'\',\''+code+'\',0);');
                        var btn = '<a href="javascript:;" class="glyphicon glyphicon-remove del" title="<?= Yii::t('common', 'delete_button') ?>"></a>';
                        <?php 
                        //if ($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE && in_array($params['component_code'], $is_setting_component)){
                        if ($isCourseType == LnCourse::COURSE_TYPE_FACETOFACE){
                        ?>
                        btn += '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'/resource/component/config.html?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&component_code=<?=$params['component_code']?>&isCourseType=<?=$isCourseType?>&id='+$(this).attr('data-id')+'&title='+encodeURI($(this).attr('data-title'))+'\',this,\''+type+'\',\''+code+'\',\'0\');"><?= Yii::t('frontend', 'configuration') ?></a>';
                        <?php
                        }else if ($isCourseType == LnCourse::COURSE_TYPE_ONLINE){
                        ?>
                        btn += '<a href="###" class="del_config" onclick="loadModalFormData(\'addModal\',\'/resource/component/config.html?component_id=<?=$params['component_id']?>&sequence_number=<?=$params['sequence_number']?>&domain_id=<?=$params['domain_id']?>&component_code=<?=$params['component_code']?>&isCourseType=<?=$isCourseType?>&id='+$(this).attr('data-id')+'&title='+encodeURI($(this).attr('data-title'))+'\',this,\''+type+'\',\''+code+'\',\'0\');"><?= Yii::t('frontend', 'configuration') ?></a>';
                        <?php
                        }
                        ?>
						<?php
                        if (in_array($params['component_code'], $is_setting_component)){
                        ?>
                        if ($("#finalscorelist").children().length > 0) {
                            app.showMsg('<?= Yii::t('frontend', '{value}_reset',['value'=>Yii::t('frontend','weight_for_score')]) ?>！');
                            $("#finalscorelist").empty();
                        }
                        <?php
                        }
                        ?>
                        $(this).unbind('click').find('.addAction').html(btn);
                        var html = '<li id="'+$(this).attr('id')+'" class="component componentSelected" data-component="<?=$params['component_code']?>">'+$(this).html()+'</li>';
                        var sequence = $('.ulEditContent').eq(item-1).find(".componentSelected").length;
                        html = html.replace('[]', '['+(sequence+1)+']');
                        $('.ulEditContent').eq(item-1).append(html);
                        html = '';
                    }
                }else{
                    $('.ulEditContent').eq(item-1).find("#"+$(this).attr("id")).remove();
                    var ipos = $(this).attr("id").indexOf("_");
                    var tempid = $(this).attr("id").substring(ipos,$(this).attr("id").length);

                    $('#con_'+(item)+tempid).remove();
                    //$('#finalscorelist').empty();

                    <?php
                    if (in_array($params['component_code'], $is_setting_component)){
                    ?>
                    if ($("#finalscorelist").children().length > 0) {
                        app.showMsg('<?= Yii::t('frontend', '{value}_reset',['value'=>Yii::t('frontend','weight_for_score')]) ?>！');
                        $("#finalscorelist").empty();
                    }
                    <?php
                    }
                    ?>
                }
            });
            $("li[data-empty='"+$("#addModal").attr('data-li')+"_empty']").remove();
            $("#addModal").attr('data-id','').attr('data-li','').attr('data-code','').attr('data-type','');
            app.hideAlert($("#addModal"));
            $('#addModal').empty();
        });
    });
    function reloadForm1()
    {
        var ajaxUrl = "<?=Url::toRoute(['resource/component/courseware','component_id'=>$component_id,'sequence_number'=>$sequence_number,'domain_id'=>$domain_id, 'component_code' => $params['component_code'], 'isCourseType' => $isCourseType,'is_copy'=>$params['is_copy'],'companyId'=>$params['companyId']])?>";
        var pagesize = $('#pageSizeSelect_grid').val();
        if (typeof pagesize == 'undefined') pagesize = 10;
        ajaxUrl = urlreplace(ajaxUrl,'PageSize', pagesize);
        var is_display_pc = 0;
        if (typeof $("#is_display_pc:checked").val() != 'undefined'){
            is_display_pc = 1;
        }
        var is_display_mobile = 0;
        if (typeof $("#is_display_mobile:checked").val() != 'undefined'){
            is_display_mobile = 1;
        }
        ajaxUrl = urlreplace(ajaxUrl, 'is_display_pc', is_display_pc);
        ajaxUrl = urlreplace(ajaxUrl, 'is_display_mobile', is_display_mobile);
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
        ajaxUrl = urlreplace(ajaxUrl,'courseware_name',encodeURIComponent($('#searchText').val()));
        $.get(ajaxUrl, function(html){
            $("#"+ajaxBox).html(html);
        });
    }
    function resetForm1(){
        $("#is_display_pc").attr('checked', false);
        $("#is_display_mobile").attr('checked', false);
        $("#searchText").val('');
    }
</script>
