<?php

namespace buddysoft\widget\actions;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\web\ServerErrorHttpException;


class DeleteAction extends \yii\rest\DeleteAction{

    /**
     * Deletes a model.
     * @param mixed $id id of the model to be deleted.
     * @throws ServerErrorHttpException on failure.
     */
    public function run($id)
    {
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        if (isset($model->deleted)) {
        	// 对于有 deleted 字段的表，执行假删除
        	$model->deleted = 1;
        	$model->save();
        	return ['status' => 0, 'msg' => '删除成功'];
        }

        // 执行常规删除
        if ($model->delete() === false) {
            return ['status' => -1, 'msg' => $model->getErrors()];
        }

        return ['status' => 0, 'msg' => '删除成功'];
    }
}