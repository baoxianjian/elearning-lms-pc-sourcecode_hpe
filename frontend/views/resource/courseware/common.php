<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/29
 * Time: 16:57
 */
use yii\helpers\Url;
use yii\helpers\Html;
use components\widgets\TBreadcrumbs;
use yii\widgets\ActiveForm;
use components\widgets\TDatePicker;

$componentName = str_replace('resource/','',$this->context->id);
$this->pageTitle = Yii::t('common', 'upload_courseware');
$this->params['breadcrumbs'][] = ['label' => Yii::t('common','resource_management'), 'url' => ['/resource/index']];
$this->params['breadcrumbs'][] = ['label'=>Yii::t('common','courseware_management'),'url'=>['/resource/courseware/manage']];
$this->params['breadcrumbs'][] = $this->pageTitle;
$this->params['breadcrumbs'][] = '';
$ListRoute = '/../resource/courseware/empty';
?>
<?=Html::jsFile('/static/frontend/js/xss.js')?>
<style>
    .label-domain {width: 30%; font-size: 14px;}
    .label-domain input {width: auto; height: auto;}
    .field-lncourseware-courseware_category_id {margin:0;}
    #lncourseware-courseware_category_id,#lncourseware-supplier {width: 50%;}
    .field-lncourseware-start_at,.field-lncourseware-end_at {float: left; margin-bottom: 0px;}
    #lncourseware-start_at,#lncourseware-end_at {width: 100px; height: 30px;margin-right: 0px;}
    .field-lncourseware-span {height: 30px; line-height: 30px;}
    #jsTree>div:nth-child(2){overflow-x:auto;}
</style>
<script type="text/javascript">
    $(document).ready(function(){
        loadTree();
    });
    var select_node = '<?=$request['tree_node_id']?>';
    function jsTreeBack(target, data, textStatus){
        $("#" + target).html(data);
        if (typeof select_node != 'undefined' && select_node != ""){
            var t = setInterval(function() {
                var lilen = $("#jsTree_tree").find('li').length;
                if (lilen > 1){
                    $("#jsTree_tree li.jstree-node").attr('aria-selected', 'false');
                    $("#jsTree_tree a.jstree-anchor").removeClass('jstree-clicked');
                    $("#" + select_node).attr('aria-selected', 'true').find("a#" + select_node + '_anchor').addClass('jstree-clicked');
                    $("#jsTree_tree_changed_result").val('["' + select_node + '"]');
                    $("#jsTree_tree_selected_result").val('["' + select_node + '"]');
                    $("#jsTree_tree").attr('aria-activedescendant', select_node);
                    clearInterval(t);
                }
            },500);
        }
    }
    function TreeCallback(){

    }
    function loadTree(){
        var ajaxUrl = "<?=Url::toRoute(['tree-node/tree',
        'TreeType'=>'courseware-category',
        'ContentName'=>'tree-node',
        'ListRoute'=> $ListRoute])?>";
        ajaxGet(ajaxUrl, "jsTree", jsTreeBack);
    }
    function checkFrm(){
        var len = $("input[name='domain_id[]']:checked").length;
        if (len == 0){
            app.showMsg('<?=Yii::t('frontend', 'choose_courseware_domain')?>');
            return false;
        }
        var courseware_category_id=$("#jsTree_tree_selected_result").val();
        if (typeof courseware_category_id == 'undefined'){
            app.showMsg('<?=Yii::t('frontend', 'loading')?>');
            return false;
        }
        courseware_category_id = eval(courseware_category_id);//转换成数组
        if (courseware_category_id[0] == -1){
            app.showMsg("<?=Yii::t('common', 'select_{value}',['value'=>Yii::t('common','courseware_category')])?>");
            return false;
        }
        if (courseware_category_id.length > 1){
            app.showMsg("<?=Yii::t('frontend', 'can_choose_one_{value}',['value'=>Yii::t('common','catelog')])?>");
            return false;
        }
        $("#lncourseware-courseware_category_id").val(courseware_category_id[0]);
        $("#supplierDiv").empty();
        var supplier_json = common_supplier.get();
        if (typeof supplier_json != 'undefined' && typeof supplier_json['kid'] != 'undefined'){
            var supplier_title = supplier_json['title'].replace(/(\(.*?\))/g, '');
            $("#supplierDiv").append('<input type="hidden" name="LnCourseware[vendor]" value="'+supplier_title+'" /><input type="hidden" name="LnCourseware[vendor_id]" value="'+supplier_json['kid']+'" />');
        }else{
            if ($("#supplier").val() != ""){
                app.showMsg("<?=Yii::t('frontend', 'input_supplie')?>");
                return false;
            }
            $("#supplierDiv").append('<input type="hidden" name="LnCourseware[vendor]" value="" /><input type="hidden" name="LnCourseware[vendor_id]" value="" />');
        }
    }
