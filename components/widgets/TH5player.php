<?php

namespace components\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

class TH5player extends BaseWidget
{

    public $libUrl = '/components/h5player';

    public $audioList = [];

    public function init()
    {


    }

    public function run()
    {
        $view = $this->view;
        $cover = Yii::$app->urlManager->hostInfo.$this->libUrl.'/src/cover.jpg';

        echo html::cssFile('/vendor/bower/font-awesome/css/font-awesome.min.css');
        echo html::cssFile($this->libUrl . '/dist/ui.css');
        echo html::jsFile($this->libUrl . '/dist/player-with-css.min.js');
        echo html::jsFile($this->libUrl . '/dist/ui.js');

        $audioList = json_encode($this->audioList);

        $jsString = '<div id=\'player\'></div><script>
var player = new Player({
    container: document.getElementById(\'player\'),
    image: \''.$cover.'\'
});
player.setSongs('.$audioList.');
//player.play(0);
</script>';
        echo $jsString;
    }
}
