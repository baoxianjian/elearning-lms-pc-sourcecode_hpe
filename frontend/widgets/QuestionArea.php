<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 11:04
 */

namespace frontend\widgets;

use common\services\social\QuestionService;
use components\widgets\BaseWidget;
use Yii;

class QuestionArea extends BaseWidget
{
    private $view = '@frontend/views/widget/question-area.php';

    public $params = [];

    public function init()
    {
        $currentTime = time();
        $sessionKey = "QuestionAreaData";
        $questionArea = [];
        $lastLoadAt = null;
        if (Yii::$app->session->has($sessionKey)) {
            $questionArea = Yii::$app->session->get($sessionKey);
            $lastLoadAt = $questionArea["lastLoadAt"];
        }

        //为了性能，最后更新时间，60秒只读取一次
        if (empty($lastLoadAt) || ($currentTime - $lastLoadAt) > 60) {
            $uid = Yii::$app->user->getId();
            $service = new QuestionService();

            $data = $service->getNewQuestionList(3);

            $questionArea["lastLoadAt"] = $currentTime;
            $questionArea["data"] = $data;

            Yii::$app->session->set($sessionKey, $questionArea);
        } else {
            $data = $questionArea["data"];
        }

        if ($data != null) {
            $this->params['data'] = $data;
        }
    }

    public function run()
    {
        echo $this->renderFile($this->view, $this->params);
    }
}