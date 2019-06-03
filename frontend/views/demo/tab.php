<?php

use yii\helpers\Html;
use components\widgets\TBreadcrumbs;
use components\widgets\TLinkPager;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = 'tab分页测试';
$this->params['breadcrumbs'][] = 'tab分页测试';

?>
<div class="col-md-8">
    <ul class="nav nav-tabs hotNews" role="tablist" id="myTab">
        <li role="presentation" class="active">
            <a href="#all" aria-controls="all" role="tab" data-toggle="tab">所有课程</a>
        </li>
        <li role="presentation">
            <a href="#study" aria-controls="study" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'page_lesson_hot_tab_2')?></a>
        </li>
        <li role="presentation">
            <a href="#done" aria-controls="done" role="tab" data-toggle="tab"><?=Yii::t('frontend', 'complete_status_done')?></a>
        </li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="all">
        </div>
        <div role="tabpanel" class="tab-pane" id="study">
        </div>
        <div role="tabpanel" class="tab-pane" id="done">
        </div>
    </div>
</div>
<!-- /container -->
<?= html::jsFile('/vendor/bower/jquery/dist/jquery.min.js') ?>
<?= html::jsFile('/vendor/bower/bootstrap/dist/js/bootstrap.min.js') ?>
<?= html::jsFile('/vendor/bower/jquery-ui/jquery-ui.min.js') ?>
<?= html::jsFile('/static/frontend/js/jquery.cookie.js') ?>
<?= html::jsFile('/static/frontend/js/accordionImageMenu-0.4.js') ?>
<?= html::jsFile('/static/frontend/js/Chart.js') ?>
<?= html::jsFile('/static/common/js/common.js') ?>
<script>
    $(document).ready(function () {
        loadTab("<?=Url::toRoute(['demo/all',])?>", 'all');
        loadTab("<?=Url::toRoute(['demo/study',])?>", 'study');
        loadTab("<?=Url::toRoute(['demo/done',])?>", 'done');

        $('#myTab a:first').tab('show');
    });

    function loadTab(ajaxUrl, container) {
        ajaxGet(ajaxUrl, container, bind);
    }
    function bind(target, data) {
        $("#" + target).html(data);
        $("#" + target + ' .pagination a').bind('click', function () {
            var url = $(this).attr('href');
            loadTab(url, target);
            return false;
        });
    }
</script>
</div>