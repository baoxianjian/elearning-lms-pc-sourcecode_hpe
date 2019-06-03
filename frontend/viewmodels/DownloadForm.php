<?php
/**
 * Created by PhpStorm.
 * User: LiuCheng
 * Date: 2015/8/21
 * Time: 13:52
 */
namespace frontend\viewmodels;

use yii\base\Model;
use Yii;

class DownloadForm extends Model
{
    public $type;
    public $id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // user_name and password are both required
            [['id', 'type'], 'required','message'=>'{attribute}不能为空！'],
        ];
    }
}