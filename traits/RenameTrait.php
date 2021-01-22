<?php
/**
 * 用于为了修改字段或表明而创建 migration 时，自动生成 up 和 down 类型的修改代码
 * TODO: 项目到一定阶段，不需要更新数据库时，将这段代码合并到 yii2-widget 的 Migration 中
 */
namespace buddysoft\widget\traits;

trait RenameTrait{
    /**
    [
        // 修改表名字的配置项
        [
            'type' => 'table',
            'tables' => [
                'table1' => 'newTable1',
                'table2' => 'newTable2',
            ]
        ],
        // 修改表中字段名字的配置项
        [
            'type' => 'column',
            'table' => 'tableName',
            'columns' => [
                'name' => 'username',
                'tel' => 'mobile',
            ]
        ]
    ];

    配置参数：
    
    type, string: 修改表名时，使用 'table'，修改表中字段时，使用 'column'    
    tables, array: 修改表名时有效，数组的 key 代表原表名，value 代表更改后的表名
    table, string: 修改字段名时有效，设置被修改的字段所属表名
    columns, array: 修改字段名时有效，数组的 key 代表原字段名，value 代表更改后的字段名
    
    */

    public function renameUp($configs){
        $this->_rename($configs, true);
    }

    public function renameDown($configs){
        $this->_rename($configs, false);
    }

    /**
     * 执行改名操作
     *
     * @param array $configs
     * @param boolean $isUp true 代表执行 migrate/up，否则代表执行 migrate/down
     * @return void
     */
    public function _rename(array $configs, bool $isUp){
        foreach ($configs as $config) {
            if ($config['type'] == 'table') {
                foreach($config['tables'] as $table => $newTable){
                    $this->_renameTableWrapper($table, $newTable, $isUp);
                }

            }else if ($config['type'] == 'column'){
                $table = $config['table'];
                // 处理同一表的字段时，schema 可以复用，所以当作参数传进去
                $schema = \Yii::$app->db->getTableSchema($table);
                foreach ($config['columns'] as $column => $toColumn){
                    $this->_renameColumnWrapper($table, $schema, $column, $toColumn, $isUp);
                }
            }
        }
    }

    private function _renameTableWrapper(string $table, string $toTable, bool $isUp){
        $from = $isUp ? $table : $toTable;
        $to = $isUp ? $toTable : $table;

        $schema = \Yii::$app->db->getTableSchema($from);
        if ($schema != null){
            // 表存在时再改名
            $this->renameTable($from, $to);
        }
    }

    /**
     * 重命名一个字段
     *
     * @param string $table
     * @param \yii\db\TableSchema $schema 为了重复使用，所以当作参数传入
     * @param string $column
     * @param string $toColumn
     * @param boolean $isUp
     * @return void
     */
    private function _renameColumnWrapper(string $table, $schema, string $column, string $toColumn, bool $isUp){
        $from = $isUp ? $column : $toColumn;
        $to = $isUp ? $toColumn : $column;

        if (isset($schema->columns[$from])){
            $this->renameColumn($table, $from, $to);
        }
    }
}

?>
