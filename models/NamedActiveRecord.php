<?php
/**
 * 
 * 原文件 namespace 有误，从 controllers 目录下移动到 models 目录下，同时修改 namespace！
 * 原文件 namespace 有误，从 controllers 目录下移动到 models 目录下，同时修改 namespace！
 * 原文件 namespace 有误，从 controllers 目录下移动到 models 目录下，同时修改 namespace！
 * 原 controllers/NamedActiveRecord 废弃。
 * 
 * 可以从这里派生 ActiveRecord，针对某个属性，例如 state，可以定义它的取值范围和含义描述：
 * const STATE_CLOSE = 0;
 * const STATE_OPEN = 1;
 * const STATES = [self::STATE_CLOSE => '关闭', self::STATE_OPEN => '打开'];
 * 
 * 定义后，可以通过 $model->stateNAME 自动返回对应的描述信息，可以通过 $model->stateNAMES 返回所有选项的数组
 */
namespace buddysoft\widget\models;

use yii\helpers\Inflector;


/**
 * 将属性名字分割成纯大写字母、字母间由 _ 连接的字符串
 *
 * @param string $attribute
 * @return string
 * 
 * 举例:
 * type 返回 TYPES, 
 * traderType 返回 TRADER_TYPES
 * status 返回 STATUSES（自动转为复数形式）
 */
function get_const_name(string $attribute) {
    // 将字符串按照大写字母分割
    $components = preg_split('/(?=[A-Z])/', $attribute);
    $count = count($components);
    $lastWord = $components[$count - 1];
    $lastWord = Inflector::pluralize($lastWord);

    if ($count > 1) {
        // 只将最后一个单词替换成复数     
        array_splice($components, -1, 1, $lastWord);
        // 单词间加上连接线
        $constName = implode('_', $components);
    }else{
        $constName = $lastWord;
    }

    return strtoupper($constName);
}


class NamedActiveRecord extends \yii\db\ActiveRecord{

    // 没有特殊意义的后缀
    const SUFFIX_TYPE_NONE = 0;
    // 后缀代表要获取某个属性的名字
    const SUFFIX_TYPE_NAME = 1;
    // 后缀代表要获取某个属性的所有选项
    const SUFFIX_TYPE_OPTIONS = 2;


    /**
     * 根据完整属性名字，获取属性后缀代表的意义
     *
     * @param string $name 完整的属性名字
     * @return array 
     */
    private function _getTypicalSuffix($name){
        if (str_ends_with($name, 'NAME')){
            return [self::SUFFIX_TYPE_NAME, substr($name, 0, -4)];
        }else if (str_ends_with($name, 'NAMES')) {
            return [self::SUFFIX_TYPE_OPTIONS, substr($name, 0, -5)];
        }else{
            return [self::SUFFIX_TYPE_NONE, null];
        }
    }


    /**
     * 获取属性对应的常量，如 type 就去获取 ClassName::TYPES
     *
     * @param string $attribute 属性名字，如 type 或 traderType 等，使用首字母小写，单词首字母大写，无连接线的命名方式
     * @return mixed 一般是字符串，但不排除用户定义的其他类型
     */
    private function _getConstOptions($attribute){

        $constName = get_const_name($attribute);
        $class = get_called_class();

        $const = "{$class}::{$constName}";
        if (defined($const)) {
            return constant($const);
        }

        return null;
    }


    /**
     * 获取属性的所有选项
     *
     * @param string $pureAttribute "纯净" 的属性名字，剔除了 suffix 的
     * @return array|null 获取到选项时，返回 array，否则返回 null
     */
    private function _getAttributeOptions(string $pureAttribute){
        $options = $this->_getConstOptions($pureAttribute);
        if (! empty($options)){
            return $options;
        }

        return null;
    }


    /**
     * 获取某个属性对应的名字
     *
     * @param string $pureAttribute
     * @return string|null 如果有定义时，返回非空字符串；否则返回 null
     */
    public function _getAttributeName(string $pureAttribute){
        if ($this->hasAttribute($pureAttribute)) {
            if ($this->$pureAttribute === null) {
                return '';
            }
        }
        
        $options = $this->_getConstOptions($pureAttribute);
        if (! empty($options)){
            return $options[$this->$pureAttribute];
        }

        return null;
    }

    /**
     * 生成名字中带 "NAME" 或 "NAMES" 字段的属性值
     * 
     * 如果访问 $this->typeNAMES，返回 self::TYPES
     * 如果访问 $this->typeNAME，返回 self::TYPES[$this->type]
     */
    public function __get($name){
        try {
            $value = parent::__get($name);
        } catch (\yii\base\UnknownPropertyException $e){
            
            list($suffix, $attribute) = $this->_getTypicalSuffix($name);

            if ($suffix == self::SUFFIX_TYPE_OPTIONS) {
                // NAMES
                $options = $this->_getAttributeOptions($attribute);
                if ($options != null) {
                    return $options;
                }

                // 未定义时，返回空数组
                return [];

            } else if ($suffix == self::SUFFIX_TYPE_NAME) {
                // NAME
                $name = $this->_getAttributeName($attribute);
                if ($name != null) {
                    return $name;
                }

                // 未定义时，返回属性值本身
                return $this->$attribute;    
            }

            throw $e;
        }

        return $value;
    }

    /**
     * 对于 xxxNAME 类型的属性，获取属性标签名字时，返回 xxx 对应的字符串
     *
     * @param string $attribute
     * @return string
     */
    public function getAttributeLabel($attribute){
        list($suffix, $pureAttribute) = $this->_getTypicalSuffix($attribute);
        if ($suffix != null){
            return parent::getAttributeLabel($pureAttribute);
        }
        
        return parent::getAttributeLabel($attribute);
    }
}

?>