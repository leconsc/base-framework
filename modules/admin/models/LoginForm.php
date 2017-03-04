<?php

namespace app\modules\admin\models;

use app\helpers\UtilHelper;
use app\modules\admin\services\AdminIdentity;
use Yii;
use app\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property Administrator|null $administrator This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;

    private $_administrator = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // username is validated by validateUsername()
            ['username', 'validateUsername'],
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
            'username' => '登录帐号',
            'password' => '登录密码'
        ];
    }

    /**
     * 验证用户帐号.
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateUsername($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $administrator = $this->getAdministrator();

            if (!$administrator) {
                $this->addError($attribute, '不正确的登录帐号或密码');
            }else if(!UtilHelper::checkIpBound($administrator->bound_ip)){
                $this->addError($attribute, 'IP访问受限');
            }else if($administrator->freeze){
                $this->addError($attribute, '帐号已被管理员冻结(如有疑问请联系站点管理员)');
            }else{
                $adminGroup = $this->getAdminGroup($administrator->gid);
                if(!$adminGroup){
                    $this->addError($attribute, '登录帐号管理组未设定');
                }else if($adminGroup->freeze){
                    $this->addError($attribute, '登录帐号所属管理组被冻结(如有疑问请联系站点管理员)');
                }
            }
        }
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional username-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $administrator = $this->getAdministrator();

            if (!$administrator || !$administrator->validatePassword($this->password)) {
                $this->addError($attribute, '不正确的登录帐号或密码');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $administrator = $this->getAdministrator();
            $administrator->updateLoginStatus();

            $identity = AdminIdentity::findIdentity($administrator->uid);
            return Yii::$app->user->login($identity);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return Administrator|bool
     */
    public function getAdministrator()
    {
        if ($this->_administrator === false) {
            $this->_administrator = Administrator::findByUsername($this->username);
        }
        return $this->_administrator;
    }

    /**
     * @param $gid
     * @return null|AdminGroup
     */
    public function getAdminGroup($gid){
        return AdminGroup::findOne($gid);
    }
}
