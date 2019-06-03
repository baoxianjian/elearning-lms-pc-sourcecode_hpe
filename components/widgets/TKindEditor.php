<?php

namespace components\widgets;

use yii\web\View;

class TKindEditor extends BaseWidget
{

    public $libUrl = '/components/kindeditor';

    public $input = null;

    public $editorId = null;

    public $defaultParams = [
        'allowFileManager' => 'true',
    ];

    public function init()
    {
    }

    public function run()
    {
        $view = $this->view;

        if (!isset($view->params['__KindEditor'])) {
            $view->registerCssFile($this->libUrl . '/plugins/code/prettify.css');
            $view->registerCssFile($this->libUrl . '/themes/default/default.css');
            $view->registerJsFile($this->libUrl . '/plugins/code/prettify.js');
            $view->registerJsFile($this->libUrl . '/kindeditor-all-min.js');
            $view->registerJsFile($this->libUrl . '/lang/zh_CN.js');

            $view->params['__KindEditor'] = true;
        }

        if ($this->input === null) {
            $this->input = '#' . $this->id;
        }

        if ($this->editorId === null) {
            $this->editorId = 'editor_' . str_replace(['#', '-'], ['', '_'], $this->input);
        }

        $this->params = array_merge($this->defaultParams, $this->params);

        $paramsString = '';
        foreach ($this->params as $name => $value) {
            if (is_string($value)) {
                $paramsString .= $name . ' : \'' . $value . "',\r\n";

            } else {
                $paramsString .= $name . ' : ' . $value . ",\r\n";

            }
        }

        $jsString = <<<JS
var $this->editorId;
KindEditor.ready(function(K) {
	$this->editorId = K.create('$this->input', {
		$paramsString
	});
	prettyPrint();
});
JS;
        $view->registerJs($jsString, View::POS_END);
    }
}
