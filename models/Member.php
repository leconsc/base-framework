<?php

namespace app\models;

use app\helpers\UtilHelper;
use Yii;
use app\helpers\RequestHelper;
use app\validators\MobileValidator;
use yii\captcha\Captcha;
use app\base\Model;

/**
 * This is the model class for table "member".
 *
 * @property integer $uid
 * @property string $email
 * @property string $name
 * @property string $password
 * @property string $mobile
 * @property string $auth_key
 * @property integer $email_status
 * @property integer $freeze
 * @property integer $registration_time
 * @property string $registration_ip
 * @property integer $last_login_time
 * @property string $last_login_ip
 * @property integer $modified_at
 */
class Member extends Model
{
    const REGISTER = 'register';
    const LOGIN = 'login';
    const CHANGE = 'change';
    const CHANGE_PASSWORD = 'change_password';

    public $password_repeat;
    public $password_original;
    public $agree;
    public $verify_code;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'filter', 'filter'=>'strip_tags'],
            [['email', 'name', 'mobile', 'password', 'password_repeat', 'verify_code'], 'trim'],
            [['email', 'name', 'mobile'], 'required'],
            [['password', 'password_repeat', 'password_original', 'verify_code', 'agree'], 'required', 'on'=>[self::CREATE, self::CHANGE_PASSWORD]],
            ['email', 'email'],
            ['email', 'string', 'max' => 40],
            ['name', 'string', 'max' => 30],
            ['mobile', MobileValidator::className()],
            [['email_status', 'freeze'], 'default', 'value' => 0],
            ['agree', 'in', 'range' => [1], 'message' => '请同意协议并勾选'],
            [['freeze'], 'in', 'range' => [0, 1]],
            ['password', 'compare', 'message' => '兩次密码輸入不相同'],
            ['verify_code', 'captcha', 'skipOnEmpty' => !Captcha::checkRequirements()],
            [['email_status', 'registration_ip', 'registration_time', 'last_login_ip', 'last_login_time', 'auth_key', 'modified_at', 'freeze'], 'safe'],
            ['email', 'unique'],
            [['password_original'], 'validateOriginalPassword'],
        ];
    }

    /**
     * 场景定义.
     *
     * @return array
     */
    public function scenarios()
    {
        $scenarios = [];
        $scenarios[self::REGISTER] = ['email', 'name', 'mobile', 'password', 'password_repeat', 'agree', 'registration_ip',
            'registration_time', 'last_login_ip', 'last_login_time', 'auth_key'];
        $validateAndSave = RequestHelper::get('validateAndSave', null, null, ['type' => 'boolean']);
        if (is_null($validateAndSave) || $validateAndSave) {
            $scenarios[self::REGISTER][] = 'verify_code';
        }
        $scenarios[self::EDIT] = ['email', 'name', 'mobile'];
        $scenarios[self::CHANGE_PASSWORD] = ['password_original', 'password', 'password_repeat'];
        $scenarios[self::CHANGE] = ['email', 'name', 'mobile', 'password', 'password_repeat', 'email_status', 'freeze'];
        $scenarios[self::LOGIN] = ['last_login_ip', 'last_login_time'];
        return $scenarios;
    }
    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional username-value pairs given in the rule
     */
    public function validateOriginalPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $member = self::findOne($this->uid);
            if (!$member->validatePassword($this->$attribute)) {
                $this->addError($attribute, '不正确的原始密码');
            }
        }
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => '用户标识',
            'email' => '电子邮件',
            'password' => '登录密码',
            'password_repeat' => '确认密码',
            'password_original' => '原始密码',
            'name' => '您的姓名',
            'mobile' => '手机号码',
            'verify_code' => '验证码',
            'auth_key' => '认证密钥',
            'email_status' => 'Email验证状态',
            'freeze' => '是否冻结',
            'registration_time' => '注册时间',
            'registration_ip' => '来源IP',
            'last_login_time' => '最近访问',
            'last_login_ip' => '访问IP',
            'modified_at' => '修改时间'
        ];
    }
    /**
     * 获取可安全可保存的属性.
     *
     * @return array
     */
    public function getEnableSavingAttributes()
    {
        $attributeNames = parent::getEnableSavingAttributes();
        if (!$this->isNewRecord) {
            if (empty($this->password)) {
                $attributeNames = array_diff($attributeNames, ['password']);
            }
        }
        $attributeNames = array_diff($attributeNames, ['password_repeat', 'verify_code']);
        return $attributeNames;
    }
    /**
     * 数据保存前.
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $curTime = time();
                $curIp = UtilHelper::getUserHostAddress();
                $this->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);
                $this->auth_key = Yii::$app->security->generateRandomString();
                $this->registration_time = $curTime;
                $this->registration_ip = $curIp;
                $this->last_login_time = $curTime;
                $this->last_login_ip = $curIp;
            } else {
                if (!empty($this->password)) {
                    $this->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);
                }
            }
            return true;
        }
        return false;
    }
    /**
     * 用户登入密码验证.
     *
     * @param string $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }

    /**
     * 根据Email查询用户信息.
     *
     * @param string $email
     * @param array || null $condition
     * @return array
     */
    public static function findByEmail($email, $condition = null){
        $query = self::find()->where('email=:email')->params([':email'=>$email]);
        if(!is_null($condition)){
            $query->andWhere($condition);
        }
        return $query->one();
    }

    /**
     * 更新登录信息
     */
    public function updateLoginStatus(){
        $this->setScenario(self::LOGIN);
        $this->last_login_time = time();
        $this->last_login_ip = UtilHelper::getUserHostAddress();
        $this->save();
    }
}
