<?php

use buddysoft\widget\migrations\Migration;

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
      $sid = $this->string(64)->notNull()->after('id')->unique();
      $accessToken = $this->string(64)->after('sid')->comment('访问令牌')->unique();
      
      $this->addColumn('user', 'sid', $sid);      
      $this->addColumn('user', 'accessToken', $accessToken);
      $this->createIdx('user', 'sid', true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
      $this->dropIdx('user', 'sid');
      $this->dropColumn('user', 'accessToken');
    	$this->dropColumn('user', 'sid');	    
    } 
}
