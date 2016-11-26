
<?php

use yii\grid\GridView;

$this->title = '日志记录';
$this->params['breadcrumbs'][] = $this->title;

?>


<div class='log-index'>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            // 'id',
            [
            	'attribute' => 'log_time',
            	'value' => function($model){
                    return Date('m-d H:i:s', round($model->log_time));
                }
            ],
            'message'
        ],
    ]); ?>
</div>