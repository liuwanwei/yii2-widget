<?php

namespace buddysoft\widget\components;

class DbNoContextTarget extends \yii\log\DbTarget{
	protected function getContextMessage(){
		return '';
	}
}

?>