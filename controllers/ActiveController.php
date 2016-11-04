<?php

namespace buddysoft\widget\controllers;

use Yii;
use yii\filters\auth\HttpBasicAuth;

class ActiveController extends \yii\rest\ActiveController{

	// 设置认证方式：Http Baisc Auth
	public function behaviors(){
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBasicAuth::className()
		];

		return $behaviors;
	}

	// 将查询返回的数据增加一个信封：items
	public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    // 配置 actions 的特殊处理过程
    public function actions(){
    	$actions = parent::actions();
    	$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
    	
    	$customActions = [
            // 对于具备 deleted 字段的表，执行假删除
    		'delete' => [
    			'class' => 'common\actions\DeleteAction',
    			'modelClass' => $this->modelClass,                
    		],
            // 对于创建和更新，重新查询并返回数据，保证用户传来的 string 格式的整型值被正确转换成 integer
            'create' => [
                'class' => 'common\actions\CreateAction',
                'modelClass' => $this->modelClass,
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => 'common\actions\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
    	];

    	return array_merge($actions, $customActions);
    }

    /**
     *
     * 处理查询参数中关于分页的参数
     *
     */
    
    public function preparePagination($dataProvider){
        $paging = false;
        $pagination = $dataProvider->getPagination();

        $params = Yii::$app->request->queryParams;
        if (isset($params['pageSize']) && is_numeric($params['pageSize'])) {
            $pagination->pageSize = $params['pageSize'];
            $paging = true;
        }

        if (isset($params['page']) && is_numeric($params['page'])) {
            $pagination->page = $params['page'];
            $paging = true;
        }

        if(false == $paging){
            // 没有分页参数时，默认关闭分页
            $dataProvider->setPagination(false);
        }
    }

    /**
     *
     * 如果数据中带 kuserId 字段，对其验证，保证自己只能修改自己创建的数据
     *
     */ 
    public function checkAccess($action, $model = null, $params = []){
        $kuserId = Yii::$app->user->id;
        if ($action == 'update') {          
            if (isset($model->kuserId) && ($model->kuserId != $kuserId)) {
                throw new \yii\web\ForbiddenHttpException('禁止修改其它用户的数据');
            }
        }
    }
}
