<?php

namespace buddysoft\widget\log\controllers;

use buddysoft\widget\controllers\WebController;
use buddysoft\widget\log\models\LogSearch;

class LogController extends WebController{
	public function actionIndex(){
		$search = new LogSearch();
		$dataProvider = $search->search(null);

		return $this->render('index', ['dataProvider' => $dataProvider]);
	}
}