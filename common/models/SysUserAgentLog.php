<?php

namespace common\models;

use phpDocumentor\Reflection\Types\Self_;
use Yii;

/**
 * This is the model class for table "sys_user_agent".
 *
 * @property int $id
 * @property string $os
 * @property string $os_ver
 * @property string $engine
 * @property string $engine_ver
 * @property string $browser
 * @property string $ver
 * @property string $user_agent
 * @property string $create_at
 * @property string $update_at
 * @property string $version
 * @property string $deleted
 */
class SysUserAgentLog extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sys_user_agent_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'os' => 'os',
            'os_ver' => 'os_ver',
            'engine' => 'engine',
            'engine_ver' => 'engine_ver',
            'browser' => 'browser',
            'ver' => 'ver',
            'user_agent' => 'user_agent',
            'create_at' => 'create_at',
            'update_at' => 'Update At',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'uid' => 'uid',
        ];
    }

    /**
     * 保存UA信息
     */
    public static function BrowserInfoSave(){
        $userAgent = Yii::$app->request->headers['user-agent'];
        if(!self::findOne(['user_agent' => $userAgent])){
            // 获取客户端浏览器以及版本号
            $browse = MonitorKeywordResult::getBrowse();
            // 获取客户端操作系统以及版本号
            $os = MonitorKeywordResult::getOs();

            $log = new SysUserAgentLog();
            $log->user_agent = $userAgent;
            $log->browser = $browse['browser'];
            $log->ver = $browse['browser_ver'];
            $log->os = $os['os'];
            $log->os_ver = $os['os_ver'];
            $log->save();
        }
    }
}
