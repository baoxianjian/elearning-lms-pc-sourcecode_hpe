<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/6/15
 * Time: 11:32
 */

namespace frontend\widgets;

use components\widgets\BaseWidget;
use common\services\framework\TagService;
use Yii;

class HotQuestionTag extends BaseWidget
{
    private $view = '@frontend/views/widget/hot-question-tag.php';

    public $params = [];

    public function init()
    {
        $size=!empty($_GET['size'])?intval($_GET['size']):20;
        $cateCode = !empty($_GET['catecode'])?$_GET['catecode']:'conversation';
        $companyId = Yii::$app->user->identity->company_id;

        $service = new TagService();

        $data = $service->getHotTagsByName($companyId,$cateCode,$size);

        if($data!=null){
            $this->params['data'] = $data;
        }
    }

    public function run()
    {
        echo $this->renderFile($this->view, $this->params);
    }
}