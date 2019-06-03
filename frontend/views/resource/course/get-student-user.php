<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/5
 * Time: 10:27
 */
use common\helpers\TStringHelper;
use yii\helpers\Html;

?>
<?php
if (!empty($users)) {
?>
    <ul class="thumbList">
        <? foreach ($users as $u): ?>
            <li>
                <label style="display: block; width: 100%; height: 100%;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;">
                    <input type="checkbox" value="<?= $u['kid'] ?>" name="users[]" checked="checked"/>
                    <!--<img src="<?/*= TStringHelper::Thumb($u['thumb']) */?>" alt="scoreList1"/>-->
                    <p class="name"><?= Html::encode($u['real_name']) ?></p>
                    <p><?= Html::encode(TStringHelper::PositionName($u['position_name'])) ?></p>
                </label>
            </li>
        <? endforeach; ?>
    </ul>
    <div class="">
        <label><input type="checkbox" checked="checked" id="checkAll" /> <?= Yii::t('common', 'check_all') ?>/<?= Yii::t('common', 'canel') ?></label>
    </div>
    <script>
        $(function(){
            $("#courseTaskButton").show();
            $("#checkAll").click(function(e){
               // e.stopPropagation();
               if (!$(this).is(":checked")){
                   $(".thumbList input[type='checkbox']").prop('checked', false);
               }else{
                   $(".thumbList input[type='checkbox']").prop('checked', true);
               }
            });
        });
    </script>
<?php
}else{
?>
<p style="margin: 25px 0;"><?= Yii::t('frontend', 'no_students_can_be_assigned') ?>!</p>
<script>
    $(function(){
        $("#courseTaskButton").hide();
    });
</script>
<?php
}
?>