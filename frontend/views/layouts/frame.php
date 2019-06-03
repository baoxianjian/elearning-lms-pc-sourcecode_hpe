<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/5/8
 * Time: 10:59
 */
use common\models\message\MsSubscribeSetting;
use common\models\message\MsTimeline;
use common\services\framework\UserPointSummaryService;
use common\services\interfaces\service\RightInterface;
use common\services\framework\DictionaryService;
use frontend\assets\AppAsset;
use frontend\widgets\Banner;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $content string */

AppAsset::register($this);
$context = $this->context;
//$new_msg_count = $context->courseMessageCount + $context->qaMessageCount + $context->newsMessageCount + $context->socialMessageCount;
$ms_setting = $context->ms_setting;
if (Yii::$app->session->has('LoginPoint')) {
    $pointResult = Yii::$app->session->get('LoginPoint');
    Yii::$app->session->remove('LoginPoint');
}

$logoUrl = Yii::$app->session->get('logo_url');

/*
if (Yii::$app->session->has('available_point')) {
    $available_point=Yii::$app->session->get('available_point');
}
else
{
    $userPointSummaryService = new UserPointSummaryService();
    $available_point=$userPointSummaryService->getAvaliablePointByUserId(Yii::$app->user->getId());
}
*/
$userPointSummaryService = new UserPointSummaryService();
$available_point=$userPointSummaryService->getAvaliablePointByUserId(Yii::$app->user->getId());

$available_point=intval($available_point);

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <?= Html::csrfMetaTags() ?>
    <title>
        <?= empty($this->title) ? Yii::t('system','frontend_name') : Yii::t('system','frontend_name') . ' - ' . $this->title ?>
    </title>
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
    $companyId = Yii::$app->user->identity->company_id;

    $rightInterface = new RightInterface();
    $menu = $rightInterface->getCompanyMenuByType($companyId,"portal-menu");
    $dictionaryService = new DictionaryService();
    $languageModel = $dictionaryService->getDictionariesByCategory('language');

}
else {
    $userId = null;
    $companyId = null;
    $menu = null;
    $languageModel = null;
}

