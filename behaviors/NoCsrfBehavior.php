<?php
/**
 * 用法：
 * 在 Controller 类中：
 * 
 * public function behaviors(){
 *   return [
 *     'csrf' => [
 *       'class' => NoCsrfBehavior::class,
 *       'controller' => $this,
 *       // 定义不进行 CSRF 验证的 actions
 *       'actions' => [
 *         'action-id-1',
 *         'action-id-2',
 *       ]
 *     ]
 *   ];
 * }
 */

namespace buddysoft\widget\behaviors;

use yii\base\Behavior;
use yii\web\Controller;


class NoCsrfBehavior extends Behavior
{
    public $actions = [];
    public $controller;
    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }

    public function beforeAction($event)
    {
        $action = $event->action->id;
        if(in_array($action, $this->actions)){
        	$this->controller->enableCsrfValidation = false;
        }
    }    
}