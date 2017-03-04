<?php

/**
 * 系统后台配置数据定义
 *
 * @author ChenBin
 * @version $Id:SystemConfigList.php, 1.0 2016-07-28 10:50+100 ChenBin$
 * @package: app\components
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */
namespace app\components;

use app\base\Config;
class SystemConfig extends Config
{
    const GROUP_BASE = 'base';

    /**
     * 配置初始化
     */
    protected static function _setUp()
    {
        self::$_configItems = [
            self::GROUP_BASE => [
                self::ITEM_TITLE => '基本配置',
                self::ITEMS => [
                    [
                        self::ITEM_TITLE => '每记录推送配置项限制',
                        self::ITEM_NAME => 'push_config_limit_per_note',
                        self::ITEM_DEFAULT_VALUE => 10,
                        self::ITEM_VALUE_TYPE => self::VALUE_TYPE_INT,
                        self::UI_TYPE => self::UI_TYPE_NUMBER,
                        self::UI_HTML_OPTIONS => [
                            'class'=>'form-text'
                        ]
                    ],
                    [
                        self::ITEM_TITLE => '每配置项推送人数限制',
                        self::ITEM_NAME => 'push_linkman_limit_per_config',
                        self::ITEM_DEFAULT_VALUE => 5,
                        self::ITEM_VALUE_TYPE => self::VALUE_TYPE_INT,
                        self::UI_TYPE => self::UI_TYPE_NUMBER,
                        self::UI_HTML_OPTIONS => [
                            'class'=>'form-text'
                        ]
                    ]
                ]
            ]
        ];
    }
}