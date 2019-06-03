<?php
/**
 * Created by PhpStorm.
 * User: TangMingQiang
 * Date: 3/5/15
 * Time: 10:31 PM
 */

namespace backend\controllers;


use backend\base\BaseBackController;
use backend\services\TreeTypeService;
use components\widgets\TPagination;
use Yii;
use yii\data\Pagination;
use yii\web\Response;

class DemoController  extends BaseBackController{

    public $layout  = 'frame';

    public function actionTree()
    {
//        $this->layout = 'content';
        //echo Yii::$app->user->getId().'<br/>';获取用户id
        //echo Yii::$app->user->identity->getUser();//获取用户名

        // echo Yii::$app->basePath;//获取应用根目录
//        if (true)
//        {
//            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
//            throw new NotFoundHttpException("test");
//        }
        return $this->render('tree');
    }

    public function actionAjaxForm()
    {
        $this->layout = 'none';
        return $this->render('ajax-form');
    }




    public function actionModalValidate()
    {
//        $this->layout = 'content';
        return $this->render('modal-validate');
    }

    public function actionModalEditor()
    {
        $this->layout = 'modalWin';
        return $this->render('modal-editor');
    }

    public function actionKindeditor()
    {
//        $this->layout = 'content';
        return $this->render('kindeditor');
    }

    public function actionDatePicker()
    {
        return $this->render('date-picker');
    }

    public function actionTkindeditor()
    {
//        $this->layout = 'content';
        return $this->render('Tkindeditor');
    }

    public function actionFrameValidateIndex()
    {
//        $this->layout = 'content';
        return $this->render('frame-validate-index');
    }

    public function actionFrameValidateList()
    {
        $this->layout = 'yii';
        return $this->render('frame-validate-list');
    }

    public function actionFrameValidateDetail()
    {
        $this->layout = 'yii';
        return $this->render('frame-validate-detail');
    }

    public function actionJqueryValidate()
    {
        $this->layout = 'yii';
        return $this->render('jquery-validate');
    }

    public function actionData($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($id == "#") {
//        $result = [
//      [ 'attributes'=> [ 'id' => 'pjson_1' ], 'state'=> 'open', 'data'=> 'Root node 1', 'children' =>
//          ['attributes'=> [ 'id' => 'pjson_2' ],  'data'=> ['title'=>'Custom icon','icon' => '../media/images/ok.png']]]];

            $result =[
                [
                    'id' => '1',
                    'text' => 'Root node 1',
                    'state' => ['opened' => true],
                    'children' => [
                        [
                            'id' => '2',
                            'text' => 'Child node 1',
                            'state' => ['selected' => true],
                            'icon' => 'jstree-file',
                            'children' => true
                        ],
                        [
                            'id' => '3',
                            'text' => 'Child node 2',
                            'state' => ['disabled' => true]
                        ],
                        ['id' => '4',
                            'text' => 'Child node 3']

                    ]
                ],
                [
                    'id' => '2',
                    'text' => 'Root node 2'

                ]
            ];
        }
        else  {
            $result =
                [
                    'text' => 'Child node 1',
                    'icon' => 'jstree-file',
                    'children' => true
                ];
        }


        return $result;
    }

    public function actionTest()
    {
//        if (array_key_exists('HTTP_X_PJAX', $_SERVER) && $_SERVER['HTTP_X_PJAX']) {
            $service = new TreeTypeService();
            $dataProvider = $service->search(Yii::$app->request->queryParams);
            $count = $dataProvider->totalCount;
            $page = new TPagination(['defaultPageSize' => $this->defaultPageSize, 'totalCount' => $count]);
            $dataProvider->setPagination($page);
            return $this->render('pjax', [
                'page' => $page,
                'searchModel' => $service,
                'dataProvider' => $dataProvider,
            ]);
//        return $this->renderPartial("pjax") ;
//        }
    }

    public function actionFram()
    {

    }
    public function actionPjax()
    {
        $this->layout  = 'content';
        return $this->render('pjax', [
            'dataProvider' => null,
        ]);
    }

    //批量上传组件demo
    public function actionUploadify(){
        return $this->render('uploadify', [
            'dataProvider' => null,
        ]);
    }

    public function actionUpload(){

        var_dump(Yii::$app->request);
        // Define a destination
        $targetFolder = '/Users/kaylio/work'; // Relative to the root

        $verifyToken = md5('unique_salt' . $_POST['timestamp']);

        if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
            $targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];

            // Validate the file type
            $fileTypes = array('jpg','jpeg','gif','png'); // File extensions
            $fileParts = pathinfo($_FILES['Filedata']['name']);

            if (in_array($fileParts['extension'],$fileTypes)) {
                move_uploaded_file($tempFile,$targetFile);
                echo '1';
            } else {
                echo 'Invalid file type.';
            }
        }
    }

    //批量上传组件demo
    public function actionUploadifive(){
        return $this->render('uploadifive');
    }

    public function actionUploadFile(){

//        var_dump(Yii::$app->request);
        // Set the uplaod directory
        $uploadDir = '/upload/';

// Set the allowed file extensions
        $fileTypes = array('jpg', 'jpeg', 'gif', 'png','doc'); // Allowed file extensions

        $verifyToken = md5('unique_salt' . $_POST['timestamp']);

        if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
            $tempFile   = $_FILES['Filedata']['tmp_name'];
            $uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
            $targetFile = $uploadDir . $_FILES['Filedata']['name'];

            // Validate the filetype
            $fileParts = pathinfo($_FILES['Filedata']['name']);
            if (in_array(strtolower($fileParts['extension']), $fileTypes)) {

                // Save the file
                move_uploaded_file($tempFile, $targetFile);
                return 1;

            } else {

                // The file type wasn't allowed
                return 'Invalid file type.';

            }
        }
    }

    public function actionCheckExists()
    {
//        var_dump(Yii::$app->request);
        $targetFolder = '/upload/'; // Relative to the root

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $targetFolder  . $_POST['filename'])) {
            return 1;
        } else {
            return 0;
        }
    }
}