?>
<div id="footerFixWrapper">
<div id="footerFixBody">
    <nav class="navbar navbar-inverse navbar-fixed-top navTopBlack">
        <div class="container">
            <div id="navbar" class="navbar-collapse collapse">
                <span class="welcomeSlogan"><?=Yii::t('frontend','welcome')?></span>
                <ul class="nav navbar-nav pull-right">
                    <? if (!Yii::$app->user->getIsGuest()) {?>
                    <li class="active topMenu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="glyphicon glyphicon-info-sign"></i><?=Yii::t('frontend','navigation')?><span class="caret"></span>
                        </a>
                        <? if (!empty($menu) && count($menu) > 0) { ?>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <?foreach ($menu as $single) {
                                    $actionUrl = $single->action_url;
                                    if ($userId != null && $rightInterface->canAction($userId,'eln_frontend',$actionUrl)) {
                                        echo "<li><a href=" . Yii::$app->urlManager->createUrl([$actionUrl]) . ">" . $single->menu_name . "</a></li>";
                                    }
                                }?>
                            </ul>
                        <? }?>
                    </li>
                    <li id="msgMenu" class="active">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?= Yii::t('frontend', 'top_message_text') ?><span class="caret"></span></a>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                            <li><a href="javascript:void(0);" onclick="showPopMsg('<?= Yii::$app->urlManager->createUrl('common/pop-message-course') ?>')"  class="pull-left"><?= Yii::t('frontend', 'task_to_do') ?></a><span class="badge pull-right">0</span></li>
                            <li><a href="javascript:void(0);" onclick="showPopMsg('<?= Yii::$app->urlManager->createUrl('common/pop-message-question') ?>')"  class="pull-left"><?= Yii::t('frontend', 'tab_btn_qa') ?></a><span class="badge pull-right">0</span></li>
                            <li><a href="javascript:void(0);" onclick="showPopMsg('<?= Yii::$app->urlManager->createUrl('common/pop-message-news') ?>')" class="pull-left"><?= Yii::t('frontend', 'tab_btn_news') ?></a><span class="badge pull-right">0</span></li>
                            <li><a href="javascript:void(0);" onclick="showPopMsg('<?= Yii::$app->urlManager->createUrl('common/pop-message-social') ?>')" class="pull-left"><?= Yii::t('frontend', 'tab_btn_social') ?></a><span class="badge pull-right">0</span></li>
                            <!--                        <li><a href="javascript:void(0);" onclick="" class="pull-left">系统消息</a><span class="badge pull-right">0</span></li>-->
                            <li><a href="javascript:void(0);" class="system" onclick="app.alert('#systemSetting');"><?= Yii::t('frontend', 'system_config') ?></a></li>
                        </ul>
                    </li>
                    <? }?>
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
                    <li class="active"><a href="<?= Yii::$app->urlManager->createUrl('site/logout') ?>" class="dropdown-toggle"><i class="glyphicon glyphicon-log-in"></i><?=Yii::t('frontend','logout')?></a>
                    </li>
                </ul>
                <? if (!Yii::$app->user->getIsGuest()) {?>
                <a class="totalScore btn pull-right" href="<?= Yii::$app->urlManager->createUrl('student/integral') ?>"><?=Yii::t('frontend','available_point')?><strong><?=$available_point?><?=Yii::t('frontend','point')?></strong></a>
                <? }?>
            </div>
        </div>
    </nav>
    <nav class="navbar navbar-inverse navbar-fixed-top navTopWhite">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href="/"><? if ($logoUrl): ?><img src="<?=$logoUrl?>" height="35px" style="margin-top: -8px;"><? else: ?><?= Yii::t('system', 'frontend_name') ?><? endif; ?></a>
                <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="true">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav pull-right">
                    <li class="searchLi">
                        <form id="frmSearch" action="<?= Yii::$app->urlManager->createUrl('site/search')?>" onsubmit="return window._iesub ? false : (window._iesub=true)" method="get" autocomplete="on">
                            <input id="search_key" name="key" type="text" placeholder="<?= Yii::t('frontend', 'top_search_text') ?>"
                                   class="searchInput showSearch pull-left"/><a
                                class="searchBar pull-left" href="javascript:void(0);"><i
                                    class="glyphicon glyphicon-search"></i></a>
                        </form>
                    </li>
                    <li class="active">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="glyphicon glyphicon-info-sign"></i><?=Yii::t('frontend','banner')?><span class="caret"></span>
                        </a>
                        <? if (!empty($menu) && count($menu) > 0) { ?>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <?foreach ($menu as $single) {
                                    $actionUrl = $single->action_url;
                                    if ($userId != null && $rightInterface->canAction($userId,'eln_frontend',$actionUrl)) {
                                        echo "<li><a href=" . Yii::$app->urlManager->createUrl([$actionUrl]) . ">" . Yii::t('data',$single->i18n_flag) . "</a></li>";
                                    }
                                }?>
                            </ul>
                        <? }?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <? echo Banner::widget(); ?>
