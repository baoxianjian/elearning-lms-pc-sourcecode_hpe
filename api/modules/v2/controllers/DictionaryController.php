<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 2/22/15
 * Time: 10:49 PM
 */

namespace api\modules\v2\controllers;

use api\base\BaseOpenApiController;
use common\services\framework\ServiceService;
use Yii;

use common\services\api\DictionaryService;

class DictionaryController extends BaseOpenApiController{

    public $modelClass = 'common\services\api\DictionaryService';

    /**
     * 获取字典分类信息接口
     * 获取所有字典分类信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetDictionaryCategory()
    {
        $apiDictionaryService = new DictionaryService($this->systemKey);
        if(!$this->systemKeyCheck) {
            return $apiDictionaryService->exception(['code' => 'common','number' => '002']);
        }
        $commonServiceSerivce = new ServiceService();
        $isServiceRunning = $commonServiceSerivce->isServiceRunning($this->serviceId);
        if(!$isServiceRunning) {
            return $apiDictionaryService->exception(['code' => 'common','number' => '005']);
        }

        $categories = $apiDictionaryService->categories();
        if(empty($categories)) {
            return $apiDictionaryService->exception([
                'code' => $this->action->id,
                'number' => '006',
                'name' => Yii::t('common', 'data_not_exist')
            ]);
        }
        return $apiDictionaryService->response(['code' => 'OK','data' => ['category' => $categories]]);
    }

    /**
     * 获取相关字典信息接口
     * 根据字典分类获取所有字典信息
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetDictionary()
    {
        $apiDictionaryService = new DictionaryService($this->systemKey);
        if (!$this->systemKeyCheck) {
            return $apiDictionaryService->exception(['code' => 'common', 'number' => '002']);
        }
        $params = Yii::$app->request->isGet ? Yii::$app->request->getQueryParams() : Yii::$app->request->getRawBody();
        $params = $apiDictionaryService->parseParams($params, ['cate_code'], Yii::$app->request->isGet);
        $validator = $apiDictionaryService->validator($params, [
            'cate_code' => 'required'
        ]);
        if (!$validator->success) {
            return $apiDictionaryService->exception(['code' => 'common', 'number' => '001', 'param' => $validator->errors[0]['field']]);
        }
        $commonServiceSerivce = new ServiceService();
        $isServiceRunning = $commonServiceSerivce->isServiceRunning($this->serviceId);
        if (!$isServiceRunning) {
            return $apiDictionaryService->exception(['code' => 'common', 'number' => '005']);
        }
        $dictionaries = $apiDictionaryService->dictionaries($params['cate_code'], null, ['kid' => 'dictionary_id']);
        if (empty($dictionaries)) {
            return $apiDictionaryService->exception(['number' => '006', 'code' => $this->action->id, 'name' => Yii::t('common', 'data_not_exist')]);
        }

        return $apiDictionaryService->response([
            'code' => 'OK',
            'data' => ['dictionary' => $dictionaries]
        ]);
    }
}