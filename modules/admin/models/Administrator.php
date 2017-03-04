<?php

namespace app\modules\admin\models;

use app\helpers\UtilHelper;
use app\base\Model;
use app\modules\admin\authorization\RoleType;
use Yii;
use yii\db\ActiveQueryInterface;

/**
 * This is the model class for table "administrator".
 *
 * @property integer $uid
 * @property string $username
 * @property string $password
 * @property string $truename
 * @property integer $gid
 * @property string $bound_ip
 * @property string $remark
 * @property string $auth_key
 * @property integer $last_login_time
 * @property string $last_login_ip
 * @property integer $freeze
 * @property integer $is_core
 * @property integer $created_at
 * @property integer $created_by
 * @property string $created_ip
 * @property integer $modified_at
 * @property integer $modified_by
 */
class Administrator extends Model
{
    const CACHE_ADMINISTRATOR = 'administrator_%s';

    const LOGIN = 'login';
    const CHANGE_PASSWORD = 'change_password';

    public $password_repeat;
    public $password_original;

    /**
     * Rule规则定义了验证器应用在不同场景的有效性。
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password', 'truename', 'bound_ip', 'remark', 'password_repeat', 'password_original'], 'trim'],
            [['username', 'password_original', 'truename', 'freeze'], 'required'],
            [['password', 'password_repeat'], 'required', 'on'=>[self::CREATE, self::CHANGE_PASSWORD]],
            [['username'], 'string', 'max' => 30],
            [['password'], 'string', 'max' => 100],
            [['truename'], 'string', 'max' => 50],
            [['bound_ip'], 'string', 'max' => 1000],
            [['remark'], 'string', 'max' => 255],
            [['freeze'], 'default', 'value' => 0],
            [['freeze'], 'in', 'range' => [0, 1]],
            ['password', 'compare', 'message' => '兩次密码輸入不相同'],
            [['created_at', 'created_ip', 'modified_at', 'modified_ip', 'last_login_time', 'last_login_ip'], 'safe'],
            [['username'], 'unique'],
            [['password_original'], 'validateOriginalPassword'],
            [['gid'], 'exist', 'targetClass' => AdminGroup::className(), 'targetAttribute' => ['gid' => 'gid']],
        ];
    }

    /**
     * 场景定义.（主要定义了在特定场景对那些属性应用验证器, 但是并不是应用所有验证器,与在rule中定义的验证器有效性有关系）
     *
     * @return array
     */
    public function scenarios()
    {
        $scenarios = [];
        $scenarios[self::CREATE] = ['username', 'password', 'password_repeat', 'truename', 'bound_ip', 'remark', 'freeze', 'created_at',
            'created_ip', 'auth_key', 'gid'];
        $scenarios[self::EDIT] = ['username', 'password', 'password_repeat', 'truename', 'bound_ip', 'remark', 'freeze', 'modified_at', 'modified_ip', 'gid'];
        $scenarios[self::CHANGE_PASSWORD] = ['password_original', 'password', 'password_repeat'];
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
            $administrator = self::findOne($this->uid);
            if (!$administrator->validatePassword($this->$attribute)) {
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
            'username' => '登录帐号',
            'password' => '登录密码',
            'truename' => '真实姓名',
            'bound_ip' => 'IP范围',
            'remark' => '备注',
            'auth_key' => '认证密钥',
            'gid' => '所属管理组',
            'role_type' => '角色类型',
            'last_login_time' => '最近登录时间',
            'last_login_ip' => '最近登录ip',
            'freeze' => '帐号状态',
            'is_core' => '是否系统核心用户(该类用户禁止删除)',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'created_ip' => '创建来源IP',
            'modified_at' => '修改时间',
            'modified_by' => '修改人',

            'password_repeat' => '重复密码',
            'password_original' => '原始密码'
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
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $curTime = time();
                $curIp = UtilHelper::getUserHostAddress();
                $this->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);
                $this->auth_key = \Yii::$app->security->generateRandomString();
                $this->created_at = $curTime;
                $this->created_ip = $curIp;
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
     * @param string $username
     * @return array
     */
    public static function findByUsername($username, $condition = null)
    {
        $query = self::find()->where('username=:username')->params([':username' => $username]);
        if(!is_null($condition)){
            $query->andWhere($condition);
        }
        return $query->one();
    }

    /**
     * 更新登录信息
     */
    public function updateLoginStatus()
    {
        $this->setScenario(self::LOGIN);
        $this->last_login_time = time();
        $this->last_login_ip = UtilHelper::getUserHostAddress();
        $this->save();
    }

    /**
     * 获取管理员名称.
     *
     * @param int $uid
     * @return bool
     */
    public static function getName($uid)
    {
        static $nameList;
        if($uid == 0){
            return '系统管理员';
        }
        if (is_null($nameList)) {
            $nameList = self::createPairs('uid', 'username');
        }
        if (isset($nameList[$uid])) {
            return $nameList[$uid];
        }
        return false;
    }
    /**
     * 获取分类列表.
     *
     * @param ActiveQueryInterface $query 　获取条件
     * @param string $order 排列顺序
     * @param int $offset 偏移量
     * @param int $limit 获取条数
     * @return array array(查询数量,查询数据)
     */
    public static function getList(ActiveQueryInterface $query, $order, $limit = null, $offset = 0)
    {

        list($count, $rows) = parent::getList($query, $order, $limit, $offset);
        $groups = AdminGroup::getGroups();
        foreach ($rows as &$row) {
            if (isset($groups[$row['gid']])) {
                $group = $groups[$row['gid']];
                $row['group_name'] = $group['group_name'];
                $row['group_freeze'] = $group['freeze'];
                $row['role_type'] = RoleType::getRoleTypeTitle($group['role_type']);
            }
        }
        return [$count, $rows];
    }
}