<?= $content ?>
<!-- 系统配置弹出页面 begin-->
<div class="ui modal" id="systemSetting">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=Yii::t('frontend','system_config')?></h4>
    </div>
    <div class="content">
        <table class="table table_teacher" style="margin-top:0; text-align:left;">
            <tbody>
            <tr>
                <td width="80%"><?=Yii::t('frontend','tab_btn_todo')?></td>
                <td width="20%"><?=Yii::t('common','status')?></td>
            </tr>
            <? foreach ($ms_setting as $v): ?>
                <? if ($v['type'] === MsTimeline::TIMELINE_TYPE_TODO): ?>
                    <tr>
                        <td><?= Yii::t('frontend', $v['i18n_flag']) ?></td>
                        <? if($v['is_turnoff']=='1'): ?>

                            <? if ($v['status'] === MsSubscribeSetting::STATUS_ON): ?>
                                <td><a href="javascript:void(0);" data-kid="<?=$v['type_id']?>" data-status="<?=MsSubscribeSetting::STATUS_OFF ?>" class="btnSub btn btn-xs btn-default btn-success"><?=Yii::t('frontend','subscribe_yes')?></a></td>
                            <? else: ?>
                                <td><a href="javascript:void(0);" data-kid="<?=$v['type_id']?>" data-status="<?=MsSubscribeSetting::STATUS_ON ?>" class="btnSub btn btn-xs btn-default"><?=Yii::t('frontend','subscribe_no')?></a></td>
                            <? endif; ?>
                        <? else:?>
                            <td><?=Yii::t('frontend','subscribe_yes')?></td>
                        <?endif;?>
                    </tr>
                <? endif; ?>
            <? endforeach; ?>
            </tbody>
        </table>
        <table class="table table_teacher" style="margin-top:0; text-align:left;">
            <tbody>
            <tr>
                <td width="80%"><?=Yii::t('frontend','tab_btn_qa')?></td>
                <td width="20%"><?=Yii::t('common','status')?></td>
            </tr>
            <? foreach ($ms_setting as $v): ?>
                <? if ($v['type'] === MsTimeline::TIMELINE_TYPE_QA): ?>
                    <tr>
                        <td><?= Yii::t('frontend', $v['i18n_flag']) ?></td>
                        <? if ($v['status'] === MsSubscribeSetting::STATUS_ON): ?>
                            <td><a href="javascript:void(0);" data-kid="<?=$v['type_id']?>" data-status="<?=MsSubscribeSetting::STATUS_OFF ?>" class="btnSub btn btn-xs btn-default btn-success"><?=Yii::t('frontend','subscribe_yes')?></a></td>
                        <? else: ?>
                            <td><a href="javascript:void(0);" data-kid="<?=$v['type_id']?>" data-status="<?=MsSubscribeSetting::STATUS_ON?>" class="btnSub btn btn-xs btn-default"><?=Yii::t('frontend','subscribe_no')?></a></td>
                        <? endif; ?>
                    </tr>
                <? endif; ?>
            <? endforeach; ?>
            </tbody>
        </table>
        <table class="table table_teacher" style="margin-top:0; text-align:left;">
            <tbody>
            <tr>
                <td width="80%"><?=Yii::t('frontend','tab_btn_news')?></td>
                <td width="20%"><?=Yii::t('common','status')?></td>
            </tr>
            <? foreach ($ms_setting as $v): ?>
                <? if ($v['type'] ===  MsTimeline::TIMELINE_TYPE_NEWS): ?>
                    <tr>
                        <td><?= Yii::t('frontend', $v['i18n_flag']) ?></td>
                        <? if ($v['status'] === MsSubscribeSetting::STATUS_ON): ?>
                            <td><a href="javascript:void(0);" data-kid="<?=$v['type_id']?>" data-status="<?=MsSubscribeSetting::STATUS_OFF ?>" class="btnSub btn btn-xs btn-default btn-success"><?=Yii::t('frontend','subscribe_yes')?></a></td>
                        <? else: ?>
                            <td><a href="javascript:void(0);" data-kid="<?=$v['type_id']?>" data-status="<?=MsSubscribeSetting::STATUS_ON ?>" class="btnSub btn btn-xs btn-default"><?=Yii::t('frontend','subscribe_no')?></a></td>
                        <? endif; ?>
                    </tr>
                <? endif; ?>
            <? endforeach; ?>
            </tbody>
        </table>
        <table class="table table_teacher" style="margin-top:0; text-align:left;">
            <tbody>
            <tr>
                <td width="80%"><?=Yii::t('frontend','tab_btn_social')?></td>
                <td width="20%"><?=Yii::t('common','status')?></td>
            </tr>
            <? foreach ($ms_setting as $v): ?>
                <? if ($v['type'] === MsTimeline::TIMELINE_TYPE_SOCIAL): ?>
                    <tr>
                        <td><?= Yii::t('frontend', $v['i18n_flag']) ?></td>
                        <? if ($v['status'] === MsSubscribeSetting::STATUS_ON): ?>
                            <td><a href="javascript:void(0);" data-kid="<?=$v['type_id']?>" data-status="<?=MsSubscribeSetting::STATUS_OFF ?>" class="btnSub btn btn-xs btn-default btn-success"><?=Yii::t('frontend','subscribe_yes')?></a></td>
                        <? else: ?>
                            <td><a href="javascript:void(0);" data-kid="<?=$v['type_id']?>" data-status="<?=MsSubscribeSetting::STATUS_ON ?>" class="btnSub btn btn-xs btn-default"><?=Yii::t('frontend','subscribe_no')?></a></td>
                        <? endif; ?>
                    </tr>
                <? endif; ?>
            <? endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- 系统配置弹出页面 end-->
<!-- 消息框弹出界面 begin -->
<div id="task1" class="ui modal">
</div>
<div id="loading" class="hide">
    <div class="header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">loading...</h4>
    </div>
    <div class="content">
        <div class="modal-body textCenter">
            <div class="loadingWaiting" style="margin:150px auto;">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>

                <p style="margin-top:15px;"><?=Yii::t('frontend','loading')?>...</p>
            </div>
        </div>
    </div>
</div>
<!-- 消息框弹出界面 end -->
</div><!--footerFixBody-->
<div style="height:35px;"></div>
<footer id="footerFix" style="height: 78px;">
    <div class="container">
        <div class="footLinks">
