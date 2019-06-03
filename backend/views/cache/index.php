<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2016/5/3
 * Time: 17:11
 */
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$ContentTanslateName = Yii::t('backend', 'cache_clean');

$this->params['breadcrumbs'][] = $ContentTanslateName;
?>
<?= Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js') ?>
<script>
    $(document).ready(function () {
        $("#btn_clean").click(
            function () {
                $("#tip").hide();
                $("#loading").show();

                var cache_data = $("input[name='cache[data]']").is(':checked');
                var cache_static = $("input[name='cache[static]']").is(':checked');

                $.post('<?=Url::toRoute('cache/clean')?>', {
                    "data": cache_data ? 1 : 0,
                    "static": cache_static ? 1 : 0
                }, function (data) {
                    $("#loading").hide();
                    $("#tip").text(data.result).show();
                });
            }
        );
    });
</script>
<div id="content-body">
    <table width="100%" border="0">
        <tr>
            <td >
                <div class="form-group">
                    <label class="control-label"><?=Yii::t('backend', 'please_choose_delete_cache')?></label>
                    <div><label><input type="checkbox" name="cache[data]" value="0" checked><?=Yii::t('backend', 'data_cache')?></label>&nbsp;&nbsp;<label><input type="checkbox" name="cache[static]" value="1" checked><?=Yii::t('backend', 'static_data_cache')?></label></div>
                </div>

            </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    <img id="loading" style="display: none" src="/static/common/images/loading.gif">
                    <label id="tip"  style="color: green; display: none"></label>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <?=
                Html::button(Yii::t('common', 'submit'), ['id' => 'btn_clean', 'class' => 'btn btn-primary'])
                ?>
            </td>
        </tr>
    </table>
</div>