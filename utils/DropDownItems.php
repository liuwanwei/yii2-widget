<?php 

namespace buddysoft\widget\utils;

use yii\helpers\ArrayHelper;

class DropDownItems extends \yii\base\Object{

	/**
	 *
	 * 从选项中生成 ActiveField::dropDownList 所需的 items
	 *
	 * @param array $itemOptions 所有真实存在的选项
	 * @param string $emptyLabel 空选项的展示内容
	 */
	
	public static function fromOptions($itemOptions, $emptyLabel = '全部'){
		if (empty($emptyLabel)) {
			return [null => '空选项参数错误'];
		}

		if (empty($itemOptions)) {
			return [null => '没有可选项'];
		}

		$items = [null => $emptyLabel];
		return ArrayHelper::merge($items, $itemOptions);
	}

	/**
	 *
	 * 通过 ActiveRecord 记录生成所有选项
	 *
	 * @param string $className ActiveRecord 类名字
	 * @param string $labelFieldName ActiveRecord 中用于获取标签字段的字段的名字
	 * @param string $valueFieldName ActiveRecord 中用于获取选项值的字段的名字
	 * @param string $emptyLabel 空选项的展示内容
	 * 
	 */
	
	public static function fromARs($className, $labelFieldName, $valueFieldName = 'id', $emptyLabel = '全部'){
		
		if (empty($className)) {
			return [null => '对象名字不能为空'];
		}

		$options = ArrayHelper::map($className::find()->all(), $valueFieldName, $labelFieldName);

		return static::fromOptions($options, $emptyLabel);
	}
}



 ?>