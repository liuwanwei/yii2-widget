<?php

namespace buddysoft\widget\utils;

class ErrorFormatter{

	/* 
	 * 实现从 model validate 错误中提取第一个错误信息，
	 * 格式为：字段名字 ： 错误信息
	 *
	 * @param Model $model
	 * @param boolean $onlyFirst 只返回第一个错误，目前只支持该选项
	 * 
	 * @return 成功时返回字符串，否则返回 null
	 */
	public static function fromModel($model, $onlyFirst = true){

		$combined = '';

		$errors = $model->getFirstErrors();
		if (!empty($errors)) {
		    foreach ($errors as $key => $value) {
		    	$formattedError = "$key: $value";
		    	if ($onlyFirst) {
					return $formattedError;
		    	}else{
		    		$combined .= "{$formattedError}\n";
		    	}
		    }
		}

		return $combined;
	}

	public static function firstError($model){
		return static::fromModel($model);
	}

	public static function error($model){
		$msg = static::fromModel($model);
		Yii::error($msg);
	}
}