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

	/**
	 * 当控制台下运行时，输出日志到控制台，根据参数选择是否输入出到日志文件
	 *
	 * @param mixed $msg
	 * @param boolean $toLogFile
	 * @return void
	 */
	public static function logIt($msg, $toLogFile = false){
		if (Yii::$app && (Yii::$app instanceof \yii\console\Application)) {
			print_r($msg);
			print_r("\n");
		}

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

	/**
	 * 输出日志到控制台或日志文件，二选一
	 *
	 * @param \yii\base\Model $model
	 * @param mixed $desc string or array，描述信息
	 * @return void
	 */
	public static function logErrors($model, $desc){
		$errors = $model->getErrors();
		if (Yii::$app && (Yii::$app instanceof \yii\console\Application)) {
			if (! empty($desc)) {
				print_r($desc);
				print_r("\n");
			}
			print_r($errors);
			print_r("\n");

		}else{
			if (!empty($desc)) {
				\Yii::error($desc);
			}
	
			\Yii::error($errors);
		}
	}
}

?>
