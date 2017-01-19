<?php

/**
 *
 * Yii 自带的 DbTarget 有一个问题：
 * 每次通过类似 Yii::error() 接口增加日志，都会将本次请求的上下文信息添加进日志，
 * 比如服务器的 cookie 信息等；每调用一次，就会写入两条日志。
 * 通过阅读源码发现，只要 getContextMessage() 函数返回长度为 0 的字符串，就不会
 * 添加这条日志，所以重载了 DbTarget。
 * 所以在使用自定义日志时，必须如此配置：
 * 
	 'log' => [
	     'traceLevel' => YII_DEBUG ? 3 : 0,
	     'targets' => [
	         [
	         	'class' => 'buddysoft\widget\log\components\DbNoContextTarget',
	         	'categories' => ['buddysoft'],
	         	'except' => ['application'],
	         	'levels' => ['error']
	         ]
	     ],
	 ],
 *
 */


namespace buddysoft\widget\log\components;

use Yii;

class DbNoContextTarget extends \yii\log\DbTarget{
	protected function getContextMessage(){
		return '';
	}
}

?>
