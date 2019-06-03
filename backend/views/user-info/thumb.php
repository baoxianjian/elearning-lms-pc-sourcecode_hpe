<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

//$ContentTanslateName =  Yii::t('common', 'user_thumb') ;
//
//$this->params['breadcrumbs'][] =  $ContentTanslateName . Yii::t('common', 'management');
?>
<input type="hidden" id="indexUrl" value="<?=Yii::$app->urlManager->createUrl(['user-info/thumb-index']);?>"/>
<head>
    <?=Html::cssFile('/static/common/css/jquery.Jcrop.css')?>
    <?=Html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
    <?=Html::jsFile('/static/common/js/ajaxupload.js')?>
    <?=Html::jsFile('/static/common/js/jquery.Jcrop.min.js')?>
    <?=Html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>

    <script>
        $(document).ready(function() {
            $("#crop").hide();
        });

        var indexUrl = document.getElementById('indexUrl');

        if(!document.getElementById("content-body"))
        {
            window.location = indexUrl.value;
        }


    </script>

    <style type="text/css">
        .form{padding: 15px;}
        .jcorp-holder{position: relative;}
        #frm{margin-bottom: 0px; }
        #frm input{margin:15px 0; }
        .pic-display{display: block;margin: 20px;width: auto;}
        #thum{width: auto;}
        /*#thum img{width: auto;height: auto;display: block;}*/
        #preview-pane{
            padding: 0;
            width:150px;
            height: 150px;
            overflow: hidden;
            display: block;
            position: absolute;
            z-index: 2000;
            top: 10px;
            right:-170px;

            border: 1px rgba(0,0,0,.4) solid;
            background-color: white;

            -webkit-border-radius: 6px;
            -moz-border-radius: 6px;
            border-radius: 6px;

            -webkit-box-shadow: 1px 1px 5px 2px rgba(0, 0, 0, 0.2);
            -moz-box-shadow: 1px 1px 5px 2px rgba(0, 0, 0, 0.2);
            box-shadow: 1px 1px 5px 2px rgba(0, 0, 0, 0.2);
        }

        #preview-pane .preview-container {
            width: 150px;
            height: 150px;
            overflow: hidden;
            padding: 0;
            text-indent: 0;
        }

        .jcrop-preview{
            padding: 0;
            margin: 0;
            text-indent: 0;
        }

    </style>
</head>

<div class="form">
    <img class="img-circle tx" src="<?=$model->getThumb() ?>" alt="" width="120" height="120"/>
    <br/> <br/>
    <?php $form = ActiveForm::begin([
        'id' => 'frm',
        'method' => 'post',
        'action' => '#'
    ]); ?>
        <input type="hidden" id="x" name="x" />
        <input type="hidden" id="y" name="y" />
        <input type="hidden" id="w" name="w" />
        <input type="hidden" id="h" name="h" />
        <input type="hidden" id="f" name="f" />
        <?=
            Html::button(Yii::t('common', 'clear_setting'),
                ['id'=>'clear','class'=>'btn btn-primary'])
        ?>
        <?=
            Html::button(Yii::t('common', 'upload_thumb'),
                ['id'=>'upload','class'=>'btn btn-primary'])
        ?>
        <?=
            Html::button(Yii::t('common', 'confirm_crop'),
                ['id'=>'crop','class'=>'btn btn-primary'])
        ?>

    <?php ActiveForm::end(); ?>
    <div class="info"></div>
    <div class="pic-display"></div>
    <div class="text-info"></div>
</div>

