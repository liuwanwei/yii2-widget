<?php
/**
 * Created by PhpStorm.
 * User: sungeo
 * Date: 2018/4/24
 * Time: 17:03
 */

namespace buddysoft\widget\models\wx;


class WxGroupUidForm extends EncryptedDataForm
{
	public $wxGroupUid;
	
	public function decrypt(){
		if (! parent::decrypt()){
			return false;
		}
		
		// 注意大小写：openG-I-d
		$this->wxGroupUid = $this->decryptedData->openGId;
		return true;
	}
}