<!--            <ul class="privacy_links">-->
<!--                <li class="first_li"><a name="1433727" tabindex="910" class="link_metrics"-->
<!--                                        href="http://www8.hp.com/cn/zh/home.html">首页</a><span-->
<!--                        class="hf_separ">|</span></li>-->
<!--                <li><a name="1514074" tabindex="910" class="link_metrics"-->
<!--                       href="https://h41183.www4.hp.com/hub.php?country=CN&amp;language=ZHS">电子邮件注册</a><span-->
<!--                        class="hf_separ">|</span></li>-->
<!--                <li><a name="1433728" tabindex="910" class="link_metrics"-->
<!--                       href="http://www8.hp.com/cn/zh/sitemap.html">网站地图</a><span class="hf_separ">|</span></li>-->
<!--                <li><a name="1433729" tabindex="910" class="link_metrics"-->
<!--                       href="http://www8.hp.com/cn/zh/privacy/privacy.html">隐私</a><span-->
<!--                        class="hf_separ">|</span>-->
<!--                </li>-->
<!--                <li><a name="1552183" tabindex="910" class="link_metrics"-->
<!--                       href="http://www8.hp.com/cn/zh/privacy/privacy.html#hptpw">广告策略</a><span-->
<!--                        class="hf_separ">|</span></li>-->
<!--                <li><a name="1433730" tabindex="910" class="link_metrics"-->
<!--                       href="http://www8.hp.com/cn/zh/privacy/terms-of-use.html">使用条款</a><span-->
<!--                        class="hf_separ">|</span></li>-->
<!--                <li class="lstchild"><a name="1433736" tabindex="910" class="link_metrics"-->
<!--                                        href="http://www8.hp.com/cn/zh/hp-information/recalls.html">帮助</a></li>-->
<!--            </ul>-->
<!--            <br/>-->

            <div class="hf_bottom_links">
                <p class="copyright"><?=Yii::t('system','version_info');?> <?=Yii::t('system','version_no')?>：<?= Yii::$app->version ?></p>
            </div>
        </div>
    </div>
