<?php

namespace common\base;

use Yii;
use yii\base\Action;

/**
 * Site controller
 */
class BaseAction extends Action
{

	public $channels;

	public $rootChannels;

	public function init()
	{
		parent::init();
	}



	public function render($view, $params = [])
	{
		return $this->controller->render($view, $params);
	}

	public function redirect($url, $statusCode = 302)
	{
		return $this->controller->redirect($url, $statusCode);
	}
}
