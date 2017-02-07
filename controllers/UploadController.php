<?php

namespace buddysoft\widget\controllers;

use yii\web\UploadedFile;
use buddysoft\widget\models\UploadFileForm;
use buddysoft\widget\models\UploadFilesForm;

class UploadController extends ApiController{
	public function actionFile(){
		$params = $_POST;

		if (! isset($params['scope'])){
			$this->exitWithInvalidParam();
		}

		$model = new UploadFileForm();
		$model->scope = $params['scope'];
		$model->inputFile = UploadedFile::getInstance($model, 'inputFile');

		if ($model->upload()) {
			$this->exitWithSuccess([[
				'original' => $model->original, 
				'thumbnail' => $model->thumbnail
			]]);
		}

		$this->exitWithCode(parent::CODE_INTERNAL_ERROR, json_encode($model->getErrors()));
	}

	public function actionFiles(){
		$params = $_POST;
		if (! isset($params['scope'])) {
			$this->exitWithInvalidParam();
		}

		if (! isset($_FILES['inputFiles'])) {
			$this->exitWithInvalidParam();
		}

		// 获取上传文件总数
		$count = count($_FILES['inputFiles']['name']);

		// 提取上传文件信息
		$model = new UploadFilesForm();
		$model->scope = $params['scope'];
		for ($i=0; $i < $count; $i++) {
			$model->inputFiles[] = UploadedFile::getInstance($model, "inputFiles[$i]");
		}

		// 保存文件
		$output = $model->upload();
		if (! empty($output)) {
			$this->exitWithSuccess($output);
		}

		$this->exitWithCode(parent::CODE_INTERNAL_ERROR, json_encode($model->getErrors()));
	}
}