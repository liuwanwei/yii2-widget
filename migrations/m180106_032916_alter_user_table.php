<?php

use yii\db\Migration;

/**
 * Class m180106_032916_alter_user_table
 */
class m180106_032916_alter_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
    	$col = $this->string(64)->notNull()->after('id')->unique();
    	$this->addColumn('user', 'sid', $col);
		
		$col = $this->string(64)->after('sid')->comment('访问令牌')->unique();
		$this->addColumn('user', 'accessToken', $col);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    	$this->dropColumn('user', 'sid');
	    $this->dropColumn('user', 'accessToken');
    } 
}