</footer>
</div><!--footerFixWrapper-->
<?= html::jsFile('/static/common/js/common.js') ?>
<script type="text/javascript">
    app.extend("alert");

    var btnSub = $('.btnSub')
    btnSub.bind('click', function(){
        var typeId=$(this).attr('data-kid');
        var status=$(this).attr('data-status');
        var btn=$(this);
        $.post("<?=Url::toRoute(['common/set-subscribe-setting-status'])?>", {"type_id": typeId, "status": status},
            function (data) {
                var result = data.result;
                if (result === 'failure') {
                    app.showMsg('<?=Yii::t('common','operation_confirm_warning_failure');?>', 500);
                }
                else {
                    if(btn.hasClass('btn-success')){
                        btn.attr('data-status',"<?=MsSubscribeSetting::STATUS_ON ?>");
                        btn.removeClass('btn-success').text('<?=Yii::t('frontend','subscribe_no')?>');
                    }else{
                        btn.attr('data-status',"<?=MsSubscribeSetting::STATUS_OFF ?>");
                        btn.addClass('btn-success').text('<?=Yii::t('frontend','subscribe_yes')?>');
                    }
                    app.showMsg('<?=Yii::t('common','operation_success');?>', 500);
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

        <? if ($pointResult):?>
        //score-Effect(<?=$pointResult['trans_op'] . $pointResult['trans_point']?>);
        scorePointEffect('<?=$pointResult['show_point']?>','<?=$pointResult['point_name']?>','<?=$pointResult['available_point']?>');
        <? endif; ?>
    });
    document.getElementById('search_key').onkeydown = function (e) {
        if (!e) e = window.event;//火狐中是 window.event
        if ((e.keyCode || e.which) == 13) {
            var searchBlock = $(".searchInput").val();
            if (searchBlock != '') {
                $("#frmSearch").submit();
            }
        }
    };

    function showPopMsg(ajaxUrl) {
        showAndLoad(ajaxUrl, 'task1');
    }

    function showAndLoad(ajaxUrl, container) {
        if (!$('#' + container).hasClass('active')) {
            app.alertWide('#' + container);
        }
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
    // change 是加分还是减分, 数字
    // totalScore 传入总分, 数字
    function scoreEffect(change) {
        if (!change || parseInt(change) === 0) {
            return;
        }
        /*
        var changeStatus = "", tag = "";

        (change > 0) ? (changeStatus = "addOne", tag = "+") : (changeStatus = "reduceOne", tag = "");

        var showScore = '<div class="showScore">' + tag + change + '</div>';

        $('body').append(showScore);
        $('.showScore').css({
            "animation": changeStatus + " 1.1s ease"
        });
        setTimeout('$(".showScore").css({"animation":"null","display":"none"}).remove()', 1000);
        */
        scoreEffect1('<?=Yii::t('backend','point');?>',20,'<?=Yii::t('frontend','registered_face_to_face_courses')?>',600000);
           
    }
    
    function scorePointEffect(change, cAction, newScore) {
        try {      
            if(typeof(change)=='undefined' || parseInt(change)==0)
            { 
               return;
            }  
        }catch(e) {
            return;
        }
        change=parseFloat(change);
        newScore=parseInt(newScore);
       //  myJson = [{"point":"1"},{"password":"1111"}] ;
         scoreEffect1('<?=Yii::t('backend','point');?>',change,cAction,newScore);
    }
    
    function checkPointResult(pointResult)
    {
        if(typeof pointResult=='undefined' || !pointResult){return false;}
        if(typeof pointResult.show_point=='undefined'){return false;}
        if(parseInt(pointResult.show_point)==0){return false;} 
        return true;
    }
    
    function showPointEffect(pointResult)
    {
       if(!checkPointResult(pointResult)){return false;}
       change=parseFloat(pointResult.show_point);
       newScore=parseInt(pointResult.available_point);
       scoreEffect1('<?=Yii::t('common', 'point')?>',change,pointResult.point_name,newScore);
    }
    
//-------score flash effect start------------

      // 获取最新的总分
  function scoreRefresh(newScore) {
    $('.totalScore strong').text(newScore + '<?=Yii::t('frontend','point');?>');
  }

  // 总积分增减时候的效果
  function scoreEffectTop() {
    $('.totalScore').css("animation", "highLight 1s");
    setTimeout('$(".totalScore").css("animation", "null")', 900);
  }

  // 积分增减的动画函数, 变量例子:  ('积分',-20,'注销课程',10000), ('积分',20,'注册面授课程',600000)
  function scoreEffect1(actionType, change, cAction, newScore) {
    var
      changeStatu = "",
      tag = "",
      shadow = '<div class="shadow"></div>',
      baseClass = "",
      word = "",
      animationPart = "",
      animationPartClass = "",
      animationBack = "";
      
      $('#appMsg').css('margin-top','-200px');
      
      if (!change || parseInt(change) === 0) {
            return;
        }

    // 根据数字正负判断使用何种动画
    if (change > 0) {
      changeStatu = "addOne";
      tag = "+";
      baseClass = "add";
      word = "<?=Yii::t('frontend','sucess');?>!";
      animationPart = "addScore_stars.png";
      animationPartClass = "addScore_stars";
      animationBack = "addScore_back.png";
    } else {
      changeStatu = "reduceOne";
      tag = "";
      baseClass = "reduce";
      word = "";
      animationPart = "reduceScore_break.png";
      animationPartClass = "reduceScore_break";
      animationBack = "reduceScore_back.png";
    }

    // 拼装动画结构
    var showScore = '<div class="Score_back"> <div class="' + baseClass + '"><div class="addScore"> <p>' + actionType + '<strong>' + tag + change + '</strong></p> </div> <div class="reason"> <p>' + cAction + word + '</p> </div> </div><img src="/static/frontend/images/' + animationPart + '" class="' + animationPartClass + '"><img src="/static/frontend/images/' + animationBack + '"> </div>'

    // 插入动画结构以及幕布
    $('body').append(showScore).append(shadow);

    // 给动画增加效果
    $('.Score_back').css({
      "animation": changeStatu + " 2.5s ease"
    });

    // 清空动画, 顶部总积分高亮
    setTimeout(function() {
      $(".Score_back").css({"animation": "null","display": "none"}).remove();
      $(".shadow").remove();
      scoreEffectTop();
      scoreRefresh(newScore);
      $('#appMsg').css('margin-top','auto');
    }, 2500);

  }
//-------score flash effect end------------
    
    
    
    
    function scrollDirection() {
        $('.navTopWhite').css('transition', '0.3s');
        var initScroll = $(document).scrollTop();

        $(document).bind('scroll', function () {
            var afterScroll = $(document).scrollTop();
            (afterScroll - initScroll > 0 && initScroll >= 75) ? ($('.navTopWhite').css('transform', 'translateY(-110px)')) : ($('.navTopWhite').css('transform', 'translateY(0)'));
            initScroll = afterScroll;
        })
    }
    $(document).bind('scroll', scrollDirection());
</script>
<?=Html::jsFile('/components/noty/packaged/jquery.noty.packaged.min.js')?>
</body>
</html>
<?php $this->endPage() ?>
