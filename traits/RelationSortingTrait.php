<?php
namespace buddysoft\widget\traits;

/**
 * 可以方便的给 relation 字段增加排序功能
 */
trait RelationSortingTrait{

    /**
     * 为某个来自于 relation 的字段增加排序功能
     *
     * @param \yii\data\ActiveDataProvider $dataProvider
     * @param array $columns 字段名字，如 ['material.name', 'dept.name']
     * @return void
     */
    public function addSortColumn(\yii\data\ActiveDataProvider $dataProvider, array $columns){
        foreach ($columns as $columnName) {
            $dataProvider->sort->attributes[$columnName] = [
                'asc' => [$columnName => SORT_ASC],
                'desc' => [$columnName => SORT_DESC]
            ];
        }
    }
}

?>