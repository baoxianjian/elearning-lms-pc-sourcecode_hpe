<?php

namespace backend\controllers;

use backend\services\CompanyMenuService;
use backend\services\CompanyService;
use backend\services\CompanySettingService;
use common\services\framework\DictionaryService;
use common\services\framework\TreeNodeService;
use common\models\treemanager\FwTreeNode;
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
 * CompanyController implements the CRUD actions for FwCompany model.
 */
class CompanyController extends BaseBackController
{
    /**
     * Displays a single FwCompany model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $service = new CompanyService();
        $companyId = $service->getCompanyIdByTreeNodeId($id);

        if ($companyId != null) {
            $model = $this->findModel($companyId,true);
        }
        else
        {
            $model = new FwCompany();
        }
        return $this->renderAjax('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new FwCompany model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($parentNodeId)
    {
//        $this->layout = 'modalWin';
        $service = new CompanyService();
        $model = new FwCompany();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->tree_node_id = Yii::$app->request->getQueryParam("resultId");

            $treeNodeModel = FwTreeNode::findOne($model->tree_node_id);
            if ($treeNodeModel != null) {
                $model->company_name = $treeNodeModel->tree_node_name;
                $model->company_code = $treeNodeModel->tree_node_code;
            }

//            $model->tree_node_id = $resultId;

//            $treeNodeService = new TreeNodeService();
//            $parentNodeId = $treeNodeService->findOne($model->tree_node_id)->parent_node_id;

            if ($parentNodeId != "-1") {
                $parentCompanyId = $service->getCompanyIdByTreeNodeId($parentNodeId);
                $model->parent_company_id = $parentCompanyId;
            }

            if (!empty($model->second_level_domain)) {
                if ($service->isExistSameSecondLevelDomain(null, $model->second_level_domain)) {
                    $treeNodeModel->delete();//如果存在相同二级域名需要删除树节点，以便重新创建数据
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'second_level_domain')])];
                }
            }
            $model->needReturnKey = true;
            if ($model->save()) {
                TFileHelper::writeCachedSitesFile($model->kid, null, $model->second_level_domain);
                return ['result' => 'success'];
            }else {
                return ['result' => 'failure'];
            }
        } else {
            $dictionaryService = new DictionaryService();
            $themeModel = $dictionaryService->getDictionariesByCategory('theme');
            $languageModel = $dictionaryService->getDictionariesByCategory('language');

            $model->reporting_model = FwCompany::REPORTING_MODEL_LINE_MANAGER;
            $model->default_portal = FwCompany::USER_PORTAL;
            $model->limited_user_number = 0;
            $model->limited_domain_number = 0;

            $defaultTheme = $dictionaryService->getDictionaryValueByCode("system", "default_theme");
            $defaultLanguage = $dictionaryService->getDictionaryValueByCode("system","default_language");
            $defaultIsSelfRegister = $dictionaryService->getDictionaryValueByCode("system","is_self_register");

            $model->theme = $defaultTheme;
            $model->language = $defaultLanguage;

            if (!empty($defaultIsSelfRegister)) {
                $model->is_self_register = $defaultIsSelfRegister;
            }
            else {
                $model->is_self_register = FwCompany::NO;
            }

            $model->is_default_company = FwCompany::NO;
            $model->register_mode = FwCompany::REGISTER_MODE_MAIL;

            return $this->renderAjax('create', [
                'model' => $model,
                'themeModel'=>$themeModel,
                'languageModel'=>$languageModel
            ]);
        }
    }

    /**
     * Updates an existing FwCompany model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
//        $this->layout = 'modalWin';
        $service = new CompanyService();
        $companyId = $service->getCompanyIdByTreeNodeId($id);
        $oldDomain = null;

        if ($companyId == null) {
            $model = new FwCompany();
            $model->tree_node_id = $id;

            $treeNodeService = new TreeNodeService();
            $parentNodeId = $treeNodeService->findOne($model->tree_node_id)->parent_node_id;

            if ($parentNodeId != null) {
                $parentCompanyId = $service->getCompanyIdByTreeNodeId($parentNodeId);
                $model->parent_company_id = $parentCompanyId;
            }
        } else {
            $model = $this->findModel($companyId);
        }


        if (!empty($model))
            $oldDomain = $model->second_level_domain;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $treeNodeModel = FwTreeNode::findOne($model->tree_node_id);
            if ($treeNodeModel != null) {
                $model->company_name = $treeNodeModel->tree_node_name;
                $model->company_code = $treeNodeModel->tree_node_code;
            }
//            $model->tree_node_id = Yii::$app->request->getQueryParam("resultId");
//            $model->tree_node_id = $resultId;

            if (!empty($model->second_level_domain)) {
                if ($service->isExistSameSecondLevelDomain($model->kid, $model->second_level_domain)) {
                    return ['result' => 'other', 'message' => Yii::t('common', 'exist_same_code_{value}',
                        ['value' => Yii::t('common', 'second_level_domain')])];
                }
            }
            $model->needReturnKey = true;
            if ($model->save()) {

                $cacheLanguageKey = "Company_Language_" . $model->kid;
                $cacheThemeKey = "Company_Theme_" . $model->kid;
                BaseActiveRecord::saveToCache($cacheLanguageKey, $model->language);
                BaseActiveRecord::saveToCache($cacheThemeKey, $model->theme);

                TFileHelper::writeCachedSitesFile($model->kid, $oldDomain, $model->second_level_domain);
                return ['result' => 'success'];
            } else {
                return ['result' => 'failure'];
            }
        } else {
            $dictionaryService = new DictionaryService();
            $themeModel = $dictionaryService->getDictionariesByCategory('theme');
            $languageModel = $dictionaryService->getDictionariesByCategory('language');

            return $this->renderAjax('update', [
                'model' => $model,
                'themeModel' => $themeModel,
                'languageModel' => $languageModel
            ]);
        }
    }

    /**
     * Deletes an existing FwCompany model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete()) {
            $companyService = new CompanyService();
            $companyService->deleteRelateInfoByCompanyId($id);

            TFileHelper::writeCachedSitesFile($id, null, null, true);
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the FwCompany model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return FwCompany the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id,$loadEnCode = false)
    {
        if (($model = FwCompany::findOne($id,false,true,false,$loadEnCode)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('common','data_not_exist'));
        }
    }


    public function actionUpload()
    {
        // $physicalPath = Yii::$app->basePath."/../upload/temp/";
        $physicalPath = rtrim(Yii::getAlias("@upload/companylogo/"), '/\\') . "/";
        $logicalPath = "/upload/companylogo/";
        if (!empty($_FILES)) {

            //得到上传的临时文件流
            $tempFile = $_FILES['myfile']['tmp_name'];

            $type = $_FILES['myfile']["type"];

            //得到文件原名
            $fileName = $_FILES["myfile"]["name"];
            $fileParts = pathinfo($_FILES['myfile']['name']);

            $fileError = $_FILES["myfile"]["error"];
            $fileSize = $_FILES["myfile"]["size"];

            //允许的文件后缀
            $fileTypes = array(
                'image/jpg',
                'image/jpeg',
                'image/png',
                'image/pjpeg',
                'image/gif',
                'image/bmp',
                'image/x-png');

            if ($fileError) {
                $info = Yii::t('common', 'upload_error');
//                $status=0;
//                $data='';
            } else if (!in_array($type, $fileTypes)) {
                $info = Yii::t('common', 'file_type_error');
//                $status=0;
//                $data='';
            } else {
                //最后保存服务器地址
                if (!is_dir($physicalPath)) {
                    mkdir($physicalPath);
                }

                $extension = 'jpg';

//                switch ($type)
//                {
//                    case "image/jpg":$extension="jpg";break;
//                    case "image/jpeg":$extension="jpg";break;
//                    case "image/pjpeg":$extension="jpg";break;
//                    case "image/x-png":$extension="png";break;
//                    case "image/png":$extension="png";break;
//                    case "image/gif":$extension="gif";break;
//                    case "image/bmp":$extension="bmp";break;
//                    case "image/x-ms-bmp":$extension="bmp";break;
//                    case "image/x-bmp":$extension="bmp";break;
//                }

                $newFileName = time() . "." . $extension;
                if (move_uploaded_file($tempFile, $physicalPath . $newFileName)) {
                    $info = $logicalPath . $newFileName;
//                    $status = 1;
//                    $data = array('path' => Yii::$app->basePath, 'file' => $physicalPath . $newFileName);
                } else {
                    $info = Yii::t('common', 'upload_error');
//                    $status = 0;
//                    $data = '';
                }
            }
            echo $info;
        }

    }
}
