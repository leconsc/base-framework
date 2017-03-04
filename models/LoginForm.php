<?php

namespace app\models;

use app\services\MemberIdentity;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $email;
    public $password;
    public $rememberMe = true;

    private $_member = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // email and password are both required
            [['email', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // email is validated by validateEmail()
            ['email', 'validateEmail'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => '电子邮件',
            'password' => '登录密码',
            'rememberMe' => '记住我'
        ];
    }

    /**
     * 验证电子邮件.
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateEmail($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $member = $this->getMember();

            if (!$member) {
                $this->addError($attribute, '不正确的用户名或密码');
            }else if($member->freeze){
                $this->addError($attribute, '帐号已被管理员冻结(如有疑问请联系站点管理员)');
            }
        }
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
            $member = $this->getMember();
            if (!$member || !$member->validatePassword($this->password)) {
                $this->addError($attribute, '不正确的用户名或密码');
            }
        }
    }

    /**
     * Logs in a user using the provided email and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $member = $this->getMember();
            $member->updateLoginStatus();

            $identity = MemberIdentity::findIdentity($member->uid);
            return Yii::$app->user->login($identity, $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[email]]
     *
     * @return Member|bool
     */
    public function getMember()
    {
        if ($this->_member === false) {
            $this->_member = Member::findByEmail($this->email);
        }
        return $this->_member;
    }
}
