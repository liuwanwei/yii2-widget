<?php

namespace buddysoft\widget\actions;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\web\ServerErrorHttpException;


class CreateAction extends \yii\rest\CreateAction{

    /**
     * Deletes a model.
     * @param mixed $id id of the model to be deleted.
     * @throws ServerErrorHttpException on failure.
     */
    public function run()
    {
        $model = parent::run();

        if ($model->hasErrors()) {
            $errors = array_values($model->getErrors());
            return [
                'status' => -10,
                'msg' => $errors[0][0],
            ];
        }

        /**
         *
         * 查询新添加的记录，保证字段默认值也能返回给用户，保证用户传来的 string 
         * 格式的整型值被正确转换成整型
         *
         */
        
        $model = call_user_func([$this->modelClass, 'findOne'], $model->id);

        return [
            'status' => 0,
            'msg' => '成功',
            'object' => $model
        ];
    }
}