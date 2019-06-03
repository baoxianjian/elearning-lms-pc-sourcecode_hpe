<?php

namespace common;

use PDO;
use yii\helpers\VarDumper;
use Yii;
use yii\helpers\Url;
use yii\data\Pagination;
use common\helpers\TFileHelper;

class eLearningLMS
{

	public static function getApp()
	{
		return \Yii::$app;
	}

	public static function getView()
	{
		$view = \Yii::$app->getView();
		return $view;
	}

	public static function getRequest()
	{
		return \Yii::$app->request;
	}

	public static function getResponse()
	{
		return \Yii::$app->response;
	}

	public static function getBaseUrl($url = null)
	{
		$baseUrl = \Yii::$app->request->getBaseUrl();
		if($url !== null)
		{
			$baseUrl .= $url;
		}
		return $baseUrl;
	}

	public static function getHomeUrl($url = null)
	{
		$homeUrl = \Yii::$app->getHomeUrl();
		if($url !== null)
		{
			$homeUrl .= $url;
		}
		return $homeUrl;
	}

	public static function getWebUrl($url = null)
	{
		$webUrl = \Yii::getAlias('@web');
		if($url !== null)
		{
			$webUrl .= $url;
		}
		return $webUrl;
	}

	public static function getWebPath($path = null)
	{
		$webPath = \Yii::getAlias('@webroot');
		if($path !== null)
		{
			$webPath .= $path;
		}
		return $webPath;
	}

	public static function getAppParam($key, $defaultValue = null)
	{
		$params = \Yii::$app->params;
		if(array_key_exists($key,$params))
		{
			return $params[$key];
		}
		return $defaultValue;
	}

	public static function setAppParam($array)
	{
		foreach($array as $key => $value)
		{
			\Yii::$app->params[$key] = $value;
		}
	}

	public static function getViewParam($key, $defaultValue = null)
	{
		$view = \Yii::$app->getView();
		if(isset($view->params[$key]))
		{
			return $view->params[$key];
		}
		return $defaultValue;
	}

	public static function setViewParam($array)
	{
		$view = \Yii::$app->getView();
		foreach($array as $name => $value)
		{
			$view->params[$name] = $value;
		}
	}

	public static function hasGetValue($key)
	{
		return isset($_GET[$key]);
	}

	public static function getGetValue($key, $default = NULL)
	{
		if(self::hasGetValue($key))
		{
			return $_GET[$key];
		}
		return $default;
	}

	public static function hasPostValue($key)
	{
		return isset($_POST[$key]);
	}

	public static function getPostValue($key, $default = NULL)
	{
		if(self::hasPostValue($key))
		{
			return $_POST[$key];
		}
		return $default;
	}


	public static function getUser()
	{
		return Yii::$app->user;
	}

	public static function getIdentity()
	{
		return Yii::$app->user->getIdentity();
	}

	public static function getIsGuest()
	{
		return Yii::$app->user->isGuest;
	}

	public static function getDB()
	{
		return \Yii::$app->db;
	}

	public static function createCommand($sql = null)
	{
		$db = \Yii::$app->db;
		if($sql !== null)
		{
			return $db->createCommand($sql);
		}
		return $db->createCommand();
	}

	public static function execute($sql, $inputParams = null, &$outParams = null)
	{
		$db = \Yii::$app->db;
		$command = $db->createCommand($sql);


		if (!empty($inputParams)) {
			foreach ($inputParams as $param) {
				$command->bindParam($param->name, $param->value, $param->type);
			}
		}

		$result = $command->execute();

		if (!empty($outParams)) {
			foreach ($outParams as $param) {
				$title = $param->name;
				$param->value = $db->createCommand("select @".$title." as result;")->queryScalar();
			}
		}

		return $result;
	}

	public static function queryAll($sql, $params = null)
	{
		$db = \Yii::$app->db;
		$command = $db->createCommand($sql);

		if (!empty($params)) {
			foreach ($params as $param) {
				$command->bindParam($param->name, $param->value, $param->type);
			}
		}

		$result = $command->queryAll();
		return $result;
	}

	public static function queryOne($sql)
	{
		$db = \Yii::$app->db;
		$command = $db->createCommand($sql);
		return $command->queryOne();
	}

    public static function queryScalar($sql, $params = null)
    {
        $db = \Yii::$app->db;
        $command = $db->createCommand($sql);

		if (!empty($params)) {
			foreach ($params as $param) {
				$command->bindParam($param->name, $param->value, $param->type);
			}
		}
		
        return $command->queryScalar();
    }


}



