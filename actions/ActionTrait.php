<?php
/**
 * Created by PhpStorm.
 * User: sungeo
 * Date: 2018/1/16
 * Time: 09:01
 */

namespace buddysoft\widget\actions;


use buddysoft\widget\utils\ErrorFormatter;
use Yii;
use yii\helpers\ArrayHelper;


trait ActionTrait
{
	
	/*
	 * 通过对象的 id（或 sid）属性，查询对象
	 *
	 * @param string $id         对象的 id 或 sid 属性，通过 is_numeric() 区分
	 * @param string $param      查询请求中传递过来的属性名字，只用于回显错误信息
	 * @param string $modelClass 对象的类名字，通过 Model::className() 获取
	 *
	 * @return mix   返回 object 类型数据时表示查询成功，否则返回 array 类型的错误信息，返回 null 表示参数不存在
	 */
	private function _objectWithId($id, $param, $modelClass){
		if ($id === null){
//			return $this->failedWithWrongParam("缺少 {$param} 参数");
			return null;
		}
		
		if (is_numeric($id)){
			$object = $modelClass::findOne($id);
		}else{
			$object = $modelClass::findOne(['sid' => $id]);
		}
		if ($object === null){
			return $this->failedWithWrongParam("{$param} 目标对象不存在");
		}
		
		return $object;
	}
	
	/*
	 * 从 $_GET 中获取 $modelClass 类型对象的 id ，并通过查询返回对象
	 *
	 * @param string $param      存有对象 id 或 sid 属性的字段名字
	 * @param string $modelClass 目标对象类名字，通过 Model::className() 获得
	 *
	 * @return mix   返回 object 类型数据时表示查询成功，否则返回 array 类型的错误信息
	 *
	 */
	public function objectWithGetParam($param, $modelClass){
		$id = Yii::$app->request->get($param);
		
		return $this->_objectWithId($id, $param, $modelClass);
	}
	
	/*
	 * 从 $_POST 中获取 $modelClass 类型对象的 id ，并通过查询返回对象
	 *
	 * @param string $param      参数在 POST 数组中的键值
	 * @param string $modelClass 对象的类原型，可以通过 class 方法取得
	 *
	 * @return mix   返回 object 类型数据时表示查询成功，否则返回 array 类型的错误信息，返回 null 表示参数不存在
	 *
	 * 注意：调用者需使用 objectOk() 对返回值进行判断，
	 */
	public function objectWithPostParam($param, $modelClass){
		$id = Yii::$app->request->post($param);
		
		return $this->_objectWithId($id, $param, $modelClass);
	}
	
	/*
	 * 判断上一步返回的对象是否查询成功
	 *
	 * @param mix $object 查询到的结果，ActiveRecord 对象或 array 数组
	 *
	 * @return true 查询成功，false 失败
	 */
	public function objectOk($result){
		if ($result === null || is_array($result)){
			return false;
		}else{
			return true;
		}
	}
	
	/*
	 * 这对 objectOk() 判断为 false 的情况，返回统一的错误信息
	 *
	 * @param mixed  $result 对应 objectWithPostParam() 和 objectWithGetParam() 的结果
	 * @param string $param  查询对象时提供的键值
	 *
	 * @return array 符合标准协议错误格式的数组
	 */
	public function failedWithResult($result, $param){
		if ($result == null){
			return $this->failedWithWrongParam("缺少 {$param} 参数");
		}else{
			return $result;
		}
	}
	
	/*
	 * 合并错误信息和上下文信息到一个字符串
	 */
	private function _mergeMessage($category, $context){
		$error = $category;
		if ($context){
			$error = $category . ': ' . $context;
		}
		
		return $error;
	}
	
	/*
	 * 更新 GET 请求中的查询参数
	 */
	public function updateQueryParam($params){
		$request = Yii::$app->request;
		
		$oldParams = $request->queryParams;
		$newParams = ArrayHelper::merge($oldParams, $params);
		
		$request->queryParams = $newParams;
	}
	
	/*
	 * 向 Rest 请求的 body 参数中增加经过中间处理的参数
	 *
	 * @param array $params 中间处理过新加入的参数
	 */
	public function updateRequestBody($params){
		$request = Yii::$app->request;
		
		$oldParams = $request->getBodyParams();
		$newParams = ArrayHelper::merge($oldParams, $params);
		
		$request->setBodyParams($newParams);
	}
	
	/*
	 * 检查 action 执行结果是否成功
	 *
	 * @param array $result 执行结果，至少包括 status 和 msg 元素
	 *
	 * @return boolean
	 */
	public function isSuccess($result){
		if (isset($result['status']) && $result['status'] === 0){
			return true;
		}else{
			return false;
		}
	}
	
	/*
	 * 参数错误返回信息封装
	 *
	 * @param string $context 发生错误时的现场描述
	 */
	public function failedWithWrongParam($context = null){
		return [
			'status' => STATUS_INVALID_PARAM,
			'msg' => $this->_mergeMessage('参数错误', $context),
		];
	}
	
	/*
	 * 保存 Model 对象到数据库失败时，返回对应错误信息给客户端
	 *
	 * @param Model  $model   所要保存的对象
	 * @param string $context 保存时上下文环境信息
	 *
	 * @return array response array matches protocol
	 */
	public function failedWhenSaveModel($model, $context = null){
		$error = $this->_mergeMessage('', $context ? $context : '保存失败');
		$error .= ErrorFormatter::fromModel($model);
		return [
			'status' => STATUS_CAN_NOT_SAVE,
			'msg' => $error,
		];
	}
	
	public function failedWhenDeleteModel($model, $context = null){
		return $this->failedWhenSaveModel($model, $context ? $context : '删除失败');
	}
	
	/*
	 * 超过阈值时的反馈消息
	 */
	public function failedWithExceedLimit($context = null){
		return [
			'status' => STATUS_EXCEED_LIMIT,
			'msg' => $this->_mergeMessage('超过限制', $context),
		];
	}
	
	/*
	 * 直接给出错误原因并反馈给客户端
	 *
	 * @param string $context 错误原因
	 */
	public function failedWithReason($reason, $status = STATUS_FAILED_FOR_REASON){
		return [
			'status' => $status,
			'msg' => $reason,
		];
	}
	
	/*
	 * 只返回成功状态信息的反馈消息
	 */
	public function success($context = null){
		return [
			'status' => STATUS_SUCCESS,
			'msg' => $context ? $context : '成功'
		];
	}
	
	/*
	 * 格式化执行成功时返回对象信息反馈消息
	 */
	public function successWithObject($object, $context = null){
		return [
			'status' => STATUS_SUCCESS,
			'msg' => $context ? $context : '成功',
			'object' => $object,
		];
	}
}