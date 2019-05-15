<?php

namespace buddysoft\widget\models\wx;

use Yii;
use buddysoft\widget\utils\ErrorFormatter;

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

    
    /*
     * 更新数据库中保存的微信用户信息
     *
     * @param WxUser $newWxUser 从微信登录信息中解密出来的微信用户信息对象（只是个 model 对象）
     *
     * @return boolean
     */
    public function updateContent(WxUser $newWxUser)
    {
    	// 通过 array_filter 剔除空项，例如 userId，否则会将原本的 userId 冲掉
        $data = array_filter($newWxUser->attributes, function ($value) {
            return $value != null;
        });
        $this->load($data, '');
        $ret = $this->save();
        if ($ret === false) {
            Yii::error('WxUser 更新时属性验证失败：' . ErrorFormatter::fromModel($this));
            return false;
        } else if ($ret >= 0) {
        	// 没有更新或更新成功
            return true;
        }
    }
}
