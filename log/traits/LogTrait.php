<?php 

/**
 *
 * 所有控制台模块、代码共享的日志打印接口
 *
 */


namespace buddysoft\widget\log\traits;

use Yii;

trait LogTrait {
	protected function log($msg, $toLogFile = false){
		static::logIt($msg, $toLogFile);
	}

	public static function logIt($msg, $toLogFile){
		echo "$msg\n";

		/*
		 * 如果设置了 logCategory 属性，不论是否设置 $toLogFile 参数，都输出到文件
		 */
		if ($toLogFile || isset(Yii::$app->params['logCategory'])) {
			if (isset(Yii::$app->params['logCategory'])){
				Yii::error($msg, Yii::$app->params['logCategory']);
			}else{
				Yii::error($msg);
			}
		}
	}
}

?>
