<?php
/**
 * 各种操作定义.
 *
 * @author ChenBin
 * @version $Id:Operation.php, 1.0 2017-01-04 13:42+100 ChenBin$
 * @package: app\modules\admin\authorization
 * @since 2017-01-04 13:42
 * @copyright 2017(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\modules\admin\authorization;

class Operation
{
    const O_CREATE = 'create'; //创建 or 新增
    const O_EDIT = 'edit'; //编辑 or 修改
    const O_REMOVE = 'remove'; //删除
    const O_CLEAN = 'clean';
    const O_VIEW = 'view'; //查看
    const O_AUTHORIZE = 'authorize'; //授权
    const O_FREEZE = 'freeze'; //冻结
    const O_REVIEW = 'review';

    /**
     * @var array 操作描述
     */
    private static $_operationDescription = array(
        self::O_CREATE => '新增',
        self::O_EDIT => '修改',
        self::O_REMOVE => '刪除',
        self::O_VIEW => '查看',
        self::O_CLEAN => '清空',
        self::O_AUTHORIZE => '授权',
        self::O_FREEZE => '冻结',
        self::O_REVIEW => '审核'
    );

    /**
     * 获取所有操作项定义.
     *
     * @return array
     */
    public static function getOperations()
    {
        return self::$_operationDescription;
    }


    /**
     * 获取操作项描述.
     *
     * @param $operation string 操作项名称
     * @return string 如果存在该操作项，返回操作项描述，否则返回原始操作名称
     */
    public static function getOperationDescription($operation)
    {
        if (isset(self::$_operationDescription[$operation])) {
            return self::$_operationDescription[$operation];
        }
        return $operation;
    }
}