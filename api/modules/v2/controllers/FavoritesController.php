<?php
/**
 * Created by PhpStorm.
 * User: chenli
 * Date: 8/14/15
 * Time: 12:47 AM
 */

namespace api\modules\v2\controllers;



use api\base\BaseOpenApiController;
use common\helpers\TMessageHelper;
use common\models\social\SoCollect;
use common\services\social\CollectService;
use common\services\social\QuestionService;
use common\services\framework\UserService;
use yii;
use common\models\social\SoQuestionCare;

use common\services\api\UserService as ApiUserService;

class FavoritesController extends BaseOpenApiController
{
    public $modelClass = 'common\services\learning\CourseService';


    //检测是否收藏课程或问答
    public function actionHasFavorite(){
        $code = "hasFavorite";
        $userService = new ApiUserService($this->systemKey,$this->user);
        $params = Yii::$app->request->isGet ? Yii::$app->request->getQueryParams() : Yii::$app->request->getBodyParams();
        $params = $userService->parseParams($params,['object_id','type'],Yii::$app->request->isGet,true);
        $validator = $userService->validator($params, [
            'object_id' => 'required',
            'type' => 'required'
        ]);
        if (!$validator->success) {
            return $userService->exception(['code' => $code, 'number' => '001', 'param' => $validator->errors[0]['field']]);
        }
        $isFav = $userService->isMineFav($this->user->kid,$params['object_id'],$params['type']);
        return $userService->response([
            'code' => 'OK',
            'message' => $isFav ? '已收藏' : '未收藏',
            'data' => ["result"=> $isFav ? "have" : 'not']
        ]);
    }

    //添加课程收藏和问答收藏 type = 1 问答   type =2 课程
    public function actionAdd()
    {
        $code = "addFavorite";
        $userService = new ApiUserService($this->systemKey,$this->user);
        $params = Yii::$app->request->isGet ? Yii::$app->request->getQueryParams() : Yii::$app->request->getBodyParams();
        //var_dump($params);exit;
        $params = $userService->parseParams($params,['object_id','type'],Yii::$app->request->isGet,true);
        $validator = $userService->validator($params, [
            'object_id' => 'required',
            'type' => 'required'
        ]);

        if (!$validator->success) {
            return $userService->exception(['code' => $code, 'number' => '001', 'param' => $validator->errors[0]['field']]);
        }
        $isFav = $userService->isMineFav($this->user->kid,$params['object_id'],$params['type']);
        if($isFav) {
            $ret = $userService->removeFav($this->user->kid,$params['object_id'],$params['type']);
        } else {
            $ret = $userService->addFav($this->user->kid,$params['object_id'],$params['type']);
            if($ret) $this->curUserCheckActionForPoint($params['type'] == "1" ? 'Collect-Question':'Collect-Course', $this->systemKey, $params['object_id']);
        }
        $info = [
            'add' => [
                'success' => [
                    'val' => '收藏成功',
                    'key' => 'success'
                ],
                'fail' => [
                    'val' => '收藏失败',
                    'key' => 'failed'
                ]
            ],
            'remove' => [
                'success' => [
                    'val' => '取消收藏成功',
                    'key' => 'cancel_success'
                ],
                'fail' => [
                    'val' => '取消收藏失败',
                    'key' => 'cancel_failed'
                ]
            ]
        ];

        $msg = $info[$isFav ? 'remove' : 'add'][$ret ? 'success' : 'fail'];
        return $userService->response([
            'code' => 'OK',
            'message' => $msg['val'],
            'data' => ['result' => $msg['key']]
        ]);
    }


    public function actionQuestionCare()
    {
        $queryParams = Yii::$app->request->getQueryParams();
        $id = $queryParams["user_id"];
        $questionId = $queryParams["qid"];

        $service = new QuestionService();
        if ($service->isCare($id, $questionId) && $service->cancelCare($id, $questionId)) {
            return ['result' => 'success'];
        } else {
            $model = new SoQuestionCare();
            $model->user_id = $id;
            $model->question_id = $questionId;

            if ($model->saveAndUpdateQuestion()) {
                return ['result' => 'success'];
            } else {

                return ['result' => 'other', 'message' => 'failed'];
            }
        }

    }



    /**
     * 我的关注
     * @param $page
     * @param null $filter
     * @param null $time
     * @return string
     */
    public function actionGetAllAttention($page, $filter = null, $time = null)
    {

        $queryParams = Yii::$app->request->getQueryParams();
        $id = $queryParams["user_id"];

        $filter = $filter ? $filter : 3;

        $size = 10;

        $service = new UserService();
        $data = $service->getAttentionByUid($id, $filter, $time, $size, $page);


        return $data;
    }


    /**
     * 我的收藏
     * @param $page
     * @param null $time
     * @return string
     */
    public function actionGetCollect()
    {
        $code = "Todo";
        $userService = new ApiUserService($this->systemKey,$this->user);
        $params = Yii::$app->request->isGet ? Yii::$app->request->getQueryParams() : Yii::$app->request->getBodyParams();
        $params = $userService->parseParams($params,['page','type','current_time'],Yii::$app->request->isGet,true);
        $validator = $userService->validator($params, [
            'page' => 'required',
            'type' => 'required',
            'current_time' => 'required'
        ]);
        if (!$validator->success) {
            return $userService->exception(['code' => $code, 'number' => '001', 'param' => $validator->errors[0]['field']]);
        }

        $time = null;

        $userId = $this->user->kid;
        $size = 10;

        $service = new CollectService();
        $collect = $service->getPageDataByUserId($userId,intval($params['type']),$time,$size,$params['page'],$params['current_time']);
        
        return $userService->response(['code' => 'OK','data' => $collect]);
    }


}