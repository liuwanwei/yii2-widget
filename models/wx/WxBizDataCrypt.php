<?php
namespace buddysoft\widget\models\wx;

/**
 * 对微信小程序用户加密数据的解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */


use Yii;


class WxBizDataCrypt
{
    private $appid;
		private $sessionKey;

	/**
	 * 构造函数
	 * @param $appid string 小程序的appid
	 * @param $sessionKey string 用户在小程序登录后获取的会话密钥
	 */
	public function __construct($appid, $sessionKey)
	{
		$this->appid = $appid;
		$this->sessionKey = $sessionKey;
	}


	/**
	 * 检验数据的真实性，并且获取解密后的明文.
	 * @param $encryptedData string 加密的用户数据
	 * @param $iv string 与用户数据一同返回的初始向量
	 * @param $data string 解密后的原文
     *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function decryptData( $encryptedData, $iv, &$data )
	{
		if (strlen($this->sessionKey) != 24) {
			return ErrorCode::$IllegalAesKey;
		}
		$aesKey=base64_decode($this->sessionKey);

        
		if (strlen($iv) != 24) {
			return ErrorCode::$IllegalIv;
		}
		$aesIV=base64_decode($iv);

		$aesCipher=base64_decode($encryptedData);

		$result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

		$dataObj=json_decode( $result );
		if( $dataObj  == NULL )
		{
			return ErrorCode::$IllegalBuffer;
		}
		if( $dataObj->watermark->appid != $this->appid )
		{
			return ErrorCode::$IllegalBuffer;
		}
		$data = $result;
		return ErrorCode::$OK;
	}

	/*
	 * 解密敏感数据，比如微信群 ID
	 *
	 * @param string  $encryptedData 加密后的数据
	 * @param string  $iv            解密向量
	 * @param string  $sessionKey    字面意思
	 *
	 * @return mix 解密失败返回 null，失败原因在 Yii::error() 日志中；否则返回解密后的数组
	 */
	public static function decryptEncryptedData($encryptedData, $iv, $sessionKey = null){
		$appId = Yii::$app->params['weapp.appId'];
		
		if ($sessionKey === null){
			$user = Yii::$app->user->identity;
			if ($user === null || $user->sessionKey === null){
				Yii::error("解密数据失败：user.sessionKey 获取失败");
				return null;
			}
			
			$sessionKey = $user->sessionKey;
		}
		
		
		$data = null;
		$pc = new static($appId, $sessionKey);
		$errCode = $pc->decryptData($encryptedData, $iv, $data);
		if ($errCode != 0) {
			Yii::error("解密数据失败：{$errCode}");
			return null;
		}

        return json_decode($data);
	}

	/*
	 * 解密微信群 openGid
	 *
	 * @param string $encryptedData
	 * @param string $iv
	 * @param string $sessionKey
	 *
	 * return json format:
	 *
	 * {
	 *      "openGId":"tGX10H0Vg4iQl1f3S0EK_w0BVs4JQ",
	 *      "watermark":
	 *      {
	 *          "timestamp":1517306043,
	 *          "appid":"wx1e0e045999972923"
	 *      }
	 * }
	 *
	 */
	public static function decryptGroupGid($encryptedData, $iv, $sessionKey = null){
		$data = static::decryptEncryptedData($encryptedData, $iv, $sessionKey);
		if ($data === null){
			return null;
		}else{
			return $data->openGId;
		}
	}
	
	/*
	 * 解密用户 unionId
	 *
	 * return json format:
	 *
	 * {
	 *    "openId": "OPENID",
	 *    "nickName": "NICKNAME",
	 *    "gender": GENDER,
	 *    "city": "CITY",
	 *    "province": "PROVINCE",
	 *    "country": "COUNTRY",
	 *    "avatarUrl": "AVATARURL",
	 *    "unionId": "UNIONID",
	 *    "watermark":
	 *    {
	 *        "appid":"APPID",
	 *        "timestamp":TIMESTAMP
	 *    }
	 * }
	 */
	public static function decryptUnionId($encryptedData, $iv, $sessionKey = null){
		$data = static::decryptEncryptedData($encryptedData, $iv, $sessionKey);
		if ($data === null){
			return null;
		}else{
			return $data->unionId;
		}
	}

}

