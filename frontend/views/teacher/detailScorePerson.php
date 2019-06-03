<?php
/**
 * User: zhanglei
 * Date: 2015/8/12
 * Time: 13:02
 */

use yii\helpers\Url;
use yii\helpers\Html;
use components\widgets\TLinkPager;
use common\models\learning\LnComponent;
use common\models\learning\LnCourseComplete;

?>
<!-- 成绩详情的弹出窗口 -->
<?php
if ($header == 'show'){
?>
<div class="header">
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <h4 class="modal-title" id="myModalLabel"><?= Yii::t('frontend', '{value}_score',['value'=>$user->real_name])?></h4>
</div>
<?php
}
?>
<div class="content">
    <div class="panel-body">
        <p class="pull-right"><?= Yii::t('common', 'courseware_default_credit') ?>：<?= $total_score && $scoreComponentCount > 0 ? $total_score : '--' ?></p>
        <table class="table table-bordered table-hover table-striped table-center">
            <tbody>
            <tr>
                <td><?= Yii::t('frontend', 'matter') ?></td>
                <td><?=Yii::t('common', 'status')?></td>
                <td><?= Yii::t('common', 'examination_score') ?></td>
                <td><?= Yii::t('common', 'action') ?></td>
            </tr>
            <?php
            if(!empty($courseRes)){
                foreach ($courseRes as $res){
                    ?>
                    <tr>
                        <td align="left"><?php if($res['isCourseware']) { echo $res['item']->courseware_name;} else {echo  $res['item']->title ;}?></td>
                        <td>
                            <?php
                            if($res['componentCode'] == 'homework' && !empty($res['resComplete']->end_at) && ($res['resComplete']->end_at > $res['item']->finish_before_at)){
                                echo Yii::t('common', 'complete_status_overtime');
                            }else{
                                echo $res['status'];
                            }
                            ?>
                            <?/*=$res['is_retake']==LnCourseComplete::IS_RETAKE_YES?'(重学)':''*/?>
                        </td>
                        <td>
                            <?php
                            if(isset($res['score']) && !is_null($res['score']) && $res['score'] != '' && $res['isRecordScore']){
                                echo $res['score'];
                            }else{
                                echo '--';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if($res['isCourseware']){
                                echo '--';
                            }else {
                                //$componentModel = LnComponent::findOne($res['componentId']);
                                //$componentCode = $componentModel->component_code;

                                if($res['score'] != '--' && $res['resstatus']){
                                    if ($res['componentCode'] == 'investigation' && isset($res['item']->answer_type) && $res['item']->answer_type == '1'){
                                    ?>
                                    --
                                    <?php
                                    }elseif($res['componentCode'] === LnComponent::COMPONENT_CODE_HOMEWORK && !$showHomework){ ?>
                                        --
                                    <?php
                                    } else {
                                    ?>
                                    <a href="javascript:void(0)" data-log="1" data-activity-id="<?=$res['modRes']->courseactivity_id?>" data-mod-id="<?=$res['modRes']->mod_id?>" onclick="LoadCompleteInfo(this, '<?=$courseModel->kid?>','<?= $res['modResId'] ?>','<?= $res['itemId'] ?>','','<?= $res['componentCode']?>','<?=$userId?>');"><?= Yii::t('common', 'view_button') ?></a>
                                    <?php
                                    }
                                }else{
                                    echo '--';
                                }
                            } ?>
                        </td>
                    </tr>
                <?php }}?>
            </tbody>
        </table>
        <nav id="scorePersonPage" style="text-align: right;">
            <?php
            echo TLinkPager::widget([
                'id' => 'page',
                'pagination' => $pages,
                'displayPageSizeSelect'=>false
            ]);
            ?>
        </nav>
    </div>
</div>
<script>
    $(function(){
        $("#scorePersonPage .pagination").on('click', 'a', function(e){
            e.preventDefault();
            ajaxGet($(this).attr('href'), "scoreDetails");
        });
    });
    window._isPWatch = true;
</script>

  