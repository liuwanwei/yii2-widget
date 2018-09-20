<?php

/**
 * Created by PhpStorm.
 * User: sungeo
 * Date: 2018/1/4
 * Time: 15:42
 */

namespace buddysoft\widget\models;


use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use buddysoft\widget\utils\GlobalApp;
use buddysoft\widget\utils\StringObject;

class BDAR extends ActiveRecord
{
	const SID = 'sid';
	
	/*
	 * 获取当前类的排除 namespace 后的名字
	 *
	 * 如 'common\models\Notice'，返回 Notice
	 *
	 * @return string
	 */
	public static function getNeatClassName(){
		$className = static::class;
		$pos = strrpos($className, '\\');
		return substr($className, $pos + 1);
	}
	
	
	/**
	 * 从 Yii 全局配置参数中解析需要排除的 Model 字段
	 *
	 * @return array
	 */
	public static function getExceptFields(){
		$excepts = [];
		
		$route = GlobalApp::route();
		$className = static::getNeatClassName();

		$configs = Yii::$app->params['exceptFields'];
		foreach($configs as $unit){
			if(in_array($route, $unit['routes']) &&
			in_array($className, $unit['models'])){
				$excepts = ArrayHelper::merge($excepts, $unit['fields']);
			}
		}

		return $excepts;
	}
	
	/*
	 * 根据当前请求的路由，确定需要屏蔽到的秘密字段定义
	 * 
	 * 定义秘密字段的方法，请参考 README.md 文件 "BDAR类" 小节
	 *
	 * @return array 需要屏蔽的秘密字段
	 */
	public static function getCommonSecretFields(){
		// 需要排除的秘密字段
		$secrets = Yii::$app->params['secretFields'];
		$excepts = static::getExceptFields();
		
		// 从秘密字段中排除需要临时返回给用户的
		foreach ($excepts as $value){
			$keys = array_keys($secrets, $value);
			if (!empty($keys)){
				unset($secrets[$keys[0]]);
			}			
		}
		
		return $secrets;
	}
	
	/*
	 * 子类重载来定义自己的秘密字段
	 */
	protected function secretFields(){
		return [];
	}
	
	/*
	 * 在返回数据时，根据当前 controller/action 和类名字筛选字段
	 *
	 * @param array & $fields 被筛选的所有字段数组
	 *
	 * @return void
	 */
	private function _discardSecretFields(&$fields){
		$secrets = ArrayHelper::merge(static::getCommonSecretFields(), $this->secretFields());
		
		// 排除秘密字段
		foreach ($secrets as $item){
			$keys = array_keys($fields, $item);
			if (!empty($keys)){
				unset($fields[$keys[0]]);
			}
		}
	}
	
	/*
	 * 自定义需要返回给查询接口的数据字段
	 */
	public function fields()
	{
		$fields = parent::fields();
		
		/*
		 * 禁止返回机密信息给客户端
		 */
		$this->_discardSecretFields($fields);
		
		/*
		 * 所有名字类似 "xxxId" 的字段，都认为是外联字段，自动替换为对应对象的 sid 属性，
		 *
		 * 例如对于 creatorId 字段：
		 * 1. $this 对象必须具备对应的 getCreator() 接口；
		 * 2. $this->>creator 对象必须拥有 sid 属性；
		 * 3. $this->>creatorId 属性会被替换成 $this->>creatorSid 属性并返回给客户端；
		 */
		foreach ($fields as $field) {
			$name = StringObject::from($field);
			if ($name->endWith('Id')){
				unset($fields[$field]);
				$relation = $name->substr(0, $name->len() - 2);
				$relationSid = $relation . 'Sid';
				$fields[$relationSid] = function($model) use ($relation){
					$relationModel = $model->$relation;
					if ($relationModel === null){
						return null;
					}else{
						return $relationModel->sid;
					}
				};
			}
		}
		
		return $fields;
	}
	
	/*
	 * 调用对象 save() 接口保存数据之前自动生成 sid 属性
	 *
	 * save() 接口执行时，内部调用顺序是 beforeValidate - beforeSave - afterSave，
	 * 所以要在 beforeValidate 之前，为新创建的对象生成 sid 属性，否则会验证失败。
	 */
	public function beforeValidate()
	{
		// 获取当前执行的对象类名字
		$class = StringObject::from(static::class);
		
		// 根据类名字排除在 Search 类中执行的情况，生成 ActiveModel 对象的 sid 属性
		if (! $class->endWith('Search') && $this->isNewRecord){
			$this->generateSid();
		}
		
		return parent::beforeValidate();
	}
	
	/*
	 * 从对象通过 asArray 生成的数组中，屏蔽掉秘密字段
	 *
	 * @param array 从对象转换来的数组
	 *
	 * @return void
	 */
	public static function unsetSecretFields(&$array){
		$secrets = static::getCommonSecretFields();
		
		foreach ($secrets as $item){
			unset($array[$item]);
		}
		
		foreach ($array as $key => $value) {
			$name = StringObject::from($key);
			if ($name->endWith('Id')) {
				unset($array[$key]);
			}
		}
	}
	
	/*
	 * 生成 sid 属性，如果有这个字段的话
	 *
	 * @return boolean 成功返回 true，否则 false
	 */
	public function generateSid()
	{
		$table = static::tableName();
		$tableSchema = Yii::$app->db->getTableSchema($table);
		if (isset($tableSchema->columns[self::SID])){
			$this->sid = $this->generateUniqueRandomString(self::SID);
			return true;
		}else{
			Yii::debug("can not find `sid` column in table: {$table}");
			return false;
		}
	}
	
	/**
	 * 生成唯一属性值
	 *
	 * 用法：
	 * Book 基类设置为 BDAR
	 * $book = Book:findOne($bookID);
	 * $book->characterKeyString = $book->generateUniqueRandomString("uniqueId");
	 * $book->save();
	 *
	 * @param string 	$attribute 	需要生成唯一 ID 的字段名字
	 * @param int 		$length			希望的 ID 长度（暂未用到）
	 * @return string 生成的唯一 ID
	 * @throws \yii\base\Exception
	 */
	public function generateUniqueRandomString($attribute, $length = 22) {
		// 使用 uniqid() 的 more_entropy 参数，生成类似 4b340550242239.64159797 的 ID
		$uniqueId = strtoupper(uniqid("", true));
		// 移除 ID 中的 '.' 符号
		$randomString = str_replace('.', '', $uniqueId);
		
		// 检查 ID 在表中的唯一性，为了加速查找，强烈建议为 $attribute 属性增加索引
		if(!$this->findOne([$attribute => $randomString]))
			return $randomString;
		else
			// 如果非唯一，通过递归方式再次生成
			return $this->generateUniqueRandomString($attribute, $length);
	}
}