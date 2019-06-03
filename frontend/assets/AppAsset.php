<?php
namespace frontend\assets;
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@static/frontend/';
    public $baseUrl = '/static/frontend/';
    public $css = [
       'css/elearning.1.css',
       'css/elearning.2.css',
       'css/elearning.3.css',
       'css/demo45.css'
    /* 4.0 
        'css/'.
        'os.css,jquery-ui.less,reset.less'.
        ',@p.index.less,@p.UI_update.less,p.timeline.less,p.mobileStyle.less,p.bootstrap-datetimepicker.less,p_.fullcalendar.less,p2.timeline.left.less'.
        ',c.segment.less,c.dimmer.less,c.modal.less,c.popup.less,c.rating.less,c.table.less,c.transition.less,c.search.less,c.dropdown.less,c.jstree.less,c.query.list.less'.
        ',c_.button.less,c_.image.less,c_.input.less,c_.label.less,c_.list.less'.
        ',@v4.less,ui.less'.
        '.merge()-4.5.56.css','css/demo45.css'
    */
    ];

    public $js = [
        //'js/lang.zh.js',
        'js/elearning.js'
        //'js/t3.js,rating.js,popup.js,transition.js,dropdown.js,app.js,app.form.js,app.calendar.js,app.time.js,app.query.list.js,app.msg.js,elearning.richCalendar.js,bootstrap-datetimepicker.js,Chart.js.merge()-4.5.123.js', 
    ];

    public $depends = [
        'frontend\assets\TJuiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'frontend\assets\TYiiAsset',
//      'yii\web\YiiAsset',
    ];

/*
    public $depends = [
//        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\bootstrap\BootstrapThemeAsset',
    ];
*/
}