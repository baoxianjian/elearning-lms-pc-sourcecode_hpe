<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/24
 * Time: 15:05
 */
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use components\widgets\TGridView;
use common\services\learning\ComponentService;
use common\models\learning\LnExamination;
use common\models\learning\LnExaminationPaperCopy;

?>

<?php
if ($dataProvider['pages']->totalCount > 0){
    ?>
    <ul class="component-list" style="min-height: 311px;">
        <?php
        $componentService = new ComponentService();
        $component = $componentService->getCompoentByComponentKid($params['component_id']);
        $icon = !empty($component->icon) ? $component->icon : '';
        $action_url = !empty($component->action_url) ? Url::toRoute([$component->action_url]) : '';
        foreach ($dataProvider['data'] as $item){
//                            $LnExamination = new LnExamination();
//                            $created_by = $LnExamination->getExaminationCreateBy($item->created_by);
            $paperModel = LnExaminationPaperCopy::findOne($item->examination_paper_copy_id);
            ?>
            <li id="ware_<?=$item->kid?>" data-id="<?=$item->kid?>" data-title="<?=$item->title?>" onclick="ToggleComponent(this);" class="component clearfix">
                <a href="javascript:;" class="pull-left component-tbody" data-uri="<?=$action_url?>">
                    <font><?=$icon?>&nbsp;<?=$item->title?></font>
                    <!--                                    <font>--><?//=$item->examination_mode == LnExamination::EXAMINATION_MODE_EXERCISE ? '练习' : '测试'?><!--</font>-->
                    <!--                                    <font>--><?//=$created_by?><!--</font>-->
                    <font><?= Yii::t('common', 'examination_question_number') ?>：<?=$paperModel->examination_question_number?></font>
                </a>
                <div class="addAction pull-right">
                    <i class="glyphicon glyphicon-plus"></i>
                </div>
                <input type="hidden" class="componentid" data-modnum="<?=$params['sequence_number']?>" data-restitle="<?=$item->title?>" data-compnenttitle="<?=$component->title?>" data-completerule="<?=$component->complete_rule?>" data-isscore="<?=$component->is_record_score?>"  name="resource[<?=$params['sequence_number']?>][activity][examination][]" value="<?=$item->kid?>"/>
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
<script>
    $(".pagination").on('click', 'a', function(e){
        e.preventDefault();
        //$("#saveBtn").trigger('click');翻页有bug
        $.get($(this).attr('href'), function(data){
            if (data){
                $("#rightList").html(data);
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
</script>
