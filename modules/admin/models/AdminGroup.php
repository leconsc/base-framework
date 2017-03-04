<?php

namespace app\modules\admin\models;

use app\helpers\Cache;
use app\helpers\SystemHelper;
use app\modules\admin\authorization\AccessResource;
use app\modules\admin\authorization\AccessResourceGroup;
use app\modules\admin\authorization\AuthItem;
use app\modules\admin\authorization\Operation;
use app\modules\admin\authorization\RoleType;
use app\base\Model;
use app\modules\admin\services\AdminIdentity;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "admin_group".
 *
 * @property integer $gid
 * @property string $group_name
 * @property integer $role_type
 * @property string $permission
 * @property integer $freeze
 * @property integer $is_core
 * @property string $description
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $modified_at
 * @property integer $modified_by
 * @property Administrator $g
 */
class AdminGroup extends Model
{
    const CACHE_ADMIN_GROUP = 'admin_group_%s';

    const AUTHORIZATION = 'authorization';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_name', 'role_type', 'freeze'], 'required'],
            [['role_type'], 'in', 'range' => array_keys(RoleType::getRoleTypes())],
            [['freeze'], 'default', 'value' => 0],
            [['freeze'], 'in', 'range' => [0, 1]],
            [['group_name'], 'string', 'max' => 30],
            [['permission'], 'string', 'max' => 2000],
            [['description'], 'string', 'max' => 255],
            [['created_at', 'created_by', 'modified_at', 'modified_by'], 'safe'],
            [['group_name'], 'unique']
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
        $scenarios[self::CREATE] = ['group_name', 'role_type', 'freeze', 'description', 'created_at', 'created_by'];
        $scenarios[self::EDIT] = ['group_name', 'role_type', 'freeze', 'description', 'modified_at', 'modified_by'];
        $scenarios[self::AUTHORIZATION] = ['permission', 'modified_at', 'modified_by'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'gid' => '管理组编号',
            'group_name' => '组名',
            'role_type' => '角色类型',
            'permission' => '授于组的访问许可项(JSON,full_permissions表示授于该角色的所有权限)',
            'freeze' => '登录状态',
            'is_core' => '是否系统核心管理组(为1禁止对该组权限进行修改与删除)',
            'description' => '组说明',
            'created_at' => '组创建时间',
            'created_by' => '创建人',
            'modified_at' => '组修改时间',
            'modified_by' => '修改人',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getG()
    {
        return $this->hasOne(Administrator::className(), ['gid' => 'gid']);
    }

    /**
     * 获取授权项列表.
     *
     * @param integer $gid
     * @return array
     */
    public static function getAuthList($gid)
    {
        $status = false;
        $gid = intval($gid);
        if ($gid) {
            $adminGroup = self::findOne($gid);
            if ($adminGroup) {
                $authItems = AuthItem::getAuthItems($adminGroup->role_type);
                $authorized = [];
                if (strcasecmp($adminGroup['permission'], AuthItem::FULL_PERMISSIONS)) {
                    $authorized = Json::decode($adminGroup['permission']);
                }
                $authList = [];
                $resources = AccessResource::getResources();
                foreach ($resources as $group => $resourceList) {
                    $groupTitle = AccessResourceGroup::getGroupDescription($group);
                    if ($groupTitle) {
                        /**　初始化资源列表项 */
                        foreach ($resourceList as $resource => $title) {
                            if (isset($authItems[$resource])) {
                                //初始化组列表项
                                if (!isset($authList[$group])) {
                                    $authList[$group] = [
                                        'title' => $groupTitle,
                                        'items' => []
                                    ];
                                }
                                if (!isset($authList[$group]['items'][$resource])) {
                                    $authList[$group]['items'][$resource] = [
                                        'title' => $title,
                                        'actions' => []
                                    ];
                                }
                                $actions = $authItems[$resource];
                                foreach ($actions as $action => $status) {
                                    $checked = false;
                                    if (isset($authorized[$resource][$action])) {
                                        $checked = true;
                                    }
                                    $authList[$group]['items'][$resource]['actions'][] = [
                                        'action' => $action,
                                        'title' => Operation::getOperationDescription($action),
                                        'checked' => $checked
                                    ];
                                }
                            }
                        }
                    }
                }
                return [$adminGroup, $authList];
            } else {
                $message = '无效的管理用户组编号';
            }
        } else {
            $message = '无效的管理用户组编号';
        }
        return [$status, $message];
    }

    /**
     * 设置管理组权限.
     *
     * @param array $data
     * @return array
     */
    public static function saveAuthorize(array $data)
    {
        $gid = isset($data['gid']) ? intval($data['gid']) : 0;
        $status = false;
        if ($gid) {
            if (isset($data[AuthItem::FULL_PERMISSIONS])) {
                $permission = AuthItem::FULL_PERMISSIONS;
            } elseif (isset($data['perm'])) {
                $permission = Json::encode($data['perm']);
            } else {
                $permission = '';
            }
            $data = array();
            $data['permission'] = $permission;
            $data['modified_at'] = time();
            $data['modified_by'] = SystemHelper::getOperator();
            if (self::updateAll($data, ['gid' => $gid])) {
                $status = true;
                AdminIdentity::removeCache($gid, true);
                $message = '设置管理员组权限成功';
            } else {
                $message = '无效的管理员组编号';
            }
        } else {
            $message = '无效的管理员组编号';
        }
        return [$status, $message];
    }

    /**
     * 获取管理员组信息.
     *
     * @param integer $gid
     * @return mixed|null
     */
    public static function get($gid)
    {
        $group = self::find()->select(['gid', 'group_name', 'role_type', 'permission', 'freeze'])->where(['gid' => $gid])->asArray()->one();
        if ($group) {
            if ($group['permission'] !== AuthItem::FULL_PERMISSIONS) {
                $group['permission'] = Json::decode($group['permission']);
            }
        } else {
            return false;
        }
        return $group;
    }

    /**
     * 获取所有管理组信息.
     *
     * @return array
     */
    public static function getGroups()
    {
        $query = self::find()
            ->select(['gid', 'group_name', 'role_type', 'freeze']);
        $groups = self::createAssoc('gid', $query);

        return $groups;
    }

    /**
     * 获取所有管理组信息,按Key-Value形式组织.
     *
     * @return array
     */
    public static function getGroupPairs()
    {
        $groups = self::createPairs('gid', 'group_name');
        return $groups;
    }

    /**
     * 根据角色类型获取管理组信息.
     *
     * @param integer $roleType
     * @return array
     */
    public static function getGroupsByRole($roleType)
    {
        $query = self::find()
            ->select(['gid', 'group_name', 'freeze'])
            ->where(['role_type' => $roleType]);
        $groups = self::createAssoc('gid', $query);
        return $groups;
    }

    /**
     * 获取管理组角色类型.
     *
     * @param integer $gid
     * @return integer
     */
    public static function getRoleType($gid)
    {
        $roleType = self::find()->select(['role_type'])->where(['gid' => $gid])->scalar();
        return intval($roleType);
    }
}
