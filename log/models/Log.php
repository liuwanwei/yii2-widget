<?php

namespace buddysoft\widget\log\models;

class Log extends \yii\db\ActiveRecord{

	public static function tableName(){
		return 'log';
	}

	public function attributeLabels(){
		return [
			'level' => '级别',
			'category' => '分类',
			'message' => '内容',
			'log_time' => '时间',
		];
	}
	
}