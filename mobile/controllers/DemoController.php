<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/6/27
 * Time: 10:29
 */

namespace mobile\controllers;
use mobile\base\BaseMobileController;

class DemoController extends BaseMobileController{
    public $title;
    public $layout = 'demo';
    
    public function actionVoice() {

        return $this->render('demo/voice');
    }
}