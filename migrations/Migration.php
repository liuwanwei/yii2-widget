<?php
/**
	 * Created by PhpStorm.
	 * User: sungeo
	 * Date: 2018/1/6
	 * Time: 22:08
	 */
	
namespace buddysoft\widget\migrations;


use yii\db\ColumnSchemaBuilder;
use yii\db\Exception;

class Migration extends \yii\db\Migration
{
	public $tableName;
	
	private function _getForeignKeyName($table, $column, $refTable){
		return "fk_{$table}_{$refTable}_{$column}";
	}
	/*
	 * 创建外键
	 *
	 * 对原接口进行封装，实现统一格式的外键命名
	 * 
	 * @param string $thisTable 	要创建外键表名字，如 user
	 * @param string $thisColumn	要创建外键的字段名字，如 userId
	 * @param string $refTable		外部表表名字，如 user
	 * @param string $refColumn		外键字段在外部表中的名字，如 id
	 * @param string $delete			当外部表中记录被删除时，如何处理当前表中的字段（userId）
	 * @param string $update			当外部表中记录被更新时，如何处理当前表中的字段（userId）
	 * 
	 * @return 参考 \yii\db\Migrations::addForeignKey() 的返回值
	 */
	public function createForeignKey($thisTable, $thisColumn, $refTable, $refColumn = 'id', $delete = null, $update = null){
		$name = $this->_getForeignKeyName($thisTable, $thisColumn, $refTable);
		$this->addForeignKey($name, $thisTable, $thisColumn, $refTable, $refColumn, $delete, $update);
	}
	
	/*
	 * 删除一个外键，createForeignKey 的逆操作
	 */
	public function removeForeignKey($table, $column, $refTable){
		$name = $this->_getForeignKeyName($table, $column, $refTable);
		$this->dropForeignKey($name, $table);
	}
	
	/*
	 * 根据表和字段名生成统一的索引名字
	 *
	 * @param  string  $table   表名字
	 * @param  mixed   $columns 字段，支持数组
	 */
	private function _idxNameForColumns($columns){
		if (is_array($columns)){
			$name = implode('_', $columns);
		}else{
			$name = $columns;
		}
		
		return "idx_{$name}";
	}

	/*
	 * 创建索引进行封装，实现统一格式的索引名字
	 *
	 * @param  string  $table   表名字
	 * @param  mixed   $columns 字段，支持数组
	 * @unique boolean $unique  索引是否唯一
	 *
	 * @return void
	 */
	public function createIdx($table, $columns, $unique = false)
	{
		$name = $this->_idxNameForColumns($columns);
		$this->createIndex($name, $table, $columns, $unique);
	}
	
	/*
	 * 删除索引，createIdx 的逆操作
	 */
	public function dropIdx($table, $columns){
		$name = $this->_idxNameForColumns($columns);
		$this->dropIndex($name, $table);
	}
	
	/*
	 * 检查使用哪个表名字
	 *
	 * 优先使用用户输入的表名字，如果没有，使用类属性中的表名字，否则抛出异常
	 *
	 * @throw exception Exception 如果未找到表名字
	 */
	private function getUsableTableName($tableName){
		if ($tableName !== null){
			return $tableName;
		}else if ($this->tableName !== null){
			return $this->tableName;
		}else{
			throw new Exception("操作前必须知道数据表的名字");
		}
	}
	
	/*
	 * 检查表中是否存在字段
	 *
	 * @param string $column 字段名
	 * @param string $table  表名字，如果为空，使用 $this->>tableName
	 *
	 * @return boolean
	 */
	public function isColumnExist(string $column, string $tableName = null){
		$table = $this->getUsableTableName($tableName);
		$tableSchema = \Yii::$app->db->getTableSchema($table);
		
		if (isset($tableSchema->columns[$column])) {
			return true;
		}else{
			return false;
		}
	}
	
	/*
	 * 从表中检查删除一个字段
	 */
	public function checkDropColumn(string $column, string $tableName = null){
		$table = $this->getUsableTableName($tableName);
		if ($this->isColumnExist($column, $table)){
			$this->dropColumn($table, $column);
			return true;
		}else{
			return false;
		}
	}
	
	/*
	 * 向表中检查添加不存在的字段
	 *
	 * @param ColumnSchemaBuilder $col       要创建的字段对象
	 * @param string              $column    字段名字
	 * @param string              $tableName 表名字
	 *
	 * @return boolean
	 */
	public function checkCreateColumn(ColumnSchemaBuilder $col, string $column, string $tableName = null){
		$table = $this->getUsableTableName($tableName);
		if ($this->isColumnExist($column, $table)){
			return false;
		}else{
			$this->addColumn($table, $column, $col);
			return true;
		}
	}
	
	/*
	 * 修改字段
	 *
	 * @param ColumnSchemaBuilder $col       要创建的字段对象
	 * @param string              $column    字段名字
	 * @param string              $tableName 表名字
	 *
	 * @return boolean
	 *
	 */
	public function checkAlterColumn(ColumnSchemaBuilder $col, string $column, string $tableName = null){
		$table = $this->getUsableTableName($tableName);
		if (! $this->isColumnExist($column, $table)){
			return false;
		}else{
			$this->alterColumn($table, $column, $col);
			return true;
		}
	}
	
	/*
	 * 字段改名
	 */
	public function checkRenameColumn(string $column, string $newColumn, string $tableName = null){
		$table = $this->getUsableTableName($tableName);
		if (! $this->isColumnExist($column, $table)){
			return false;
		}else{
			$this->renameColumn($table, $column, $newColumn);
			return true;
		}
	}
}