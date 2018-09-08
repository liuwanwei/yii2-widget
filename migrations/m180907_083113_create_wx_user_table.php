<?php

/**
 * 如果需要微信登录并存储微信用户信息时，导入 wx_user 表
 * 
 * ./yii migrate --migrationPath=@buddysoft/widget/migrations
 * 
 */

use buddysoft\widget\migrations\Migration;

/**
 * Handles the creation of table `wx_user`.
 */
class m180907_083113_create_wx_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('wx_user', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer()->notNull()->unique(),
            'openId' => $this->string(128),
            'unionId' => $this->string(128),
            'sessionKey' => $this->string(255),
            'nickName' => $this->string(255),
            'avatarUrl' => $this->string(255),
            'gender' => $this->integer(),
            'city' => $this->string(32),
            'province' => $this->string(32),
            'country' => $this->string(32),
            'createdAt' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updatedAt' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP')
        ]);

        $this->createIdx('wx_user', 'userId');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('wx_user');
    }
}
