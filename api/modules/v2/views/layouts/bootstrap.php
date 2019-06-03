<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/8
 * Time: 10:59
 */
use frontend\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $content string */

AppAsset::register($this);
$context = $this->context;
//$new_msg_count = $context->courseMessageCount + $context->qaMessageCount + $context->newsMessageCount + $context->socialMessageCount;
//$ms_setting=$context->ms_setting;
//$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <META HTTP-EQUIV="Cache-Control" CONTENT="no-cache"><!-- 临时禁用缓存，避免css缓存 -->
    <?= Html::csrfMetaTags() ?>
    <title>
        <?= empty($this->title) ? Yii::t('system','frontend_name') : Yii::t('system','frontend_name') . ' - ' . $this->title ?>
    </title>
    <?php echo $this->head() ?>
    <?=html::cssFile('/static/mobile/assets/bootstrap/css/bootstrap.min.css')?>
    <?= html::jsFile('/static/app/js/fastclick.js') ?>
    <?= html::jsFile('/static/app/js/jquery.min.js') ?>
    <?= html::jsFile('/static/app/js/main.js')?>
    <?= html::jsFile('/static/common/js/common.js')?>
    <link href="/static/frontend/css/elearning.1.css" rel="stylesheet">
    <link href="/static/frontend/css/elearning.2.css" rel="stylesheet">
    <link href="/static/frontend/css/elearning.3.css" rel="stylesheet">
</head>

<body style="padding: 0">
<?php //$this->beginBody() ?>
<?php //$this->endBody() ?>
<?php
if (!Yii::$app->user->getIsGuest()) {
    $userId = Yii::$app->user->getId();
}
else {
    $userId = null;
}
?>

<?= $content ?>

<script type="text/javascript">
    if(typeof app == 'object') app.extend("alert");

    var btnSub = $('.btnSub')
    btnSub.bind('click', function(){
        var typeId=$(this).attr('data-kid');
        var status=$(this).attr('data-status');
        var btn=$(this);
        $.post("<?=Url::toRoute(['common/set-subscribe-setting-status'])?>", {"type_id": typeId, "status": status},
            function (data) {
                var result = data.result;
                if (result === 'failure') {
                    app.showMsg('<?=Yii::t('common', 'operation_confirm_warning_failure')?>', 500);
                }
                else {
                    if(btn.hasClass('btn-success')){
                        btn.removeClass('btn-success').text('未订阅');
                    }else{
                        btn.addClass('btn-success').text('已订阅');
                    }
                    app.showMsg('<?=Yii::t('common', 'operation_success')?>', 500);
                }
            }, "json");
    });

    $(document).ready(function () {
        $(".searchBar").bind("click", function () {
            var searchBlock = $(".searchInput").val();
            if (searchBlock != '') {
                $("#frmSearch").submit();
            }
        });
        $("#msgMenu").load('<?=Url::toRoute(['common/message-menu'])?>');
    });


    //    document.getElementById('search_key').onkeydown = function (e) {
    //        if (!e) e = window.event;//火狐中是 window.event
    //        if ((e.keyCode || e.which) == 13) {
    //            var searchBlock = $(".searchInput").val();
    //            if (searchBlock != '') {
    //                $("#frmSearch").submit();
    //            }
    //        }
    //    };

    function showPopMsg(ajaxUrl) {
        showAndLoad(ajaxUrl, 'task1');
    }

    function showAndLoad(ajaxUrl, container) {
        app.alertWide('#'+ container);
        loadMessage(ajaxUrl, container);
    }

    function loadMessage(ajaxUrl, container) {
        $("#" + container).html($("#loading").html());
        $("#msgMenu").load('<?=Url::toRoute(['common/message-menu'])?>');
        ajaxGet(ajaxUrl, container, bind1);
    }
    function bind1(target, data) {
        $("#" + target).html(data);
        $("#" + target + ' .pagination a').bind('click', function () {
            var url = $(this).attr('href');
            loadMessage(url, target);
            return false;
        });
    }
</script>
<?//=Html::jsFile('/components/noty/packaged/jquery.noty.packaged.min.js')?>
</body>
</html>
<?php $this->endPage() ?>
