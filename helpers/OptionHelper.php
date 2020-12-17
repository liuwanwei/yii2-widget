<?php
namespace buddysoft\widget\helpers;

class OptionHelper{

    /**
     * 在选项的第一个位置添加 “所有” 含义的选项，用于 dropDownList()
     * 
     * @param array & $options 每个选项
     * @param string $i18nSource 自定义的多语言源名字，如 'App'
     * 
     * @return array
     */
    public static function addNullOption(array $options, string $nullOptionName = 'Please Select...'){
        $options = [null => $nullOptionName] + $options; 
        return $options;
    }
}

?>