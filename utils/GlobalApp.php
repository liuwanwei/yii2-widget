<?php
/**
 * 跟应用有关的全局信息
 * User: Liu Wanwei
 * Date: 2018/3/10
 * Time: 12:18
 */

namespace buddysoft\widget\utils;

use Yii;

class GlobalApp
{
	/*
	 * 获取当前路由
	 *
	 * @return string
	 */
	public static function route(){
		return Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
	}

	/**
	 * 返回当前已经登录的用户（如果存在的话）
	 *
	 * @return void
	 */
	public static function user(){
		return Yii::$app->user->identity;
	}
	
	/*
	 * 获取当前登录用户 id
	 */
	public static function userId(){
		if (Yii::$app->user->identity){
			return Yii::$app->user->identity->id;
		}else{
			return null;
		}
	}
	
}