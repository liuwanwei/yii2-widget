<?php

namespace buddysoft\widget\log\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class LogSearch extends Log{

	public function rules(){
		return [
			[['message'], 'string'],
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
	    // bypass scenarios() implementation in the parent class
	    return Model::scenarios();
	}

	public function search($params){
		$query = Log::find();

		// add conditions that should always apply here
		$dataProvider = new ActiveDataProvider([
		    'query' => $query,
		    'sort' => [
		        'defaultOrder' => [
		            'log_time' => SORT_DESC,
		        ]
		    ]
		]);

		$this->load($params);

		if (!$this->validate()) {
		    $query->where('0=1');
		    return $dataProvider;
		}

		$query->andFilterWhere(['like', 'message', $this->message]);

		return $dataProvider;
	}
}