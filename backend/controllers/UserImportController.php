<?php
/**
 * Created by PhpStorm.
 * FwUser: Alex Liu
 * Date: 7/22/16
 * Time: 10:49 PM
 */

namespace backend\controllers;

use backend\base\BaseBackController;
use backend\services\DomainService;
use backend\services\OrgnizationService;
use backend\services\UserService;
use common\helpers\TStringHelper;
use common\models\framework\FwCompany;
use common\models\framework\FwDomain;
use common\models\framework\FwOrgnization;
use common\models\framework\FwPosition;
use common\models\framework\FwUser;
use common\services\framework\UserCompanyService;
use common\services\framework\UserDomainService;
use common\services\framework\UserOrgnizationService;
use components\widgets\TPagination;
use Yii;
use yii\web\Response;

class UserImportController extends BaseBackController
{
    public $layout = 'frame';

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionUploadFile()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $physicalPath = rtrim(Yii::getAlias("@upload/user-import/"), '/\\') . "/";
        $logicalPath = "/upload/user-import/";
        if (!empty($_FILES)) {
            //得到上传的临时文件流
            $tempFile = $_FILES['myfile']['tmp_name'];

            $fileMd5 = md5_file($tempFile);

            $type = $_FILES['myfile']["type"];

            //得到文件原名
            $fileName = $_FILES["myfile"]["name"];
            $fileParts = pathinfo($_FILES['myfile']['name']);

            $fileError = $_FILES["myfile"]["error"];
            $fileSize = $_FILES["myfile"]["size"];

            //允许的文件类型
            $fileTypes = array(
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel');

            if ($fileError) {
                return ['result' => 'fail', 'errmsg' => Yii::t('common', 'upload_error')];
            } else if (!in_array($type, $fileTypes)) {
                return ['result' => 'fail', 'errmsg' => Yii::t('common', 'file_type_error')];
            } else {
                //最后保存服务器地址
                if (!is_dir($physicalPath)) {
                    mkdir($physicalPath);
                }

                $extension = '';

                switch ($type) {
                    case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                        $extension = "xlsx";
                        break;
                    case "application/vnd.ms-excel":
                        $extension = "xls";
                        break;
                }

                $newFileName = $fileMd5 . "." . $extension;
                if (move_uploaded_file($tempFile, $physicalPath . $newFileName)) {
                    $src = $physicalPath . $newFileName;
                    $info = $logicalPath . $newFileName;
                    return ['result' => 'success', 'path' => $info, 'md5' => $fileMd5];
                } else {
                    return ['result' => 'fail', 'errmsg' => Yii::t('common', 'upload_error')];
                }
            }
        } else {
            return ['result' => 'fail', 'errmsg' => '404_FILE_NOT_FOUND'];
        }
    }

    public function actionImportTemp()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $file = Yii::$app->request->post('file');
            $fileMd5 = Yii::$app->request->post('fileMd5');

            $sessionKey = "UserImport_" . $fileMd5;

            $path = Yii::$app->basePath . '/../' . $file;

            if (!file_exists(Yii::$app->basePath . '/../' . $file)) {
                return ['result' => 'fail', 'errmsg' => Yii::$app->basePath . '/../' . $file];
            } else {
                //读入上传文件
                $objPHPExcel = \PHPExcel_IOFactory::load($path);
                //内容转换为数组
                $sheet_0 = $objPHPExcel->getSheet(0)->toArray();

                $orgCache = [];
                $positionCache = [];

                if (!empty($sheet_0)) {
                    $companyId = Yii::$app->user->identity->company_id;

                    $domain = FwDomain::findOne(['company_id' => $companyId]);
                    $domainId = $domain->kid;
                    $data = array();
                    foreach ($sheet_0 as $i => $item) {
                        if ($i > 0) {
                            $data[$i]['row'] = $i + 1;
                            $data[$i]['op'] = trim($item[0]);
                            $data[$i]['user_name'] = trim($item[1]);
                            $data[$i]['real_name'] = trim($item[2]);
                            $data[$i]['email'] = trim($item[3]);
                            $data[$i]['is_manager'] = trim($item[6]) === '1' ? FwUser::MANAGER_FLAG_YES : FwUser::MANAGER_FLAG_NO;
                            $data[$i]['manager_account'] = trim($item[7]);

                            $item[4] = trim($item[4]);
                            $orgCacheKey = $companyId . '_' . $item[4];
                            if (array_key_exists($orgCacheKey, $orgCache)) {
                                $data[$i]['orgnization_name'] = $orgCache[$orgCacheKey]->orgnization_name;
                                $data[$i]['orgnization_id'] = $orgCache[$orgCacheKey]->kid;
                            } else {
                                $org = FwOrgnization::findOne(['company_id' => $companyId, 'orgnization_code' => $item[4]]);
                                $data[$i]['orgnization_name'] = $org->orgnization_name;
                                $data[$i]['orgnization_id'] = $org->kid;
                                $orgCache[$orgCacheKey] = $org;
                            }

                            $item[5] = trim($item[5]);
                            $positionCacheKey = $companyId . '_' . $item[5];
                            if (array_key_exists($positionCacheKey, $positionCache)) {
                                $data[$i]['position_name'] = $positionCache[$positionCacheKey]->position_name;
                                $data[$i]['position_id'] = $positionCache[$positionCacheKey]->kid;
                            } else {
                                $query = FwPosition::find(false);
                                $query->where('(company_id is null and position_code = :position_code) or (company_id = :company_id and position_code = :position_code)',
                                    [':company_id' => $companyId, ':position_code' => $item[5]]);
                                $position = $query->one();
                                $data[$i]['position_name'] = $position->position_name;
                                $data[$i]['position_id'] = $position->kid;
                                $positionCache[$positionCacheKey] = $position;
                            }

                            $data[$i]['domain_id'] = $domainId;
                            $data[$i]['company_id'] = $companyId;
                        }
                    }
                }
                if ($sessionKey) {
                    Yii::$app->session->set($sessionKey, $data);
                }
                return ['result' => 'success'];
            }
        } else {
            return ['result' => 'fail', 'errmsg' => ''];
        }
    }

    public function actionPreview()
    {
        $defaultSize = 10;

        $fileMd5 = Yii::$app->request->get('fileMd5');

        $sessionKey = "UserImport_" . $fileMd5;

        $data = Yii::$app->session->get($sessionKey);

        $count = count($data);
        if ($count > 0) {
            $page = new TPagination(['defaultPageSize' => $defaultSize, 'totalCount' => $count]);
            $result = array_slice($data, $page->offset, $page->limit);
            return $this->renderAjax('preview', [
                'data' => $result, 'page' => $page
            ]);
        } else {
            return $this->renderAjax('preview', [
                'data' => null, 'page' => null
            ]);
        }
    }

    public function actionSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            $file = Yii::$app->request->post('file');
            $fileMd5 = Yii::$app->request->post('fileMd5');

            $path = Yii::$app->basePath . '/../' . $file;

            $sessionKey = "UserImport_" . $fileMd5;

            if (Yii::$app->session->has($sessionKey)) {
                $data = Yii::$app->session->get($sessionKey);

                $service = new UserService();
                $result = $service->saveImport($data, $path, $fileMd5);
                
                if ($result === true) {
                    return ['result' => 'success'];
                } else {
                    return ['result' => 'fail', 'errmsg' => $result];
                }
            }
        }
    }
}