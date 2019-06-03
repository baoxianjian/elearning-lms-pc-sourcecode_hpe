<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/9/15
 * Time: 11:31 AM
 */

namespace backend\controllers;

use backend\services\CompanyService;
use backend\services\DomainService;
use backend\services\OrgnizationService;
use backend\services\PermissionService;
use Yii;
use backend\base\BaseBackController;
use common\services\framework\TreeNodeService;
use yii\base\Exception;
use yii\bootstrap\ActiveForm;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TreeNodeContentController extends BaseBackController{

    public $layout  = 'frame';

    public function actionUser()
    {
        $treeType = 'orgnization';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common','management');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }


    public function actionPosition()
    {
        $treeType = 'company';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common','management');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }


    public function actionRole()
    {
        $treeType = 'company';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common','management');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionUserRole()
    {
        $treeType = 'orgnization';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common','management');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionUserReportingManager()
    {
        $treeType = 'orgnization';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common','management');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionUserDirectReporter()
    {
        $treeType = 'orgnization';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common','management');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionCompanySetting()
    {
        $treeType = 'company';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common','management');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionUserOnline()
    {
        $treeType = 'orgnization';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common','search');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionActionLog()
    {
        $treeType = 'orgnization';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common','search');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionPoint()
    {
        $treeType = 'company';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common','rule').Yii::t('common','management');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionCertificationTemplate()
    {
        $treeType = 'company';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common','management');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionWechatTemplate()
    {
        $treeType = 'company';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common','management');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionCompanyMenu()
    {
        $treeType = 'company';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common','management');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'tree_type_not_exist'));
        }
    }

    public function actionWorkPlace()
    {
        $treeType = 'company';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common', 'management');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'TreeTypeNotExist'));
        }
    }

    public function actionCompanyDictionary()
    {
        $treeType = 'company';
        $contentName = $this->action->id;
        $service = new TreeNodeService();
        $treeTypeId = $service->getTreeTypeId($treeType);
        $functionName = Yii::t('common', 'management');
        if ($treeTypeId != "") {
            return $this->render('index', [
                'TreeType' => $treeType,
                'ContentName' => $contentName,
                'FunctionName' => $functionName,
            ]);
        } else {
            throw new NotFoundHttpException(Yii::t('common', 'TreeTypeNotExist'));
        }
    }
}