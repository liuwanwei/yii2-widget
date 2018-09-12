<?php
namespace buddysoft\widget\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\web\IdentityInterface;
use buddysoft\widget\utils\ErrorFormatter;
use buddysoft\widget\models\wx\WxUser;

/**
 * User model
 *
 * @property int $id
 * @property string $sid
 * @property string $username
 * @property string $accessToken 口令
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property string $password write-only password
 */
class User extends BDAR implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
	        [['sid', 'username', 'auth_key', 'password_hash', 'email', 'created_at', 'updated_at'], 'required'],
	        [['status', 'created_at', 'updated_at'], 'integer'],
	        [['sid', 'accessToken'], 'string', 'max' => 64],
	        [['username', 'auth_key'], 'string', 'max' => 32],
	        [['password_hash', 'password_reset_token', 'email'], 'string', 'max' => 255],
	        [['sid'], 'unique'],
	        [['accessToken'], 'unique'],
        ];
    }
    
    public function fields()
    {
	    $fields = parent::fields();
	    $forbids = ['auth_key', 'password_hash', 'password_reset_token', 'email', 'created_at', 'updated_at'];
	    foreach ($forbids as $item){
	    	unset($fields[$item]);
	    }
	    
	    $fields['nickname'] = [User::class, 'wxUserInfo'];
	    $fields['avatar'] = [User::class, 'wxUserInfo'];
	    $fields['openUid'] = [User::class, 'wxUserInfo'];
	    
	    return $fields;
    }
    
    public static function wxUserInfo($model, $field){
    	// 发往客户端的属性名字，跟 wx_user 中的实际属性名字的映射关系
    	static $map = [
    	    'nickname' => 'nickName',
		    'avatar' => 'avatarUrl',
		    'openUid' => 'openId',
	    ];
    	
        if (isset($map[$field])){
        	$realName = $map[$field];
        	return $model->wxUser->$realName ?? null;
        }else{
        	return null;
        }
    }
	
	public function attributeLabels()
    {
	    return [
		    'id' => Yii::t('be', 'ID'),
		    'sid' => Yii::t('be', 'Sid'),
		    'username' => Yii::t('be', 'Username'),
		    'openUid' => Yii::t('be', 'Open Uid'),
		    'nickname' => Yii::t('be', 'Nickname'),
		    'avatar' => Yii::t('be', 'Avatar'),
		    'accessToken' => Yii::t('be', 'Access Token'),
		    'auth_key' => Yii::t('be', 'Auth Key'),
		    'password_hash' => Yii::t('be', 'Password Hash'),
		    'password_reset_token' => Yii::t('be', 'Password Reset Token'),
		    'email' => Yii::t('be', 'Email'),
		    'status' => Yii::t('be', 'Status'),
		    'created_at' => Yii::t('be', 'Created At'),
		    'updated_at' => Yii::t('be', 'Updated At'),
	    ];
    }
	
	/*
	 * @events
	 */
    public function afterSave($insert, $changedAttributes)
    {
	    parent::afterSave($insert, $changedAttributes);
	    
	    if ($insert){
	    	$this->generateAccessToken();
	    }
	    
    }
	
	/**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
	    return static::findOne(['accessToken' => $token]);
    }
	
	/*
	 * 完善登录流程
	 * 通过 API 登录后，调用本接口，后续可以从 Yii::$app->user->identity 得到 $user 对象
	 */
	public function safeLogin(){
		Yii::$app->user->login($this);
		
		// 登录后，重新生成 accessToken，废弃旧值
		$this->generateAccessToken();
	}

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }
	
    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
	
	public function generateAccessToken(){
    	if (empty($this->accessToken)){
		    $this->accessToken = $this->generateUniqueRandomString('accessToken');
		    $ret = $this->save();
		    if (! $ret){
		    	throw new Exception("保存用户 {ID:$this->id} accessToken 失败: " . ErrorFormatter::fromModel($this));
		    }
	    }
	}
	
	////////////////////////////////////////
	
	public function getWxUser(){
    	return $this->hasOne(WxUser::class, ['userId' => 'id']);
	}
	
	/*
	 * 使用它来存储微信小程序登录后返回的 session_key
	 */
	public function getSessionKey(){
		return $this->wxUser->sessionKey ?? null;
	}
	
	public function getNickname(){
		return $this->wxUser->nickName ?? null;
	}
	
	public function getAvatar(){
		return $this->wxUser->avatarUrl ?? null;
	}

	/**
	 * 从微信登录中获取的用户信息，查找该微信在系统中的 user 表记录
	 *
	 * @param  WxUser $wxUser
	 *
	 * @return User|null
	 */
	public static function getMainUser(WxUser $wxUser)
	{
		$user = null;
        // 查看微信用户对象是否已经创建
		$oldWxUser = WxUser::findOne(['openId' => $wxUser->openId]);
		if ($oldWxUser != null) {
            // 微信用户已经登录过，查找基础用户对象
			$user = User::findOne($oldWxUser->userId);

            // 更新用户微信信息（可能会变）
			$oldWxUser->updateContent($wxUser);
		} else {
            // 微信用户尚未登录，创建 User 记录和 WxUser 记录
			$user = static::createMainUser($wxUser->openId, null);
			if ($user == null) {
				return null;
			}

            // 更新 wx_user.userId 字段，建立关联
			$wxUser->userId = $user->id;
			if (!$wxUser->save()) {
				Yii::error("为用户 {$user->id} 创建 wx_user 记录失败");
				return null;
			}

            // 更新 user.wxUser relation，因为旧值是 null
			$user->populateRelation('wxUser', $wxUser);
		}

		return $user;
	}

    /*
	 * 用户首次授权登录后，创建新用户
	 */
	public static function createMainUser(string $username, string $password)
	{
        if (empty($password)){
            $password = Yii::$app->params['user.defaultPassword'];
        }

		$user = new static();
		$user->username = $username;
		$user->generateAuthKey();
		$user->setPassword($password);
		$user->email = $username . Yii::$app->params['user.emailDomain'];
		$user->created_at = $user->updated_at = time();

		$ret = $user->save();
		if ($ret === false) {
			Yii::error('创建基础用户失败：' . json_encode($user->getErrors()));
			return null;
		}

		return $user;
	}
}
