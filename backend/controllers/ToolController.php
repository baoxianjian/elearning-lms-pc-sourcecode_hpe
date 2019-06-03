<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 8/22/2015
 * Time: 9:51 PM
 */

namespace backend\controllers;


use backend\base\BaseBackController;
use backend\viewmodels\CryptModel;
use backend\services\ExternalSystemService;
use common\models\framework\FwExternalSystem;
use common\models\framework\FwUser;
use common\services\framework\RbacService;
use common\crpty\CryptErrorCode;
use common\crpty\MessageCrypt;
use Yii;
use yii\filters\AccessControl;

class ToolController extends BaseBackController
{
    public $layout  = 'frame';

    public function actionCrypt()
    {
        $userId = Yii::$app->user->getId();
        $rbacService = new RbacService();
        $isSpecialUser = $rbacService->isSpecialUser($userId);
        $formType = $this->action->id;

        $externalSystemService = new ExternalSystemService();
        $companyId = null;
        if (!$isSpecialUser)
            $companyId = Yii::$app->user->identity->company_id;

        $externalSystemModel =$externalSystemService->getExternalSystemByCompanyId($companyId);

        $model = new CryptModel();
        $model->mode = "0";

        if (Yii::$app->request->isPost) {
            $bodyParams = Yii::$app->request->bodyParams['CryptModel'];

            if (!empty($bodyParams['system_id'])) {
                $model->system_id = $bodyParams['system_id'];
                $model->mode = $bodyParams['mode'];

                $systemModel = FwExternalSystem::findOne($model->system_id);
//                $systemKey = $systemModel->system_key;

                if ($model->mode == CryptModel::ENCRYPT) {
                    $message = $bodyParams['decrypt_message'];

                    $model->decrypt_message = $message;

                    $encodingKey = $systemModel->encoding_key;
                    $securityMode = $systemModel->security_mode;
                    $encryptMode = $systemModel->encrypt_mode;
                    if ($securityMode == FwExternalSystem::SECURITY_MODE_ENCRYPT && !empty($encodingKey)) {
                        $pc = new MessageCrypt();
                        $pc->MessageCrypt($encodingKey);

                        $tempResult = null;
                        $encrptCode = $pc->encryptMsg($encryptMode, $message, $tempResult);
                        if ($encrptCode == CryptErrorCode::OK) {
                            $result = $tempResult;
                        } else {
                            $result = CryptErrorCode::getCryptErrorMessage($encrptCode);
                        }
                        $model->encrypt_message = $result;
                    } else {
                        $model->encrypt_message = $message;
                    }
                } else {
                    $encryptMessage = $bodyParams['encrypt_message'];

                    $model->encrypt_message = $encryptMessage;

                    $encodingKey = $systemModel->encoding_key;
                    $securityMode = $systemModel->security_mode;
                    $encryptMode = $systemModel->encrypt_mode;
                    if ($securityMode == FwExternalSystem::SECURITY_MODE_ENCRYPT && !empty($encodingKey)) {
                        $pc = new MessageCrypt();
                        $pc->MessageCrypt($encodingKey);

                        $tempResult = null;
                        $encrptCode = $pc->decryptMsg($encryptMode, $encryptMessage, $tempResult);
                        if ($encrptCode == CryptErrorCode::OK) {
                            $result = $tempResult;
                        } else {
                            $result = CryptErrorCode::getCryptErrorMessage($encrptCode);
                        }
                        $model->decrypt_message = $result;
                    } else {
                        $model->decrypt_message = $encryptMessage;
                    }
                }
            }
        }

        return $this->render('crypt', [
            'externalSystemModel' => $externalSystemModel,
            'formType'=> $formType,
            'model'=>$model
        ]);
    }
}