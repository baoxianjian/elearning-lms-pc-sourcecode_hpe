<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 11:32
 */

namespace frontend\widgets;

use components\widgets\BaseWidget;

class QuickChannel extends BaseWidget
{
    private $view = '@frontend/views/widget/quick-channel.php';

    public $params = [];

    public function init()
    {

    }

    public function run()
    {
        echo $this->renderFile($this->view, $this->params);
    }
}