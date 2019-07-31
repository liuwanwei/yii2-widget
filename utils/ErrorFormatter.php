<?php

/**
 *
 * Yii2 错误格式化输出工具，主要针对 Model 对象
 *
 * 当调用 Model->save() 失败时，Yii2 中通过 Model->getErrors() 获取错误信息，
 * 这个错误信息是数组格式，还可能嵌套有数组。
 *
 * 如果想要将错误输出到日志中，可以使用 json_encode() 将数组转化成字符串，但中文
 * 信息会在日志中无法识别。
 *
 * 所以提供这个工具，帮助管理错误信息，简化代码。
 *
 */
namespace buddysoft\widget\utils;

class ErrorFormatter{

	/* 
	 * 实现从 model validate 错误中提取第一个错误信息，
	 * 格式为：字段名字 ： 错误信息
	 *
	 * @param object $model      \yii\base\Model 的派生类对象
	 * @param boolean $onlyFirst 是否只返回第一个错误，目前只支持该选项
	 * 
	 * @return 成功时返回字符串，否则返回 null
	 */
	public static function fromModel($model, $onlyFirst = true){

		$combined = '';

		$errors = $model->getFirstErrors();
		if (!empty($errors)) {
		    foreach ($errors as $key => $value) {
		    	$formattedError = $value;
		    	if ($onlyFirst) {
					return $formattedError;
		    	}else{
		    		$combined .= "{$formattedError}\n";
		    	}
		    }
		}

		return $combined;
	}

	/**
	 *
	 * 返回 Model 中第一个校验失败属性的第一个错误
	 *
	 * @param object $model \yii\base\Model 的派生类对象
	 *
	 * @return string 第一条错误信息
	 */	
	public static function firstError($model){
		return static::fromModel($model);
	}

	/**
	 *
	 * 将 Model 的第一个校验失败属性的第一条错误信息输出到日志中
	 *
	 * @param object $model    \yii\base\Model 的派生类对象
	 * @param string $category 错误日志分类信息，详见 Yii::log() $category 参数
	 *
	 * @return void
	 */
	
	public static function error($model, $category = 'application'){
		$msg = static::fromModel($model);
		Yii::error($msg, $category);
	}
}