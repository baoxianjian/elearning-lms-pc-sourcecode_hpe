<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/5/5
 * Time: 14:03
 */

namespace api\modules\v2\controllers;


use Yii;
use api\base\BaseOpenApiController;
use common\services\framework\TagService;
use common\services\framework\PointRuleService;
use yii\web\MethodNotAllowedHttpException;

class TagController extends BaseOpenApiController {
    public $modelClass = 'common\services\framework\TagService';
    /**
     * 所有标签
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function actionAll() {
        $companyId = Yii::$app->user->identity->company_id;
        $service = new TagService();
        $data = $service->getTagsByCategoryCode('course', $companyId);

        return empty($data) ? [] : $data;
    }

    /**
     * 用户已设置标签
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function actionOwn() {
        $userId = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;

        $service = new TagService();
        $userTagList = $service->getTagListByUserId($companyId, 'interest', $userId);
        
        return empty($userTagList) ? [] : $userTagList;
    }

    /**
     * 更新标签
     * @param tags 格式
     * tags[]:8B5B87D7-072D-4FDC-A8B9-CCDA27FC747E
     * tags[]:8B5B87D7-072D-4FDC-A8B9-CCDA27FC747E
     * 或者
     * tags=8B5B87D7-072D-4FDC-A8B9-CCDA27FC747E
     * tag=8B5B87D7-072D-4FDC-A8B9-CCDA27FC747E|8B5B87D7-072D-4FDC-A8B9-CCDA27FC747E
     * @return array
     * @throws MethodNotAllowedHttpException
     */
    public function actionUpdate() {
        if(Yii::$app->request->isGet) {
            throw new MethodNotAllowedHttpException('http post only');
        }
        $userId = Yii::$app->user->getId();
        $companyId = Yii::$app->user->identity->company_id;

        $service = new TagService();
        $tagList = Yii::$app->request->post('tags');
        if(!is_array($tagList)) {
            $tagList = strpos($tagList,"|") === false ? [$tagList] : explode("|",$tagList);
        }

        $service->saveUserInterestTags($userId, $companyId, $tagList);

        $pointRuleService = new PointRuleService();
        $pointResult = $pointRuleService->curUserCheckActionForPoint('Complete-Self-Info', 'Learning-Portal');

        return ['result' => 'success', 'pointResult' => $pointResult];
    }
}