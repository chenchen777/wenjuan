<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "user_agent".
 *
 * @property string $id
 * @property string $os
 * @property string $os_ver
 * @property string $engine
 * @property string $engine_ver
 * @property string $browser
 * @property string $ver 浏览器版本号
 * @property string $user_agent
 * @property string $ip
 * @property string $create_at
 * @property string $update_at
 * @property string $version
 * @property int $deleted
 */
class UserAgent extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_agent';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_at', 'update_at'], 'required'],
            [['create_at', 'update_at', 'version', 'deleted'], 'integer'],
            [['os', 'os_ver', 'engine', 'engine_ver', 'ver'], 'string', 'max' => 16],
            [['browser', 'ip'], 'string', 'max' => 32],
            [['user_agent'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'os' => 'Os',
            'os_ver' => 'Os Ver',
            'engine' => 'Engine',
            'engine_ver' => 'Engine Ver',
            'browser' => 'Browser',
            'ver' => 'Ver',
            'user_agent' => 'User Agent',
            'ip' => 'Ip',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'version' => 'Version',
            'deleted' => 'Deleted',
        ];
    }

    /**
     * 保存浏览器UA
     */
    public static function BrowserInfoSave()
    {
        $userAgent = Yii::$app->request->headers['user-agent'];
        $ip = Yii::$app->request->getUserIP();

        if (empty($log = self::findOne(['user_agent' => $userAgent, 'ip' => $ip]))){
            $log = new self();
            $log->user_agent = $userAgent;
            $log->ip = Yii::$app->request->getUserIP();
            $log->create_at = time();
        }

        // 获取客户端浏览器以及版本号
        $browse = MonitorKeywordResult::getBrowse();
        // 获取客户端操作系统以及版本号
        $os = MonitorKeywordResult::getOs();

        $log->browser = $browse['browser'];
        $log->ver = $browse['browser_ver'];
        $log->os = $os['os'];
        $log->os_ver = $os['os_ver'];
        $log->update_at = time();
        $log->save();
    }

    // 获取浏览器
    // 获取用户系统
    public static function getOs(){
        $agent = $_SERVER['HTTP_USER_AGENT'];

        //window系统
        if (stripos($agent, 'window')) {
            $os = 'Windows';
            $equipment = '电脑';
            if (preg_match('/nt 6.0/i', $agent)) {
                $os_ver = 'Vista';
            }elseif(preg_match('/nt 10.0/i', $agent)) {
                $os_ver = '10';
            }elseif(preg_match('/nt 6.3/i', $agent)) {
                $os_ver = '8.1';
            }elseif(preg_match('/nt 6.2/i', $agent)) {
                $os_ver = '8.0';
            }elseif(preg_match('/nt 6.1/i', $agent)) {
                $os_ver = '7';
            }elseif(preg_match('/nt 5.1/i', $agent)) {
                $os_ver = 'XP';
            }elseif(preg_match('/nt 5/i', $agent)) {
                $os_ver = '2000';
            }elseif(preg_match('/nt 98/i', $agent)) {
                $os_ver = '98';
            }elseif(preg_match('/nt/i', $agent)) {
                $os_ver = 'nt';
            }else{
                $os_ver = '';
            }
//            if (preg_match('/x64/i', $agent)) {
//                $os .= '(x64)';
//            }elseif(preg_match('/x32/i', $agent)){
//                $os .= '(x32)';
//            }
        }
        elseif(stripos($agent, 'linux')) {
            if (stripos($agent, 'android')) {
                preg_match('/android\s([\d\.]+)/i', $agent, $match);
                $os = 'Android';
                $equipment = 'Mobile phone';
                $os_ver = $match[1];
            }else{
                $os = 'Linux';
            }
        }
        elseif(stripos($agent, 'unix')) {
            $os = 'Unix';
        }
        elseif(preg_match('/iPhone|iPad|iPod/i',$agent)) {
            preg_match('/OS\s([0-9_\.]+)/i', $agent, $match);
            $os = 'IOS';
            $os_ver = str_replace('_','.',$match[1]);
            if(preg_match('/iPhone/i',$agent)){
                $equipment = 'iPhone';
            }elseif(preg_match('/iPad/i',$agent)){
                $equipment = 'iPad';
            }elseif(preg_match('/iPod/i',$agent)){
                $equipment = 'iPod';
            }
        }
        elseif(stripos($agent, 'mac os')) {
            preg_match('/Mac OS X\s([0-9_\.]+)/i', $agent, $match);
            $os = 'Mac OS X';
            $equipment = '电脑';
            $os_ver = str_replace('_','.',$match[1]);
        }
        else {
            $os = 'Other';
        }
        return ['os'=>$os, 'os_ver'=>$os_ver, 'equipment'=>$equipment];
    }

    // 获取客户端浏览器以及版本号
    public static function getBrowse(){
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $browser = '';
        $browser_ver = '';
        if (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)) {
            $browser = 'OmniWeb';
            $browser_ver = $regs[2];
        }
        if (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Netscape';
            $browser_ver = $regs[2];
        }
        if (preg_match('/safari\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Safari';
            $browser_ver = $regs[1];
        }
        if (preg_match('/Chrome\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Chrome';
            $browser_ver = $regs[1];
        }
        if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)) {
            $browser = 'Internet Explorer';
            $browser_ver = $regs[1];
        }
        if (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)) {
            $browser = 'Opera';
            $browser_ver = $regs[1];
        }
        if (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)) {
            $browser = '(Internet Explorer '.$browser_ver.') NetCaptor';
            $browser_ver = $regs[1];
        }
        if (preg_match('/Maxthon/i', $agent, $regs)) {
            $browser = '(Internet Explorer '.$browser_ver.') Maxthon';
            $browser_ver = '';
        }
        if (preg_match('/SE 2.x/i', $agent, $regs)) {
            $browser = '(Internet Explorer '.$browser_ver.') 搜狗';
            $browser_ver = '';
        }
        if (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'FireFox';
            $browser_ver = $regs[1];
        }
        if (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Lynx';
            $browser_ver = $regs[1];
        }
        if (preg_match('/360SE/i', $agent, $regs)) {
            $browser = '(Internet Explorer '.$browser_ver.') 360SE';
            $browser_ver = '';
        }
        if (preg_match('/QQBrowser\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'QQBrowser';
            $browser_ver = $regs[1];
        }
        if (preg_match('/MicroMessenger\/([^\s]+)/i', $agent, $regs)) {
            $browser = '微信浏览器';
            $browser_ver = $regs[1];
        }
        if ($browser != '') {
            return ['browser'=>$browser, 'browser_ver'=>$browser_ver];
        } else {
            return ['browser'=>'未知','browser_ver'=> ''];
        }
    }

    /**
     * 获取UA信息
     * @param int $limit
     * @return string
     */
    public static function randGetUaList($limit = 1){
        $limit = empty($limit) ? 1 : $limit;
        $Arr = self::find()
            ->select('user_agent')
            ->where(new Expression('rand()'))
            ->limit($limit)
            ->asArray()
            ->all();
        return implode('*', $Arr);
    }
}
