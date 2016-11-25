
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
            	'format' => ['date', 'php:m-d H:i:s'],
            ],
            'message'
        ],
    ]); ?>
</div>