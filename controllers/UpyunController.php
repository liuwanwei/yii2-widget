<?php

namespace buddysoft\widget\controllers;

use Yii;
use buddysoft\widget\utils\UpyunHelper;

class UpyunController extends ApiController{

	/**
	 *
	 * 为又拍云小程序上传 SDK 生成签名
	 *
	 * 细节参考：https://github.com/upyun/upyun-wxapp-sdk
	 *
	 * @param string $data 又拍云服务器调用时传入的参数
	 * 
	 * @return string "signature=xxxx" 类型的字符串
	 */
	
	public function actionXcxSignature($data){
		// TODO: 暂时配置在应用里，抽空挪到 module 初始化参数里
		$password = Yii::$app->params['upyun']['password'];

        $md5Password = md5($password);
        $hash = hash_hmac('sha1', $data, $md5Password, true);
        $signature = base64_encode($hash);

        echo json_encode(['signature'=>$signature]);
	}
}

?>
