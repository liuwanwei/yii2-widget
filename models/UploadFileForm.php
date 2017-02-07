<?php

namespace buddysoft\widget\models;

use Yii;

class UploadFileForm extends UploadForm{
	public $inputFile;
	public $scope;

	public $original;
	public $thumbnail;

	public function rules(){
		return [
			[['inputFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg'],
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

		// 生成文件名和缩略图文件名
		$id = uniqid();
		$this->original = $url . $id . '.' . $this->inputFile->extension;
		$this->thumbnail = $url . $id . '.thumb.' . $this->inputFile->extension;

		// 保存原始文件
		$this->inputFile->saveAs($this->realPath($this->original));
		// 生成缩略图
		$this->generateThumbnail($this->original, $this->thumbnail);

		return true;
	}

}