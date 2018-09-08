<?php

namespace buddysoft\widget\actions;

use yii\web\ServerErrorHttpException;


class DeleteAction extends \yii\rest\DeleteAction{

	use ActionTrait;
	use ModelTrait;
	
	public function run($id){
		$model = $this->getModelById($id);
		if ($model === null){
			return $this->failedWithWrongParam("sid 错误");
		}else{
			return $this->runWithModel($model);
		}
	}
	
    /**
     * Deletes a model.
     * @param mixed $id id of the model to be deleted.
     * @throws ServerErrorHttpException on failure.
     *
     * @return array response array matches protocol
     */
    public function runWithModel($model)
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        if (isset($model->deleted)) {
        	// 对于有 deleted 字段的表，执行假删除
        	$model->deleted = 1;
        	$model->save();
        	
        	return $this->success('删除成功');
        }else{
	        // 常规删除
	        if ($model->delete() === false) {
		        return $this->failedWhenDeleteModel($model);
	        }else{
		        return $this->success('删除成功');
	        }
        }
    }
}