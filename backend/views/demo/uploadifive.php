<?php

use yii\helpers\Html;
?>

<!--    --><?//=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
<!--    --><?//=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>
    <?=Html::jsFile('/components/uploadifive/jquery.uploadifive.js')?>
    <?=Html::cssFile('/components/uploadifive/uploadifive.css')?>
    <style type="text/css">
        body {
            font: 13px Arial, Helvetica, Sans-serif;
        }
        .uploadifive-button {
            float: left;
            margin-right: 10px;
        }
        #queue {
            border: 1px solid #E5E5E5;
            height: 177px;
            overflow: auto;
            margin-bottom: 10px;
            padding: 0 3px 3px;
            width: 300px;
        }
    </style>

    <h1>UploadiFive Demo</h1>
    <form>
        <div id="queue"></div>
        <input id="file_upload" name="file_upload" type="file" multiple="true">
        <a style="position: relative; top: 8px;" href="javascript:$('#file_upload').uploadifive('upload')">Upload Files</a>
    </form>

    <script type="text/javascript">
        <?php $timestamp = time();?>
        $(function() {
            $('#file_upload').uploadifive({
                'auto'             : false,
//                'checkScript'      : '<?//=Yii::$app->urlManager->createUrl('/demo/check-exists')?>//',
//                '<?//=Yii::$app->urlManager->createUrl('/demo/upload-file')?>//',
                'formData'         : {
                    '_csrf':'<?= Yii::$app->request->csrfToken ?>',
                    'timestamp' : '<?php echo $timestamp;?>',
                    'token'     : '<?php echo md5('unique_salt' . $timestamp);?>'
                },
                'queueID'          : 'queue',
                'uploadScript'     :   '<?=Yii::$app->urlManager->createUrl('/demo/upload-file')?>'
//                'onUploadComplete' : function(file, data) { console.log(data); }
            });
        });
    </script>