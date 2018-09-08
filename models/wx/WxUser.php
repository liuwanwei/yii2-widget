<?php

namespace buddysoft\widget\models\wx;

use Yii;

/**
 * This is the model class for table "wx_user".
 *
 * @property int $id
 * @property int $userId
 * @property string $openId
 * @property string $unionId
 * @property string $sessionKey
 * @property string $nickName
 * @property string $avatarUrl
 * @property int $gender
 * @property string $city
 * @property string $province
 * @property string $country
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property User $user
 */
class WxUser extends \buddysoft\widget\models\BDAR
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wx_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId'], 'required'],
            [['userId', 'gender'], 'integer'],
            [['createdAt', 'updatedAt'], 'safe'],
            [['openId', 'unionId'], 'string', 'max' => 128],
            [['sessionKey', 'nickName', 'avatarUrl'], 'string', 'max' => 255],
            [['city', 'province', 'country'], 'string', 'max' => 32],
            [['userId'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'userId' => '用户ID',
            'openId' => 'Open ID',
            'unionId' => 'Union ID',
            'sessionKey' => 'Session Key',
            'nickName' => 'Nick Name',
            'avatarUrl' => 'Avatar Url',
            'gender' => 'Gender',
            'city' => 'City',
            'province' => 'Province',
            'country' => 'Country',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }
}
