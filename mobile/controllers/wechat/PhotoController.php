<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/7/7
 * Time: 11:31
 */

namespace mobile\controllers\wechat;

use mobile\base\BaseMobileController;
use Yii;

class PhotoController extends BaseMobileController {
    public $layout = 'wechat';
    public $title;

    public function actionIndex() {
        $this->title = '图片笔记';
        return $this->render('photo');
    }
}