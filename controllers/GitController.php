<?php

namespace buddysoft\widget\controllers;

use Yii;

class GitController extends ApiController{

    public $oschinaPassword;

	public function actionOschinaCallback(){
        if(isset($_POST['hook'], $_POST['hook']['password'])){
            $password = $_POST['hook']['password'];
        }else{
            $password = 'password not found';
        }

		$this->exitWithSuccess($password);
	}
}

?>
