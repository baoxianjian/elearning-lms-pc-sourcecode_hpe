<?php
/**
 * Created by PhpStorm.
 * User: kaylio
 * Date: 15/4/28
 * Time: 16:22
 */

use yii\helpers\Html;
?>
<?=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
<?=Html::jsFile('/components/uploadify/jquery.uploadify.min.js')?>
<?=Html::cssFile('/components/uploadify/uploadify.css')?>
<style type="text/css">
    body {
    font: 13px Arial, Helvetica, Sans-serif;
}
</style>
<h1>Uploadify Demo</h1>
<form>
    <div id="queue"></div>
    <input id="file_upload" name="file_upload" type="file" multiple="true">
</form>

<script type="text/javascript">
    <?php $timestamp = time();?>
    $(function() {
        $('#file_upload').uploadify({
            'formData'     : {
            'timestamp' : '<?php echo $timestamp;?>',
            'token'     : '<?php echo md5('unique_salt' . $timestamp);?>'
            },

            'auto' : false,
            'swf'      : '/components/uploadify/uploadify.swf',
            'uploader' : '<?=Yii::$app->urlManager->createUrl('/demo/upload')?>'
        });
    });
    </script>