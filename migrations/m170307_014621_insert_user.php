<?php

use yii\db\Migration;

class m170307_014621_insert_user extends Migration
{
    public function up()
    {
        // 默认插入一条数据
        // 用户名为：admin 
        // 密码：123456
        $this->insert('user', [
            'username' => 'admin',
            'password_hash' => '$2y$13$bKPC4TpjN41R/MwwZYMrKuul1GXz394yq7KpTa7onO/rHV5VGkE1G',
            'auth_key' => '',
            'email' => '',
            'created_at' => 0,
            'updated_at' => 0,
        ]);
    }

    public function down()
    {
        $this->delete('user', ['username' => 'admin']);
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
