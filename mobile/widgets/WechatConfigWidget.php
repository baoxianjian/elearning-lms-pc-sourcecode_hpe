<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/6
 * Time: 14:23
 */

namespace mobile\widgets;

use yii\base\Widget;

class WechatConfigWidget extends Widget {

    private $config = [];
    public $shareData;
    
    public function init() {
        parent::init();
        if($this->shareData === null) {
            $this->shareData = [
                'title' => '',
                'desc' => '',
                'link' => '',
                'imgUrl' => '',
                'success' => 'successCallback',
                'cancel' => 'cancelCallback'
            ];
        }
    }

    public function run() {
        return $this->render('@mobile/views/widgets/wechat-js-sdk-cfg',['config' => $this->config,'shareData' => $this->shareData]);
    }
}