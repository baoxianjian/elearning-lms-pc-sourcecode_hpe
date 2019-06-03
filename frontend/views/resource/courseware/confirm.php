<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/29
 * Time: 16:57
 */
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use components\widgets\TBreadcrumbs;
$componentName = str_replace('resource/','',$this->context->id);
$this->pageTitle = Yii::t('common', 'courseware_confirm');
$this->params['breadcrumbs'][] = ['label' => Yii::t('common','resource_management'), 'url' => ['/resource/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','courseware_management'),'url'=>['/resource/courseware/manage']];
$this->params['breadcrumbs'][] = $this->pageTitle;
$this->params['breadcrumbs'][] = '';
$ware = array();
?>
<style type="text/css">
    input.error {border: 1px solid #a94442!important;}
    label.error {color: #a94442!important;;}
    .glyphicon-remove{
        top: 3px;
    }
    .btn-danger{
        padding: 2px 8px;
    }
    table .filename,table .cwname,table .eaname{
        overflow: hidden;
        width: 140px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    #file_list input[type='text'] {border: 1px solid #ccc; max-width: 80%;}
</style>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<script type="text/javascript">
    function loadNext(){
        loadTab('<?=Url::toRoute([$this->context->id.'/setting'])?>', 'uploadCourse');
    }
    /*移出一行数据*/
    function removeRow(id, num){
        if ($("#file_list > tr").length == 2){
            app.showMsg('<?=Yii::t('frontend', 'list_less_one')?>');
            return false;
        }else{
            NotyConfirm('<?=Yii::t('common', 'operation_confirm')?>',  function(data){
                $("#row_"+id+'_'+num).remove();
                $("#file_"+num).remove();
                $("#file_name_"+num).remove();
            });
        }
    }
</script>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12 col-sm-12" id="uploadCourse">
            <div class="panel panel-default hotNews">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-retweet"></i> <?=Yii::t('common','courseware_confirm')?>
                </div>
                <div class="panel-body uploadCourse">
                    <?php $form = ActiveForm::begin([
                        'id' => 'confirm',
                        'method' => 'post',
                        'action' => Yii::$app->urlManager->createUrl([$this->context->id.'/confirm']),
                    ]); ?>
                    <input type="hidden" name="action" value="confirm">
                    <input type="hidden" name="domain_id" value="<?=$request['domain_id']?>">
                    <input type="hidden" name="LnCourseware[courseware_category_id]" value="<?=$request['courseware_category_id']?>">
                    <input type="hidden" name="LnCourseware[vendor]" value="<?=$request['vendor']?>">
                    <input type="hidden" name="LnCourseware[vendor_id]" value="<?=$request['vendor_id']?>">
                    <input type="hidden" name="LnCourseware[start_at]" value="<?=$request['start_at']?>">
                    <input type="hidden" name="LnCourseware[end_at]" value="<?=$request['end_at']?>">
                    <h4><?=Yii::t('common', 'you_will_upload_scorms_in_domain_{domain_id}',['domain_id'=>$model->getDomainNameByText($request['domain_id'])])?></h4><hr/>
                    <div style="display: none;">
                        <?php
                        if (!empty($tempArr)){
                            foreach ($tempArr as $key=>$val){
                                echo '<input type="hidden" name="file_id[]" id="file_'.$key.'" value="'.$val['file_id'].'">';
                                echo '<input type="hidden" name="file_name['.$val['file_id'].']" id="file_name_'.$key.'" value="'.$val['file_name'].'">';
                            }
                        }
                        ?>
                    </div>
                    <div class="uploadFileTable">
                        <table class="table noneBorder">
                            <tbody>
                            <tr>
                                <td width="20%"><?=Yii::t('common', 'courseware_category')?></td>
                                <td align="left">
                                    <?=$request['courseware_category_name']?>
                                </td>
                            </tr>
                            <tr>
                                <td><?=Yii::t('common', 'supplier')?></td>
                                <td align="left">
                                    <?php if (!empty($request['vendor'])) { echo $request['vendor']; }else {echo Yii::t('common', 'encrypt_mode_none'); } ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?=Yii::t('common', 'time_validity')?></td>
                                <td align="left">
                                    <?php if (empty($request['end_at']) && empty($request['start_at'])) { echo Yii::t('frontend', 'long_term'); } else if (!empty($request['start_at']) && empty($request['end_at'])){ echo $request['start_at']. Yii::t('common', 'to2').Yii::t('frontend', 'forever'); } else { ?><?=$request['start_at']?> <?=Yii::t('common','to2')?> <?=$request['end_at']?> <?php } ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="uploadFileTable courseSave">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td class="filename"><?=Yii::t('common','filename')?></td>
                                <td class="cwname"><?=Yii::t('common','courseware_name')?></td>
                                <td class="eaname"><?=Yii::t('common','entrance_address')?></td>
                                <td align="center"><?=Yii::t('common','courseware_time')?></td>
                                <td align="center"><?=Yii::t('common','courseware_default_credit')?></td>
                                <td align="center"><?=Yii::t('common','display_name')?></td>
                                <td align="center"><?=Yii::t('frontend','download')?></td>
                                <td align="center"><?=Yii::t('common','action')?></td>
                            </tr>
                            </thead>
                            <tbody id="file_list">
                            <tr style="display:none;">
                                <td colspan="7"><?=Yii::t('common', 'select_{value}',['value'=>Yii::t('common', 'upload_files')])?></td>
                            </tr>
                            <?php
                            if (!empty($tempArr)){
                                foreach ($tempArr as $i=>$t){
                                    $is_display_pc_checked = "";
                                    if ($t['is_display_pc']){
                                        $is_display_pc_checked = 'checked="checked"';
                                    }
                                    $is_display_mobile_checked = "";
                                    if ($t['is_display_mobile']){
                                        $is_display_mobile_checked = 'checked="checked"';
                                    }
                                    $is_allow_download_yes_checked = "";
                                    $is_allow_download_no_checked = "";
                                    if ($t['is_allow_download']){
                                        $is_allow_download_yes_checked = 'checked="checked"';
                                    }else{
                                        $is_allow_download_no_checked = 'checked="checked"';
                                    }
                            ?>
                            <tr id="row_<?=$t['file_id']?>_<?=$i?>">
                                <td class="filename" title="<?=$t['component_text']?>" title="<?=$t['file_name']?>"><?=$t['file_icon']?><?=$t['file_name']?></td>
                                <td class="cwname" title="<?=$t['courseware_name']?>">
                                    <input type="text" name="courseware_name[<?=$t['file_id']?>]" value="<?=$t['courseware_name']?>" />
                                </td>
                                <td class="eaname" title="<?=$t['entrance_address']?>">
                                    <input type="text" name="entrance_address[<?=$t['file_id']?>]" value="<?=$t['entrance_address']?>" />
                                </td>
                                <td align="center">
                                    <div class="courseware_time">
                                        <input type="text" name="courseware_time[<?=$t['file_id']?>]" value="<?=$t['courseware_time']?>" maxlength="5" /><?=Yii::t('frontend', 'time_minute')?>
                                    </div>
                                </td>
                                <td align="center">
                                    <div class="default_credit">
                                        <input type="text" name="default_credit[<?=$t['file_id']?>]" value="<?=$t['default_credit']?>" maxlength="5"/>
                                    </div>
                                </td>
                                <td align="center">
                                    <label><input type="checkbox" name="is_display_pc[<?=$t['file_id']?>]" value="1" <?=$is_display_pc_checked?> /><?=Yii::t('common', 'position_pc')?></label>
                                    <label><input type="checkbox" name="is_display_mobile[<?=$t['file_id']?>]" value="1" <?=$is_display_mobile_checked?> /><?=Yii::t('common', 'position_mobile')?></label>                                </td>
                                <td align="center">
                                    <label><input type="radio" name="is_allow_download[<?=$t['file_id']?>]" value="1" <?=$is_allow_download_yes_checked?> /><?=Yii::t('frontend','yes')?></label>
                                    <label><input type="radio" name="is_allow_download[<?=$t['file_id']?>]" value="0" <?=$is_allow_download_no_checked?> /><?=Yii::t('frontend','no')?></label>
                                </td>
                                <td align="center"><!--<button type="button" class="btn btn-primary" onclick="reviewWare('<?/*=$t['file_id']*/?>',this);">预览</button>--><button type="button" class="btn btn-danger" onclick="removeRow('<?=$t['file_id']?>',<?=$i?>);"><i class="glyphicon glyphicon-remove" style="color:#fff;"></i></button></td>
                            </tr>
                            <?php
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <hr/>
                    <?= Html::submitButton(Yii::t('common','previous'), ['class' => 'btn btn-success pull-left' , 'onclick' => 'document.getElementById(\'confirm\').action=\''.Url::toRoute([$this->context->id.'/common']).'\'']) ?>
                    <?= Html::submitButton(Yii::t('common','save'), ['id' => 'saveBtn', 'class' => 'btn btn-success pull-right']) ?>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
       $("#saveBtn").click(function(){
           var error = 0;
           $("input[name^=courseware_name]").each(function(){
               var index = $(this).index();
               var courseware_name = $(this).val().replace(/(^\s*)|(\s*$)/g,'');
               var xss_courseware_name = filterXSS(courseware_name);
               if (courseware_name == ""){
                   error ++;
                   $(this).addClass('error').focus();
                   app.showMsg('<?=Yii::t('frontend', '{value}_not_null',['value'=>Yii::t('common', 'courseware_name')])?>');
                   return false;
               }else if (app.stringLength(courseware_name) > 150) {
                   error ++;
                   $(this).addClass('error').focus();
                   app.showMsg('<?=Yii::t('frontend', '{value}_limit_50_word',['value'=>Yii::t('common', 'courseware_name')])?>');
                   return false;
               }else if (courseware_name != xss_courseware_name){
                   error ++;
                   $(this).add('error').focus();
                   app.showMsg('<?=Yii::t('frontend', '{value}_lillegal_char',['value'=>Yii::t('common', 'courseware_name')])?>');
                   return false;
               }else{
                   $.ajax({
                       url: '<?=Url::to(['/resource/courseware/check-courseware'])?>',
                       data: {courseware_name: courseware_name},
                       async: false,
                       dataType: 'json',
                       type: 'get',
                       success: function(e) {
                           if (e.result == 'success') {
                               /*检测通过*/
                               $(this).removeClass('error');
                           } else {
                               /*有重复名称课件*/
                               error++;
                               $("input[name^=courseware_name]").eq(index).addClass('error').focus();
                               app.showMsg('<?=Yii::t('frontend', 'courseware_name_isset')?>');
                               return false;
                           }
                       },
                       error: function(r){
                           /**/
                       }
                   });
               }
           });
           if (error > 0) return false;
           /*入口地址*/
           $("input[name^=entrance_address]").each(function(){
               var entrance_address = $(this).val().replace(/(^\s*)|(\s*$)/g,'');
               var xss_entrance_address = filterXSS(entrance_address);
               if (entrance_address != xss_entrance_address){
                   error ++;
                   $(this).addClass('error').focus();
                   app.showMsg('<?=Yii::t('frontend', '{value}_lillegal_char',['value'=>Yii::t('common', 'entrance_address')])?>');
                   return false;
               }
           });
           if (error > 0) return false;
       }) ;
    });
</script>
