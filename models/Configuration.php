<?php

/**
 * This is the model class for table "{{configuration}}".
 *
 * The followings are the available columns in table '{{configuration}}':
 * @property int $id
 * @property string $config_name
 * @property string $config_value
 * @property int $created_at
 * @property int $created_by
 * @property int $modified_at
 * @property int $modified_by
 */
namespace app\models;

use app\base\Model;
use app\helpers\SystemHelper;
use app\components\SystemConfig;

class Configuration extends Model
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['config_name', 'config_value'], 'required'],
            [['created_at', 'created_by', 'modified_at', 'modified_by'], 'safe'],
            [['config_name'], 'unique']
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
        $scenarios[self::CREATE] = ['config_name', 'config_value', 'created_at', 'created_by'];
        $scenarios[self::EDIT] = ['config_name', 'config_value', 'modified_at', 'modified_by'];
        return $scenarios;
    }
    /**
     * 保存配置数据
     *
     * @param array $data
     * @return bool
     */
    public static function store(array $data)
    {
        $configTypes = SystemConfig::getConfigTypes();

        /** @var Configuration[] $configs */
        $configs = self::find()->all();
        foreach ($configs as $config) {
            if (isset($configTypes[$config->config_name])) {
                if (array_key_exists($config->config_name, $data)) {
                    if(is_array($data[$config->config_name])){
                        $configValue = 0;
                        foreach($data[$config->config_name] as $value){
                            $configValue += intval($value);
                        }
                    }else{
                        $configValue = $data[$config->config_name];
                    }
                    $config->setScenario(self::EDIT);
                    $config->config_value = $configValue;
                    $config->modified_at = time();
                    $config->modified_by = SystemHelper::getOperator();
                    $config->update(['config_value', 'modified_at', 'modified_by']);
                }
                unset($data[$config->config_name]);
            } else {
                $config->delete();
            }
        }
        foreach ($data as $configName => $configValue) {
            if (isset($configTypes[$configName])) {
                if(is_array($configValue)){
                    $cfgValue = 0;
                    foreach($configValue as $value){
                        $cfgValue += intval($value);
                    }
                }else{
                    $cfgValue = $configValue;
                }
                $config = new self();
                $config->setScenario(self::CREATE);
                $config->config_name = $configName;
                $config->config_value = $cfgValue;
                $config->created_at = time();
                $config->created_by = SystemHelper::getOperator();
                $config->insert();
            }
        }
        return true;
    }

    /**
     * 获取定义的用户配置(如果配置未设置，则取默认定义)
     *
     * @return array
     */
    public static function getItems(){
        $config = self::createPairs('config_name', 'config_value');
        $configDefaultValueList = SystemConfig::getConfigDefaultValueList();
        foreach($configDefaultValueList as $configName => $configValue){
            if(!array_key_exists($configName, $config)){
                $config[$configName] = $configValue;
            }
        }
        return $config;
    }
}
