<?php

namespace buddysoft\widget\models;

use Yii;

class UploadFilesForm extends UploadForm{
	/**
     * @var inputFile[]
     */
	public $inputFiles;

	public $scope;

	public function rules(){
		return [
			[['inputFiles'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg', 'maxFiles' => 10],
		];
	}

	/**
	 *
	 * 保存文件到本地，并生成缩略图，缩略图的访问路径是文件后缀之前增加 .thumb. 字符串
	 * 如原文件路径是：/upload/album/1243a32sdad.png
	 * 缩略图的路径是：/upload/album/1243a32sdad.thumb.png
	 */
	
	public function upload(){
		if (! $this->validate()) {
			return false;
		}

		// 生成文件存储路径
		$url = $this->scopePath($this->scope);
		$this->confirmDirectory($this->realPath($url));

		$output = [];
		foreach ($this->inputFiles as $file) {
			// 生成文件名和缩略图文件名
			$id = uniqid();
			$original = $url . $id . '.' . $file->extension;
			$thumbnail = $url . $id . '.thumb.' . $file->extension;

			// 保存原始文件
			$file->saveAs($this->realPath($original));
			// 生成缩略图
			$this->generateThumbnail($original, $thumbnail);

			$output[] = ['original' => $original, 'thumbnail' => $thumbnail];
		}

		return $output;
	}

}