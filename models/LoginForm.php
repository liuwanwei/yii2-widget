<?php

namespace buddysoft\widget\models;

use Yii;
use yii\base\Model;


class LoginForm extends Model{
  public $username;
  public $password;

  private $_user = false;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      // username and password are both required
      [['username', 'password'], 'required'],
      // password is validated by validatePassword()
      ['password', 'validatePassword'],
    ];
  }

  /**
   * Validates the password.
   * This method serves as the inline validation for password.
   *
   * @param string $attribute the attribute currently being validated
   * @param array $params the additional name-value pairs given in the rule
   */
  public function validatePassword($attribute, $params)
  {
    if (!$this->hasErrors()) {
      $user = $this->getUser();
      if (!$user || !$user->validatePassword($this->password)) {
        $this->addError($attribute, 'Incorrect username or password.');
      }
    }
  }

  /**
   * Logs in a user using the provided username and password.
   *
   * @return boolean whether the user is logged in successfully
   */
  public function login()
  {
    if ($this->validate()) {
      return Yii::$app->getUser()->login($this->getUser(), 0);
    } else {
      return false;
    }
  }

  /**
   * Finds user by [[username]]
   *
   * @return User|null
   */
  public function getUser()
  {
    if ($this->_user === false) {
      // 使用用户设置的 User identity class
      $userClass = Yii::$app->user->identityClass;
      $user = $userClass::findByUsername($this->username);
      $this->_user = $user;
      
      // $this->_user = User::findByUsername($this->username);
    }

    return $this->_user;
  }
}

?>