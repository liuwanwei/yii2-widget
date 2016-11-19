<?php

namespace buddysoft\widget\components;

use Yii;

class DbNoContextTarget extends \yii\log\DbTarget{
	protected function getContextMessage(){
		return '';
	}
}

?>
