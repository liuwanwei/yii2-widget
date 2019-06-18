<?php
namespace buddysoft\widget\models\wx;


use common\models\User;
use Yii;
use yii\base\Model;
use buddysoft\widget\utils\GlobalApp;

/**
 * Login form
 */
class WxLoginForm extends Model
{
    // 微信用户登录凭证，客户端获得用户授权后，得到 wxCode，发往服务器，由服务器来获取用户信息
	public $wxCode;	
    
    // 微信小程序加密数据套餐，需要使用登录时获取的 session_key 解密后得到最终数据
    public $encryptedData;
    public $iv;

    // Deprecated: 通过 encryptedData 可以解密出用户信息，包括头像和昵称。（2018-05-21）
    public $nickname;
    public $avatar;

    // 用户对象
    private $_user;    

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wxCode', 'encryptedData', 'iv'], 'required'],
            [['nickname', 'avatar', 'wxCode'], 'string'],
        ];
    }
    
    public function formName()
    {
	    return '';
    }
	
	/*
	 * 检测是否打开"假"登录开关
	 *
	 * @return boolean
	 */
    protected function enableFakeLogin(){
        if (isset(Yii::$app->params['fakeUser.enabled'])){
            if (Yii::$app->params['fakeUser.enabled'] === true) {
                return true;
            }
        }

        return false;
    }

    /*
     * 检测当前登录用户名是否"假"的
     *
     * 根据是否使用毛宇用户的临时 wxCode 来判断
     *
     * @return string 当前是 fake user 时，返回配置好的 sessionKey，否则返回 null
     */
    protected function _isFakeLogin(){
        if (! $this->enableFakeLogin()) {
            // 假登录开关未打开
            return null;
        }else{
            // 判断是否符合特定用户名
            if (($this->wxCode == Yii::$app->params['fakeUser.wxCode'])) {
                // 不跟微信服务器通信，直接使用配置的 session 信息
                return Yii::$app->params['fakeUser.sessionKey'];
            }

            // 新增对多个 fake users 的支持
            $fakeUsers = Yii::$app->params['fakeUsers'] ?? [];
            foreach ($fakeUsers as $user) {
                if ($user['wxCode'] == $this->wxCode) {
                    return $user['sessionKey'];
                }
            }

            return null;
        }
    }
    
	/*
	 * 对加密数据进行解密，得到完整用户信息，包括 openId，unionId，nickname 等
	 *
	 * 参考：https://mp.weixin.qq.com/debug/wxadoc/dev/api/open.html#wxgetuserinfoobject
	 */
    protected function _decryptWxUser(string $sessionKey){
	    $data = WxBizDataCrypt::decryptEncryptedData($this->encryptedData, $this->iv, $sessionKey);
	    if ($data === null){
		    Yii::error("解密用户信息失败");
		    return null;
	    }
	    
	    $wxUser = new WxUser();
	    if (! $wxUser->load((array)$data, '')){
	    	Yii::error('从解密后的用户信息生成 WxUser 对象失败');
			return null;
	    }
	    
	    return $wxUser;
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return WxUser 微信用户信息获取成功时，返回 WxUser 对象，否则返回 null
     */
    public function login()
    {
        $sessionKey = $this->_isFakeLogin();
		if (! $sessionKey) {
			// 向服务器拿 session 信息
            $result = Yii::$app->weapp->getSessionInfo($this->wxCode);
            if (empty($result)) {
                Yii::error("向微信服务器获取 session 信息失败");
                return null;
            }
            $sessionKey = $result['sessionKey'];
		}        

        Yii::debug("得到 sessionKey:{$sessionKey}");
        
        $curWxUser = $this->_decryptWxUser($sessionKey);
        if ($curWxUser == null){
        	return null;
        }
        // 缓存 sessionKey，可以用在该用户其它需要解密数据的场合
        $curWxUser->sessionKey = $sessionKey;

        return $curWxUser;

        // // 查找用户信息对象
        // $user = $this->_getUserFromWxUserObject($curWxUser);
        // if (null == $user) {
        // 	// 不存在时创建
        //     $user = $this->_createUser($curWxUser);
        // }else{
        //     // 存在时更新微信用户信息
        //     $user->updateWxUser($curWxUser);
        // }

        // $user->safeLogin();

        // return $user;
    }	
}
