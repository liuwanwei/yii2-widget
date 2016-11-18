<?php

namespace buddysoft\widget\controllers;

use Yii;

class GitController extends ApiController{

	public function actionCallback(){
		$params = $_POST;

		$this->exitWithSuccess('Im UK');
	}
}

?>
