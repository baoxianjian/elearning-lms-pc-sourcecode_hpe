<?php
use yii\helpers\Html;
use frontend\assets\AppAsset;
use components\widgets\TKindEditor;
use yii\widgets\ActiveForm;
use components\widgets\TDatePicker;
use yii\helpers\Url;
use common\models\message\MsMessage;
?>


<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>惠普在线学习平台 学习首页</title>
<!-- Bootstrap -->
  <?= html::cssFile('/static/frontend/css/index.css')?>
  <?= html::cssFile('/static/app/css/index.css')?>
  <?= Html::cssFile('/static/common/css/common.css')?>
  <?= html::cssFile('/static/app/css/timeline.css')?>
  <?= html::cssFile('/static/app/css/mobileStyle.css')?>
  <?= html::cssFile('/static/app/css/bootstrap.css')?>
  
</head>
<body>
<?= html::jsFile('/vendor/bower/jquery/dist/jquery.min.js')?>
<?= html::jsFile('/vendor/bower/jquery-ui/jquery-ui.min.js')?>
<?= html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js')?>
  <div class="container">
		<div class="row">
			<div class="panel panel-default hotNews">
				<div class="panel-body textCenter">
					<div class="timeline" id="timeline1"></div>

				</div>
			</div>
		</div>
	</div>
	</div>
	</div>
<?= html::jsFile('/static/common/js/common.js')?>
<script>
    var loading = true;

    var type = '<?=MsMessage::TYPE_COURSE?>';

    var course_page = 1;
    var course_url = "<?=Url::toRoute(['index/get-dynamic-message','type'=>MsMessage::TYPE_COURSE])?>" + "&page=";
    var course_end = false;
    var course_time = 1;

    $(document).ready(function () {
        loadTab(course_url + course_page + '&time=' + course_time, 'timeline1');

        $(window).scroll(function () {
            var bot = 100; //bot是底部距离的高度
            if (!loading && (bot + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
                //当底部基本距离+滚动的高度〉=文档的高度-窗体的高度时；
                //我们需要去异步加载数据了
                if (type == '<?=MsMessage::TYPE_COURSE?>' && !course_end) {
                    loading = true;
                    course_page++;
                    loadTab(course_url + course_page + '&time=' + course_time, 'timeline1');
                }
            }
        });
    });
    function loadTab(ajaxUrl, container) {
        var obj=$("#" + container);
        obj.addClass('timeline_loading');
        obj.append('<div class="load-wrapp timeline_loading"><div class="load-9"><div class="spinner"><div class="bubble-1"></div><div class="bubble-2"></div></div><p>正在加载...</p></div></div>');
        ajaxGet(ajaxUrl, container, loadCallback);
    }

    function loadCallback(target, data) {
        var obj=$("#" + target);
        $("#" + target + ' .load-wrapp').remove();
        obj.removeClass('timeline_loading');
        removeWithId('empty'); //移除空内容提示
        if(data==null || data ==""){
            var emptyPrompt='<p id="empty"  align="center">暂无内容</p>';
            obj.append(emptyPrompt);
            obj.addClass('timeline_loading');//去除中竖线
        }else{
         	obj.append(data);
         }
        loading = false;
        var count=$(data).filter('.timeline-item').length;
        if (data == null || data == '' || count < 10) {
            if (target == 'timeline1') {
                course_end = true;
            }
        }
    }

    function removeWithId(id){
    	var e=$("#"+id);
        if(e.length>0)
            e.remove();
     }

</script>
<?=Html::jsFile('/components/noty/packaged/jquery.noty.packaged.min.js')?>
</body>
</html>
