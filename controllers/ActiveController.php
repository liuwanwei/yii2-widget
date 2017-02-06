<?php

namespace buddysoft\widget\controllers;

use Yii;
use yii\filters\auth\HttpBasicAuth;

class ActiveController extends \yii\rest\ActiveController{

    const QUERY_ENVELOPE = 'items';

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
        'collectionEnvelope' => self::QUERY_ENVELOPE,
    ];

    // 配置 actions 的特殊处理过程
    public function actions(){
    	$actions = parent::actions();
    	$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
    	
    	$customActions = [
            // 对于具备 deleted 字段的表，执行假删除
    		'delete' => [
    			'class' => 'buddysoft\widget\actions\DeleteAction',
    			'modelClass' => $this->modelClass,                
                'checkAccess' => [$this, 'checkAccess'],                
    		],
            // 对于创建和更新，重新查询并返回数据，保证用户传来的 string 格式的整型值被正确转换成 integer
            'create' => [
                'class' => 'buddysoft\widget\actions\CreateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => 'buddysoft\widget\actions\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
            'view' => [
                'class' => 'buddysoft\widget\actions\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
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

    /**
     *
     * 结束 RESTFul actions（一般是 index） 执行
     * 一般用在发生参数错误时，调用本函数结束执行，返回标准协议：
     * { 'status' : -1, 'msg' : $msg}
     *
     */
    
    protected function badRequest($msg){
        throw new \yii\web\BadRequestHttpException($msg, 0);
    }

    protected function forbiddenRequest($msg = null){
        if (empty($msg)) {
            $msg = '禁止访问不属于自己的数据';
        }
        throw new \yii\web\ForbiddenHttpException($msg);
    }

    /**
     *
     * 如果 RESTFul 请求处理过程中发生异常，将异常消息转化成协议中规定的格式
     * 使用时，在 main.php 的 components 中配置：
     * 
     * 'response' => [
     *    'class' => 'yii\web\Response',
     *    'charset' => 'UTF-8',
     *    'on beforeSend' => function($event){
     *        ActiveController::onBeforeSend($event);
     *    },
     * ],
     *
     */
    
    public static function onBeforeSend($event){
        $response = $event->sender;
        if ($response->data != null) {
            // 注意：此处传递的是引用
            $data = &$response->data;
            if (isset($data['status'])) {

                // 将 Yii2 框架生成的异常信息，转换成符合自身协议格式的信息
                if($data['status'] == 400){
                    $code = self::CODE_INVALID_PARAM;
                    $msg = $data['message'];
                }else if ($data['status'] == 401) {
                    $code = self::CODE_UNAUTHORIZED;
                    $msg = '请求包认证信息错误';
                }else if($data['status'] == 404){
                    $code = self::CODE_NOT_EXIST;
                    $msg = '请求的对象不存在';
                }else if($data['status'] == 403){
                    $code = self::CODE_UNAUTHORIZED;
                    $msg = $data['message'];
                }

                if (isset($code)) {
                    // 格式化异常错误反馈
                    $data = [
                        'status' => $code,
                        'msg' => $msg,
                    ];
                }
            }else{

                // 格式化查询接口，统一反馈协议格式，便于客户端处理
                if (isset($data[static::QUERY_ENVELOPE])) {
                    // 给查询结果增加一层封装
                    $data = [
                        'status' => 0,
                        'msg' => '查询成功',
                        static::QUERY_ENVELOPE => $data[static::QUERY_ENVELOPE],
                    ];
                }
            }
        }
    }
}
