<?php

namespace buddysoft\widget\assets;

use yii\web\AssetBundle;

class SummernoteAsset extends AssetBundle{
	public $sourcePath = '@vendor/buddysoft/yii2-widget/assets/summernote';

	// 一旦定义了 $sourcePath，他们就会被覆盖
	// public $basePath = '@webroot';
	// public $baseUrl = '@web';

	public $css = [
		// 相对于 $sourcePath 的资源，必须写相对路径，不能带最前面的反斜杠
		'summernote.css',
		'//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css',
	];

	public $js = [
		'summernote.min.js',
		'lang/summernote-zh-CN.js',
	];

	public $depends = [
		'yii\web\YiiAsset',
		'yii\bootstrap\BootstrapAsset',
		'yii\web\JqueryAsset',
	];

	public $publishOptions = [
		// 'forceCopy' => true,
	];
}