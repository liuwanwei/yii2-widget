<?php
namespace buddysoft\widget\controllers\block;


class WebController extends \yii\web\Controller{
    
    public $blockNamespace = 'backend\blocks';

    public function getBlock($blockName = ''){
        if (!$blockName) {
            $blockName = $this->action->id;
            if (strchr($blockName, '-')) {
                // 将带连接线的 action-id 转换成驼峰式命名法，如：create-user 转换成 CreateUser
                $sections = explode('-', $blockName);
                $names = [];
                foreach ($sections as $name) {
                    $names[] = ucfirst($name);
                }

                $blockName = implode('', $names);
            }
        }
        
        $viewId = $this->id;
        $viewId = str_replace('/', '\\', $viewId);
        // 因为 namespace 中不能包含 - ，所以所有 block 的名字空间都不能带 -
        $viewId = str_replace('-', '', $viewId);
        $relativeFile = $this->blockNamespace . '\\'.$viewId.'\\'.ucfirst($blockName);

        return new $relativeFile();
    }
}
?>