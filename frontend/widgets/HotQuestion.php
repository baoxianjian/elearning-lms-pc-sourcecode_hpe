<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 11:32
 */

namespace frontend\widgets;

use components\widgets\BaseWidget;
use common\services\social\QuestionService;
use Yii;

class HotQuestion extends BaseWidget
{
    private $view = '@frontend/views/widget/hot-question.php';

    
    public $params = [];

    public function init()
    {
    	$companyId = Yii::$app->user->identity->company_id;
    	$service = new QuestionService();
    	$data = $service->getHotQuestionList($companyId);
    	if($data!=null){
    		$this->params['data'] = $data;
    	}
    }

    public function run()
    {
        echo $this->renderFile($this->view, $this->params);
    }
}