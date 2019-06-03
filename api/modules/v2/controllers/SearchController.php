<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/5/11
 * Time: 11:33
 */

namespace api\modules\v2\controllers;

use api\base\BaseOpenApiController;
use Yii;
use common\services\api\SearchService;
use common\services\api\UserService;

define('HIGHLIGHT_STYLE',1);

class SearchController extends BaseOpenApiController
{
    public $modelClass = 'common\services\api\SearchService';

    /**
     * 搜索
     * query params： type, q, offset, limit, record_type(optional)
     * @param $type
     * @return array
     * @throws \yii\web\MethodNotAllowedHttpException
     */
    public function actionAll($type,$raw = false) {
        $params = Yii::$app->request->getQueryParams();
        $record_type = null;
        if(isset($params['record_type']) && !empty($params['record_type'])) {
            $record_type = $record_type['record_type'];
        }
        $service = new SearchService($this->user,$this->systemKey);
        $response = $service->get($type ? $type : $params['type'],$params['q'],$params['offset'],$params['limit'],true,$record_type);
        
        $userService = new UserService($this->systemKey,$this->user);
        $userService->increaseIntegral('Search');

        return $raw ? $response : [
            'code' => 'OK',
            'result' => json_encode($response['data'])
        ];
    }

    public function actionCount() {
        $course = $this->actionAll('course',true);
        $question = $this->actionAll('question',true);
        $people = $this->actionAll('person',true);
        return [
            'code' => 'OK',
            'result' => json_encode([
                'course' => $course,
                'question' => $question,
                'person' => $people
            ])
        ];
    }

    /**
     * 课程
     * @return array
     */
    public function actionCourse() {
        return $this->actionAll('course');
    }

    /**
     * 问答
     * @return array
     */
    public function actionQuestion() {
        return $this->actionAll('question');
    }

    /**
     * 用户
     * @return array
     */
    public function actionPerson() {
        return $this->actionAll('person');
    }

    /**
     * 分享
     * @return array
     */
    public function actionShare() {
        return $this->actionAll('share');
    }

    /**
     * 历史
     * @return array
     */
    public function actionHistory() {
        return $this->actionAll('history');
    }

    /**
     * 标签
     * @return array
     */
    public function actionTag() {
        return $this->actionAll('tag');
    }
}