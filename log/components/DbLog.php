<?php

namespace buddysoft\widget\log\components;

use Yii;

class DbLog{

	public static function log($msg){
		Yii::error($msg, Yii::$app->params['logCategory']);
	}
}