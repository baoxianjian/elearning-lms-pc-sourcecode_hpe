<?php
namespace mobile\controllers;

use common\services\framework\UserService;
use mobile\base\BaseMobileController;
use Yii;
use yii\web\Response;

class IndexController extends BaseMobileController
{
    public $layout = 'main';

    public $static_root = '';
    public $title = '企业学习平台';
    public $token = null;
    public $hash = null;
    public $tplVals = [];
    public $scripts = null;
    public $isDev = false;
    
    public function init() {
        Yii::$app->response->format = 'html';
        $this->static_root = $this->getAssetsPath();
        $this->tplVals['static_root'] = $this->static_root;
        $this->tplVals['scripts'] = "<script src=\"$this->static_root/lib/tpl.js\"></script>".($this->isDev ? "<script src=\"$this->static_root/lib/jquery.js,t3.js,react-0.14.7.js,react-dom.js.merge().0.2.js\"></script><script src=\"$this->static_root/lib/babel.js\"></script>" : "<script src=\"$this->static_root/lib/jquery.js,t3.js,react.js,react-dom.js.merge().0.2.js\"></script>")."<script src=\"$this->static_root/proto/assets/js/amazeui.min.js\"></script><script src=\"$this->static_root/proto/assets/js/fastclick.js\"></script><script src=\"$this->static_root/proto/assets/js/main.js\"></script>".($this->isDev ? "<script type=\"text/babel\" src=\"$this->static_root/lib/template.raw/tpl.PlayExam.js\"></script>" : "<script src=\"$this->static_root/lib/template/tpl.PlayExam.js\"></script>")."<script src=\"$this->static_root/lib/api/interface.js\"></script><script src=\"$this->static_root/lib/weapp.js\"></script>";
        parent::init();
    }

    private function initToken() {
        $this->token = Yii::$app->session->get('access_token')['access_token'];
        $this->hash = Yii::$app->request->getQueryParams()['h'];
        Yii::$app->response->format = 'html';
    }

    public function actionIndex()
    {
        Yii::$app->response->format = 'html';
        return ['result','true'];
    }

    public function actionLesson_detail_online()
    {
        $this->initToken();
        $this->title = '学习平台 在线课程详情';
        return $this->render('lesson_detail_online', $this->tplVals);
    }

    public function actionLesson_list()
    {
        $this->initToken();
        $this->title = '学习平台 课程列表';
        return $this->render('lesson_list', $this->tplVals);
    }

    public function actionLesson_search()
    {
        $this->initToken();
        return $this->render('lesson_search', $this->tplVals);
    }

    public function actionPending_list()
    {
        $this->initToken();
        $this->title = '学习平台 待办事项';
        return $this->render('pending_list', $this->tplVals);
    }

    public function actionPlay_course()
    {
        $this->initToken();
        return $this->render('play_course', $this->tplVals);
    }

    public function actionDisplay()
    {
        $this->initToken();
        return $this->render('display', $this->tplVals);
    }

    public function actionSurvey_result()
    {
        $this->initToken();
        return $this->render('survey_result', $this->tplVals);
    }

    public function actionInvestigation_display()
    {
        $this->initToken();
        return $this->render('investigation_display', $this->tplVals);
    }

    public function actionExam_display()
    {
        $this->initToken();
        return $this->render('exam_display', $this->tplVals);
    }
/*
    public function actionOffline($message = null)
    {
        $this->layout = false;
        return $this->render('offline', ['message' => $message]);
    }


    public function actionTest()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
//        $user = new UserService();
       return ['user_name','admin'];
    }

    public function actionLogin()
    {
        $this->layout = 'login';

        return $this->render('display.html', [
            'model' => null,
        ]);
    }
*/
}