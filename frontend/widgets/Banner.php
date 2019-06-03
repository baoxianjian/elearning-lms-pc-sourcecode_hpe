<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2016/3/4
 * Time: 11:43
 */

namespace frontend\widgets;

use components\widgets\BaseWidget;
use Yii;

class Banner extends BaseWidget
{
    private $resourceArray = ['certification', 'investigation', 'tag', 'task', 'teacher-manage', 'report', 'exam-manage-main', 'exam-paper-manage'];

    private $studentArray = ['index', 'view', 'play'];
    private $view = '@frontend/views/widget/banner.php';

    public $params = [];

    public function init()
    {
        $controllerId = Yii::$app->controller->action->controller->id;

        $actionId = Yii::$app->controller->action->id;

        $class = '';

        if ($controllerId === 'student' ||
            $controllerId === 'question' ||
            ($controllerId === 'resource/course' && in_array($actionId, $this->studentArray))
        ) {
            $class = 'headBanner4';
        } else if ($controllerId === 'teacher') {
            $class = 'headBanner6';
        } else if ($controllerId === 'resource' || strpos($controllerId, 'resource') !== false) {
            $class = 'headBanner5';
        } else if (in_array($controllerId, $this->resourceArray)) {
            $class = 'headBanner5';
        } else if ($controllerId === 'manager') {
            $class = 'headBanner3';
        }

        if (strpos(Yii::$app->language, 'en') === 0) {
            $class .= '_en';
        }

        $this->params['class'] = $class;
    }

    public function run()
    {
        echo $this->renderFile($this->view, $this->params);
    }
}
