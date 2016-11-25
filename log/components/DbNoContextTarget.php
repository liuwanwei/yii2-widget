<?php

namespace buddysoft\widget\log\components;

use Yii;

class DbNoContextTarget extends \yii\log\DbTarget{
	protected function getContextMessage(){
		return '';
	}
}

?>
