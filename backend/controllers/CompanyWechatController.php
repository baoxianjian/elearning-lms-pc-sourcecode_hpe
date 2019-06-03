<?php

namespace backend\controllers;

use common\services\framework\CompanyService;
use common\models\framework\FwCompanyWechat;
use common\services\framework\RbacService;
use common\services\framework\TreeNodeService;
use common\models\treemanager\FwTreeNode;
use common\services\framework\UserCompanyService;
use common\base\BaseActiveRecord;
use common\helpers\TFileHelper;
use Yii;
use backend\base\BaseBackController;
use common\models\framework\FwCompany;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * CompanyWechatController implements the CRUD actions for FwCompanyWechat model.
 */
class CompanyWechatController extends BaseBackController
{
    public $layout  = 'frame';

    public function actionIndex()
    {
        $userId = Yii::$app->user->getId();
        $userCompanyService = new UserCompanyService();

        $companyList = $userCompanyService->getManagedListByUserId($userId, null, false);
        return $this->render('index', [
            'companyList' => $companyList
        ]);
    }

    public function actionSetting($companyId)
    {
        $this->layout = 'list';
        $companyService = new CompanyService();
        $model = $companyService->getCompanyWechat($companyId);
        if (empty($model)) {
            $model = new FwCompanyWechat();
            $model->company_id = $companyId;
            $model->mp_type = FwCompanyWechat::MP_TYPE_SUBSCRIBE;
            $model->is_authenticated = FwCompanyWechat::NO;
            $model->security_mode = FwCompanyWechat::SECURITY_MODE_PLAIN;
            $model->status = FwCompanyWechat::STATUS_FLAG_NORMAL;
            $model->action_token = md5($companyId);
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->save()) {
                $sessionKey = "WechatAccessToken-" . $companyId;
                BaseActiveRecord::removeFromCache($sessionKey);

                return ['result' => 'success'];
            } else {
                return ['result' => 'failure'];
            }
        } else {
            $wechatUrl = Yii::$app->urlManager->createAbsoluteUrl(["service/wechat-service","companyId"=>$model->company_id]);
            $wechatUrl = str_replace("/backend/","/",$wechatUrl);
            $model->server_url = $wechatUrl;

            return $this->renderAjax('setting', [
                'model' => $model
            ]);
        }
    }

}
