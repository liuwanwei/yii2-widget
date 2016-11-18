<?php

namespace buddysoft\widget\controllers;

use Yii;

class GitController extends ApiController{

	public function actionOschinaCallback(){
		$params = $_POST;

		$this->exitWithSuccess($_POST['hook']['password']);
	}
}

?>