<script>
    var ajaxUploadUrl = "<?=Url::toRoute(['user-info/upload'])?>";
    var g_oJCrop = null;
    //alert(ajaxUploadUrl);
    //异步上传文件
    new AjaxUpload("#upload", {
        action: ajaxUploadUrl,
        type:"POST",
        name:'myfile',
        data:{'_csrf':'<?= Yii::$app->request->csrfToken ?>'},
        onSubmit: function(file, ext) {
            $(".pic-display").html("");
            if($(".text-info img").length>0){
                $(".info").html("<div style='color:#E3583B;margin:5px;'>"+"<?=Yii::t('backend', 'file_cropped')?>"+"</div>");return false;
            }
            $(".info").html("<div style='color:#008000;margin:5px;'>"+"<?=Yii::t('backend', 'uploading')?>"+"</div>");
        },
        onComplete: function(file, response) {

            if(g_oJCrop!=null){g_oJCrop.destroy();}

            if (response == "<?=Yii::t('common', 'file_type_error')?>" || response == "<?=Yii::t('backend', 'upload_error')?>")
            {
                $(".info").html("<div style='color:#008000;margin:5px;'>"+response+"</div>");
                $("#crop").hide();
            }
            else {
                //生成元素
                $(".pic-display").html("<div class='thum'><img id='target' src='" + response + "'/></div>");

                //初始化裁剪区
                $('#target').Jcrop({
                    onChange: updatePreview,
                    onSelect: updatePreview,
                    aspectRatio: 1
                }, function () {
                    g_oJCrop = this;

                    //插入略缩图
                    $(".jcrop-holder").append("<div id='preview-pane'><div class='preview-container'><img  class='jcrop-preview' src='" + response + "' /></div></div>");

                    var bounds = g_oJCrop.getBounds();
                    var x1, y1, x2, y2;
                    if (bounds[0] / bounds[1] > 150 / 150) {
                        y1 = 0;
                        y2 = bounds[1];

                        x1 = (bounds[0] - 150 * bounds[1] / 150) / 2;
                        x2 = bounds[0] - x1;
                    }
                    else {
                        x1 = 0;
                        x2 = bounds[0];

                        y1 = (bounds[1] - 150 * bounds[0] / 150) / 2;
                        y2 = bounds[1] - y1;
                    }


                    g_oJCrop.setSelect([x1, y1, x2, y2]);

                });

                //传递参数上传
                $("#f").val(response);

                //更新提示信息
                $(".info").html("<div style='color:#008000;margin:5px;'>"+"<?=Yii::t('backend', 'prepare_crop')?>"+"</div>");

                $("#crop").show();
            }
        }
    });

    //更新裁剪图片信息
    function updatePreview(c) {

        if (parseInt(c.w) > 0){
            $('#x').val(c.x);
            $('#y').val(c.y);
            $('#w').val(c.w);
            $('#h').val(c.h);
            var bounds = g_oJCrop.getBounds();

            var rx = 150 / c.w;
            var ry = 150 / c.h;

            $('.preview-container img').css({
                width: Math.round(rx * bounds[0]) + 'px',
                height: Math.round(ry * bounds[1]) + 'px',
                marginLeft: '-' + Math.round(rx * c.x) + 'px',
                marginTop: '-' + Math.round(ry * c.y) + 'px'
            });
        }
    }


    var ajaxCutPicUrl = "<?=Url::toRoute(['user-info/cut-pic'])?>";

    //表单异步提交后台裁剪
    $("#crop").click( function(){
        var w=parseInt($("#w").val());
        if(!w){
            w=0;
        }
        if(w>0){
            $.post(
                ajaxCutPicUrl,
                {
                    'x':$("input[name=x]").val(),
                    'y':$("input[name=y]").val(),
                    'w':$("input[name=w]").val(),
                    'h':$("input[name=h]").val(),
                    'f':$("input[name=f]").val(),
                    '_csrf':'<?= Yii::$app->request->csrfToken ?>'
                },
                function(data){
                  //  alert(data.status);
                    if(data.status==1){
                        $(".pic-display").html("");
                        $(".info").html("<div style='color:#008000;margin:5px;'>"+"<?=Yii::t('backend', 'thumb_upload_ok')?>"+"</div>")
    //                    $(".text-info").html("<img src='"+data.data+"'>");
                        $(".text-info").html("");
                        $(".tx").attr('src',data.data);
                        $("#crop").hide();
                        //$("#upload").hide();
                    }

            },'json');
        }else{
            $(".info").html("<div style='color:#E3583B;margin:5px;'>"+"<?=Yii::t('backend', 'crop_area_select')?>"+"</div>");
        }
    });


    var ajaxClearPicUrl = "<?=Url::toRoute(['user-info/clear-pic'])?>";

    //表单异步提交后台裁剪
    $("#clear").click( function(){
        var msg = "<?=Yii::t('backend','operation_confirm')?>";
        var gender = "<?=$model->gender ?>";

        var thumbUrl = '/static/common/images/man.jpeg';
        if (gender == Yii::t('common', 'gender_female')) {
            thumbUrl = '/static/common/images/woman.jpeg';
        }
        NotyConfirm(msg,  function(data){
            $.post(
                ajaxClearPicUrl,
                {
                    '_csrf': '<?= Yii::$app->request->csrfToken ?>'
                },
                function (data) {
                    //  alert(data.status);
                    if (data.result == 'success') {
                        var msg = "<?=Yii::t('backend','operation_success')?>";
                        NotyWarning(msg);
                        $(".tx").attr('src', thumbUrl);
                        $(".info").html("");
                        $(".pic-display").html("");
                        $(".text-info").html("");
                    }

                }, 'json');
        });

//        if (confirm("<?//=Yii::t('backend','operation_confirm')?>//")) {
//            $.post(
//                ajaxClearPicUrl,
//                {
//                    '_csrf': '<?//= Yii::$app->request->csrfToken ?>//'
//                },
//                function (data) {
//                    //  alert(data.status);
//                    if (data.result == 'success') {
//                        var msg = "<?//=Yii::t('backend','operation_success')?>//";
//                        alert(msg);
//                        $(".tx").attr('src', '/static/common/images/thumb.jpg');
//                        $(".info").html("");
//                        $(".pic-display").html("");
//                        $(".text-info").html("");
//                    }
//
//                }, 'json');
//        }
    });

</script>