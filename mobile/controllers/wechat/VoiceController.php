<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/6/27
 * Time: 12:46
 */

namespace mobile\controllers\wechat;

use mobile\base\BaseMobileController;
use Yii;

class VoiceController extends BaseMobileController {
    public $layout = 'wechat';
    public $title;
    
    public function actionIndex() {
        $this->title = '语音笔记';
        return $this->render('voice');
    }

    public function actionDownload() {
        
    }
    
    public function actionRecord() {
        Yii::$app->response->format = 'json';
        return [];
    }
}