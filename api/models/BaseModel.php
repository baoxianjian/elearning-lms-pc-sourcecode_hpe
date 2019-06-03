<?php
namespace api\models;
use yii\base\Model;
use Yii;
class BaseModel extends Model {
	
	/**
	 * 通过对象对成员变量赋值
	 * @param unknown $obj
	 */
	public function loadWithObject($obj)
	{
		if (is_object($obj) && !empty(get_object_vars($obj))) {
			$objAttributes=$obj->attributes();
			$attributes = $this->attributes();
			foreach ($attributes as $attr) {
				if(in_array($attr, $objAttributes)){
					if(isset($attr))
					$this->$attr = $obj->$attr;
				}
			}
		}
	}
	
	
	/**
	 * 通过数组对成员变量赋值
	 * @param unknown $arr
	 */
	public function loadWithArray($arr)
	{
		if (is_array($arr) && !empty($arr)) {
			$objAttributes=array_keys($arr);
			$attributes = $this->attributes();
			foreach ($attributes as $attr) {
				if(in_array($attr, $objAttributes)){
					if(isset($attr))
						$this->$attr = $arr[$attr];
				}
			}
		}
	}
}