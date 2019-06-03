<?php

namespace components\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

class TUploadifive extends BaseWidget
{

    public $libUrl = '/components/uploadifive';

    public $data = [];

    public $name = 'uploadifive';

    public $core = [];

    public $css;

    public $scriptinit;


    public function init()
    {
        $timestamp = time();
        $this->data = [
            'auto' => false,
            'formData' => [
                '_csrf'=> Yii::$app->request->csrfToken,
                'timestamp' => $timestamp,
                'token'     => md5('unique_salt'.$timestamp),

            ],
            'queueID'=> 'queue',
        ];


    }

    public function run()
    {
        $view = $this->view;

        echo html::jsFile($this->libUrl.'/jquery.uploadifive.js');
        echo html::cssFile($this->libUrl.'/uploadifive.css');

        $this->core = array_merge($this->data, $this->core);
        $initDefaults = Json::encode($this->core);

        $jsString = <<<JS
<script>
$(function() {
    $this->scriptinit;
    $('#$this->name').uploadifive($initDefaults);
});
</script>
JS;
        echo $jsString;
        //$view->registerJs($jsString, View::POS_END);
    }
}
