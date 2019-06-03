<?php

namespace api\modules\v1;

use yii\base\Module;

class ApiModule extends Module
{
    public $controllerNamespace = 'api\modules\v1\controllers';
    public function init()
    {
        parent::init();
        // custom initialization code goes here
    }
}