</script>
<div class="container">
    <div class="row">
        <?= TBreadcrumbs::widget([
            'tag' => 'ol',
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <div class="col-md-12 col-sm-12"  id="uploadCourse">
            <div class="panel panel-default hotNews">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-cloud-upload"></i><?=Yii::t('common', 'upload_files')?>
                </div>
                <?php $form = ActiveForm::begin([
                    'id' => 'common',
                    'method' => 'post',
                    'action' => Yii::$app->urlManager->createUrl([$this->context->id.'/confirm']),
                ]); ?>
                    <input type="hidden" name="action" value="common">
                    <textarea name="file_info" style="display: none;"><?=$file_info?></textarea>
                    <div class="panel-body uploadCourse">
                        <h4><?=Yii::t('common', 'please_input_commoninfo_at_this_panel')?></h4>
                        <hr/>
                        <div class="uploadFileTable">
                            <table class="table noneBorder">
                                <tbody>
                                <tr>
                                    <td width="100px"><?=Yii::t('common', 'relate_{value}', ['value'=>Yii::t('common','domain')])?></td>
                                    <td align="left">
                                        <div class="domain_list">
                                        <?php
                                        foreach ($domain as $key=>$val){
                                            ?>
                                            <label class="label-domain"><input type="checkbox" name="domain_id[]" value="<?=$val->kid?>" <?php echo in_array($val->kid, $request['domain_id']) ? 'checked':'';?>><?=Html::encode($val->domain_name)?></label>
                                            <?php
                                        }
                                        ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="100px"><?=Yii::t('common', 'courseware_category')?></td>
                                    <td align="left">
                                        <?= $form->field($model, 'courseware_category_id')->hiddenInput()->label(false)?>
                                        <div id="jsTree"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="100px"><?=Yii::t('common', 'time_validity')?></td>
                                    <td align="left">
                                        <?= $form->field($model, 'start_at')->textInput(['data-type'=>'rili'])->label(false); ?>
                                        <span class="field-lncourseware-span"><?=Yii::t('common', 'to2')?></span>
                                        <?= $form->field($model, 'end_at')->textInput(['data-type'=>'rili'])->label(false); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="100px"><?=Yii::t('common', 'supplier')?></td>
                                    <td align="left">
                                        <input type="text" id="supplier" class="form-control" style="width: 100%;" onclick="this.value=this.value.replace(/\s+/g,'');" placeholder="<?=Yii::t('frontend', 'name_and_code')?>" data-url="<?=Url::toRoute(['/resource/courseware/get-vendor'])?>" autocomplete="off">
                                        <div id="supplierDiv"></div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <hr/>
                        <?= Html::button(Yii::t('common','previous'), ['id' => 'previous', 'class' => 'btn btn-success pull-left', 'name' => 'prev-button', 'type'=>'submit']) ?>
                        <?= Html::button(Yii::t('common','next'), ['type'=>'submit', 'onclick' => 'return checkFrm();','id'=>'nextButton','class' => 'btn btn-success pull-right']) ?>
                    </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
<script>
<?php
if (!empty($supplier)){
?>
window.common_supplier = app.queryList("#supplier", '<?=$supplier?>');
<?php
}else{
?>
window.common_supplier = app.queryList("#supplier");
<?php
}
?>
    jQuery(document).ready(function () {
        jQuery('#lncourseware-start_at').attr('readonly', 'readonly');
        jQuery('#lncourseware-end_at').attr('readonly', 'readonly');
        $("#previous").on('click', function(e){
            $("#supplierDiv").empty();
            var supplier_json = common_supplier.get();
            if (typeof supplier_json != 'undefined' && typeof supplier_json['kid'] != 'undefined'){
                var supplier_title = supplier_json['title'].replace(/(\(.*?\))/g, '');
                $("#supplierDiv").append('<input type="hidden" name="LnCourseware[vendor]" value="'+supplier_title+'" /><input type="hidden" name="LnCourseware[vendor_id]" value="'+supplier_json['kid']+'" />');
            }else{
                $("#supplierDiv").append('<input type="hidden" name="LnCourseware[vendor]" value="" /><input type="hidden" name="LnCourseware[vendor_id]" value="" />');
            }
            document.getElementById('common').action = '<?=Url::toRoute([$this->context->id.'/upload'])?>';
        });
    });
</script>
