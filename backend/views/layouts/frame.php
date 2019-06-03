<?php
use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use common\services\framework\RbacService;
/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Yii::t('system','backend_name')?></title>

    <?php $this->head() ?>
<!--    <link rel="shortcut icon" href="/favicon.ico"/>-->
<!--    <link rel="bookmark" href="/favicon.ico"/>-->
    <script>


        function LoadContentPage(ajaxUrl){

//            alert($('#jsTree_tree').html());
            $('#jsTree_tree').remove();
//            alert($('#jsTree_tree').html());
            $('#page-wrapper').empty();
            var loadimg="/static/common/images/loading.gif"; // 加载时的loading图片
            $('#page-wrapper').html('<img src="'+loadimg+'"/> <?=Yii::t('backend','waiting_for_page_loading')?>'); // 设置页面加载时的loading图片
//            var ajaxUrl = "<?//=Url::toRoute(['tree-type/index'])?>//";
            ajaxGet(ajaxUrl, "page-wrapper");
        }
    </script>
</head>
<body>
    <?php $this->beginBody() ?>
    <?php $this->endBody() ?>
    <?php
    if (!Yii::$app->user->getIsGuest()) {
        $userId = Yii::$app->user->getId();
    }
    else {
        $userId = null;
    }
    ?>
    <?php require_once('header.php');?>
<!--<input value="test" type="button" onclick="LoadContentPage('');">-->
    <div id="wrapper">
        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only"><?= Yii::t('backend','toggle_menu')?></span>
<!--                    <span class="icon-bar"></span>-->
<!--                    <span class="icon-bar"></span>-->
<!--                    <span class="icon-bar"></span>-->
                </button>
                <a class="navbar-brand" href="<?=Yii::$app->urlManager->createUrl('index/index')?>"><?= Yii::t('system','backend_name')?></a>
            </div>
            <!-- /.navbar-header -->
            <ul class="nav navbar-top-links navbar-right">
                <!-- /.dropdown
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-tasks fa-fw"></i>
                        <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-tasks">
                        <li>
                            <a href="#">
                                <div>
                                    <p>
                                        <strong>学习任务 1</strong>
                                        <span class="pull-right text-muted">40% Complete</span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
                                            <span class="sr-only">40% Complete (success)</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <p>
                                        <strong>学习任务 2</strong>
                                        <span class="pull-right text-muted">20% Complete</span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%">
                                            <span class="sr-only">20% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <p>
                                        <strong>学习任务 3</strong>
                                        <span class="pull-right text-muted">60% Complete</span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                                            <span class="sr-only">60% Complete (warning)</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <p>
                                        <strong>学习任务 4</strong>
                                        <span class="pull-right text-muted">80% Complete</span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 80%">
                                            <span class="sr-only">80% Complete (danger)</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a class="text-center" href="#">
                                <strong>查看所有学习任务</strong>
                                <i class="fa fa-angle-right"></i>
                            </a>
                        </li>
                    </ul>

                </li>
                -->
                <!--
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#"> <i class="fa fa-envelope fa-fw"></i> <i class="fa fa-caret-down"></i> </a>
                    <ul class="dropdown-menu dropdown-messages">
                        <li> <a href="#">
                                <div>
                                    <strong>发件人: 赵亮</strong>
                                    <span class="pull-right text-muted"> <em>昨天</em> </span>
                                </div>
                                <div>
                                    请讲学习管理系统的内部树结构整理成报告提交.
                                </div> </a> </li>
                        <li class="divider"></li>
                        <li> <a href="#">
                                <div>
                                    <strong>唐明强</strong>
                                    <span class="pull-right text-muted"> <em>昨天</em> </span>
                                </div>
                                <div>
                                    请各组员尽快完成学习路径的模块开发工作.
                                </div> </a> </li>
                        <li class="divider"></li>
                        <li> <a class="text-center" href="#"> <strong>读取所有通知</strong> <i class="fa fa-angle-right"></i> </a> </li>
                    </ul>
                </li>
                -->
                <li class="dropdown"> <a class="dropdown-toggle" data-toggle="dropdown" href="#"> <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i> </a>
                    <ul class="dropdown-menu dropdown-user">
                        <?php $rbacService = new RbacService(); $menuCount = 0; ?>
                        <?php if ($userId != null && $rbacService->canAction($userId,'eln_backend','user-info/change-password-index')) :?>
                            <li><a href="<?=Yii::$app->urlManager->createUrl('user-info/change-password-index')?>"><i class="fa fa-key fa-fw"></i> <?=Yii::t('backend','user_change_password')?></a> </li>
                            <?php $menuCount = $menuCount + 1 ; ?>
                        <?php endif?>
                        <?php if ($userId != null && $rbacService->canAction($userId,'eln_backend','user-info/info-index')) :?>
                            <li><a href="<?=Yii::$app->urlManager->createUrl('user-info/info-index')?>"><i class="fa fa-user fa-fw"></i> <?=Yii::t('backend','user_info')?></a> </li>
                            <?php $menuCount = $menuCount + 1 ; ?>
                        <?php endif?>

                        <?php if ($userId != null && $rbacService->canAction($userId,'eln_backend','user-info/thumb-index')) :?>
                            <li><a href="<?=Yii::$app->urlManager->createUrl('user-info/thumb-index')?>"><i class="glyphicon glyphicon-picture fa-fw"></i> <?=Yii::t('backend','thumb_management')?></a> </li>
                            <?php $menuCount = $menuCount + 1 ; ?>
                        <?php endif?>
                        <?php if ($userId != null && $rbacService->canAction($userId,'eln_backend','user-info/setting-index')) :?>
                            <li><a href="<?=Yii::$app->urlManager->createUrl('user-info/setting-index')?>"><i class="fa fa-gear fa-fw"></i> <?=Yii::t('backend','user_setting')?></a> </li>
                            <?php $menuCount = $menuCount + 1 ; ?>
                        <?php endif?>

                        <?php if ($menuCount > 0) :?>
                            <li class="divider"></li>
                        <?php endif?>
                        <li>
                            <a href="<?=Yii::$app->urlManager->createUrl('index/logout')?>" ><i class="fa fa-sign-out fa-fw"></i> <?=Yii::t('backend','logout')?></a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user --> </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->
            <?= $this->render('menu') ?>
        </nav>


        <div id="page-wrapper">
            <div class="row">
                <?= Breadcrumbs::widget([
                        'homeLink'=>[
                            'label' => Yii::t('backend','home'),
                            'url' => Yii::$app->homeUrl,
                            'target' => '_top'
                        ],
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                    ]) ?>
            </div>
            <div class="row">
                <?= $content ?>
            </div>
        </div>

        <!-- /#page-wrapper -->
    </div>

    <?php require_once('footer.php');?>


<!--    -->
</body>
</html>
<?php $this->endPage() ?>


