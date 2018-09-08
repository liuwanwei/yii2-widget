<?php
/**
 * Created by PhpStorm.
 * User: sungeo
 * Date: 2018/4/2
 * Time: 16:37
 */

namespace buddysoft\widget\models\wx;


use yii\base\Model;

class EncryptedDataForm extends Model
{
	public $encryptedData;
	public $iv;
	
	// 解密后的数据，对象
	protected $decryptedData;
	
	// 传来的数据是否经过加密
	public $encrypted = true;
	
	// 返回错误给调用者时用到的字段
	public $error;
	public $status = -1;
	
	public function rules()
	{
		return [
			[['encryptedData', 'iv'], 'required'],
			[['encryptedData', 'iv'], 'string'],
			[['encrypted'], 'integer'],
			[['error'], 'safe'],
		];
	}
	
	/*
	 * 返回解密后的数据对象
	 */
	public function getDecryptedData(){
		return $this->decryptedData;
	}
	
	public function decrypt(){
		if (! $this->encrypted){
			return true;
		}
		
		$data = WxBizDataCrypt::decryptEncryptedData($this->encryptedData, $this->iv);
		if ($data == null){
			$this->addError('error', '解密 encryptedData 失败');
			return false;
		}
		
		$this->decryptedData = $data;
		return true;
	}
}