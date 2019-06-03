<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/8
 * Time: 10:59
 */
use frontend\assets\AppAsset;
use yii\helpers\Html;
use common\services\framework\DictionaryService;

/* @var $this yii\web\View */
/* @var $content string */

AppAsset::register($this);
$context = $this->context;
$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= Yii::t('system','frontend_name')?></title>
    <script type="text/javascript" src="/static/frontend/js/lang.zh-CN.js"></script>
    <?= 'zh-CN' !== Yii::$app->language ? '<script type="text/javascript" src="/static/frontend/js/lang.' . Yii::$app->language . '.js"></script>' : '' ?>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?php $this->endBody() ?>
<?php
if (!Yii::$app->user->getIsGuest()) {
    $userId = Yii::$app->user->getId();
    $dictionaryService = new DictionaryService();
    $languageModel = $dictionaryService->getDictionariesByCategory('language');}
else {
    $userId = null;
    $dictionaryService = new DictionaryService();
    $languageModel = $dictionaryService->getDictionariesByCategory('language');
}
?>
<div id="footerFixWrapper">
<div id="footerFixBody">
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <!--      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar"> <span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>  -->
                <a class="navbar-brand" href="/"><?= Yii::t('system', 'frontend_name') ?></a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav pull-right">
                    <li class="active">
                        <form id="frmSearch" action="<?= Yii::$app->urlManager->createUrl('site/search')?>" onsubmit="return window._iesub ? false : (window._iesub=true)" method="get" autocomplete="on">
                            <input id="search_key" name="key" type="text" placeholder="<?= Yii::t('frontend', 'top_search_text') ?>"
                                                  class="searchInput showSearch pull-left"/><a
                                class="searchBar pull-left" href="javascript:void(0);"><i
                                    class="glyphicon glyphicon-search"></i></a>
                        </form>
                    </li>
                    <? if(count($languageModel)>1){?>
                    <li class="active">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?=Yii::t('common','course_language')?><span class="caret"></span></a>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu" style="min-width:100px;">
                            <? foreach($languageModel as $k=>$v){?>
                            <? if($v->status == 1){?>

                                <li><a href="<?= Yii::$app->urlManager->createUrl(['site/change-lang','lang'=>$v->dictionary_code,'url'=>'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REDIRECT_URL']]) ?>" class="pull-left" style="width:100%; text-align:center;"><?=Yii::t('data',$v->i18n_flag)?></a></li>
                            <?}?>
                            <?}?>
                        </ul>
                    </li>
                    <?}?>
                </ul>
            </div>
        </div>
    </nav>
    <?= $content ?>
</div>
<!--footerFixBody-->
<footer id="footerFix">
    <div class="container">
        <div class="footLinks">
            <ul class="privacy_links">
                <li class="first_li"><a name="1433727" tabindex="910" class="link_metrics"
                                        href="http://www8.hp.com/cn/zh/home.html"><?=Yii::t('frontend','nav_home_text')?></a><span
                        class="hf_separ">|</span></li>
                <li><a name="1514074" tabindex="910" class="link_metrics"
                       href="https://h41183.www4.hp.com/hub.php?country=CN&amp;language=ZHS"><?=Yii::t('frontend','email_regist')?></a><span
                        class="hf_separ">|</span></li>
                <li><a name="1433728" tabindex="910" class="link_metrics"
                       href="http://www8.hp.com/cn/zh/sitemap.html"><?=Yii::t('frontend','web_map')?></a><span class="hf_separ">|</span></li>
                <li><a name="1433729" tabindex="910" class="link_metrics"
                       href="http://www8.hp.com/cn/zh/privacy/privacy.html"><?=Yii::t('frontend','private')?></a><span
                        class="hf_separ">|</span>
                </li>
                <li><a name="1552183" tabindex="910" class="link_metrics"
                       href="http://www8.hp.com/cn/zh/privacy/privacy.html#hptpw"><?=Yii::t('frontend','ad_strategy')?></a><span
                        class="hf_separ">|</span></li>
                <li><a name="1433730" tabindex="910" class="link_metrics"
                       href="http://www8.hp.com/cn/zh/privacy/terms-of-use.html"><?=Yii::t('frontend','terms_of_use')?></a><span
                        class="hf_separ">|</span></li>
                <li class="lstchild"><a name="1433736" tabindex="910" class="link_metrics"
                                        href="http://www8.hp.com/cn/zh/hp-information/recalls.html"><?=Yii::t('frontend','help')?></a></li>
            </ul>
            <br/>

            <div class="hf_bottom_links">
                <p class="copyright"><?=Yii::t('system','version_info');?></p>
            </div>
        </div>
    </div>
</footer>
</div>
<!--footerFixWrapper-->
<?= html::jsFile('/static/common/js/common.js') ?>
</body>
</html>
<?php $this->endPage() ?>
