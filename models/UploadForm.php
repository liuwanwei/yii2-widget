<?php

namespace buddysoft\widget\models;

use Yii;

use yii\imagine\Image;
use Carbon\Carbon;

class UploadForm extends \yii\base\Model{

	/**
	 *
	 * 需要直接从 POST 数据中提取数据，不需要在表单数据外层再包过一层 Form Name。
	 *
	 */
	
	public function formName(){
		return "";
	}

	/**
	 *
	 * 根据分区，生成图片带日期和分区的的相对路径
	 *
	 */
	
	protected function scopePath($scope){
		// $today = Date::now()->format('Y-m-d');
		$today = Carbon::now()->toDateString();
		$url = '/upload/' . $today . '/' . $scope . '/';
		return $url;
	}

	/**
	 *
	 * 根据 url 相对路径，计算出文件的绝对存储位置
	 *
	 */
	
	protected function realPath($webPath){
	    // 从 wsdweb/frontend/web 跳到 wedweb 根目录
	    return Yii::getAlias('@webroot'). '/' . $webPath;
	}

	/**
	 *
	 * 检查目录是否存在，不存在时，创建目录（包括目录中包含的上级子目录）
	 *
	 */
	
	protected function confirmDirectory($directory){
	    if (! file_exists($directory)) {
	        mkdir($directory, 0755, true);
	    }
	}

	protected function generateThumbnail($original, $thumbnail){
		// 保存缩略图
		Image::thumbnail($this->realPath($original), 100, 100)
			->save($this->realPath($thumbnail), ['quality' => 80]);;
	}

}