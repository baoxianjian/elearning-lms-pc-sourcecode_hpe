<?php

namespace components\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

class TFlowplayer extends BaseWidget
{

    public $libUrl = '/components/flowplayer';

    public $swfUrl = '/components/flowplayer/flowplayer-3.2.18.swf';

    public $path;

    public $config = [];

    public $width;

    public $height;

    public function init()
    {
        $this->config = [
            "plugins" => [
                'controls' => [
                    'autoHide' => true,
                ]
            ],
            'clip'=>[
                'url' => $this->path,
                "autoPlay" => false,
                "autoBuffering" => true,
                "scaling" => "fit",
            ],
            "mvideo" => [
                "id" => "core_media_flv",
                "fileurl" => $this->path,
                "width" => $this->width,
                "height" => $this->height,
                "autosize" => true,
                "resized" => false
            ],
            "playerId" => "core_media_flv",
            "playlist" => [
                [
                    "url" => $this->path,
                    "autoPlay" => false,
                    "autoBuffering" => true,
                    "scaling" => "fit",
                    "mvideo" => [
                        "id" => "core_media_flv",
                        "fileurl" => $this->path,
                        "width" => $this->width,
                        "height" => $this->height,
                        "autosize" => true,
                        "resized" => false
                    ]
                ]
            ]
        ];

    }

    public function run()
    {
        $view = $this->view;
        $initDefaults = Json::encode($this->config);
        $host = Yii::$app->urlManager->hostInfo;

        echo '<object  id="media" width="'.$this->width.'" height="'.$this->height.'" name="media" data="'.$host.$this->swfUrl.'" type="application/x-shockwave-flash">
                <param name="allowfullscreen" value="true">
                <param name="allowscriptaccess" value="always">
                <param name="quality" value="high">
                <param name="bgcolor" value="#000000">
                <param name="flashvars" value=\'config='.$initDefaults.'\'>
              </object>';
    }
}
