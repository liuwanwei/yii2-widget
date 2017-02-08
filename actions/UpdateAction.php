<?php

namespace buddysoft\widget\actions;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;


class UpdateAction extends \yii\rest\UpdateAction{

    /**
     * Deletes a model.
     * @param mixed $id id of the model to be deleted.
     * @throws ServerErrorHttpException on failure.
     */
    public function run($id)
    {
        $model = parent::run($id);

        if ($model->hasErrors()) {
            $errors = array_values($model->getErrors());
            return [
                'status' => -10,
                'msg' => $errors[0][0],
            ];
        }

        // 重新查询，保证用户传来的 string 格式的整型值被正确转换成 integer
        $model = call_user_func([$this->modelClass, 'findOne'], $model->id);

        $data = [
            'status' => 0,
            'msg' => '成功',
        ];

        if (! empty($model)) {
            $data['object'] = $model;
        }

        return $data;
    }
}