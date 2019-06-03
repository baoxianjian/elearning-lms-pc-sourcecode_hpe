<?php
/**
 * Created by PhpStorm.
 * User: Alex Liu
 * Date: 5/3/2016
 * Time: 3:01 PM
 */

namespace backend\controllers;


use backend\base\BaseBackController;
use common\helpers\TFileHelper;
use Yii;
use yii\web\Response;

class CacheController extends BaseBackController
{
    public $layout = 'frame';

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionClean()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $cleanData = (boolean)Yii::$app->request->post('data', false);
            $cleanStatic = (boolean)Yii::$app->request->post('static', false);

            if ($cleanData) {
                Yii::$app->cache->flush();
                if (isset(Yii::$app->cache->cachePath) && is_dir(Yii::$app->cache->cachePath)) {
                    TFileHelper::removeDir(Yii::$app->cache->cachePath);
                }
            }

            if ($cleanStatic) {
                // todo
            }
            $msgStr= Yii::t('backend','clear_finished');
            return ['result' => "$msgStr"];
        }
    }
}