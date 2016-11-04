<?php

namespace buddysoft\widget\controllers;

use Yii;

class WebController extends \yii\web\Controller{

	protected function hideLeftMenu(){
		Yii::$app->params['useLeftMenu'] = false;
	}
}
