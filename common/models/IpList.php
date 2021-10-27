<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "ip_list".
 *
 * @property int $id
 * @property string $ip ip
 * @property string $port 端口
 * @property string $account 账号
 * @property string $password 密码
 * @property string $area 地区
 * @property int $is_use pc使用情况 0 未使用 1已使用
 * @property int $is_app_use app使用情况 0 未使用 1已使用
 * @property int $is_app_pass app是否通过
 * @property string $version
 * @property int $deleted
 * @property string $create_at
 * @property string $update_at
 * @property int $past_at 过期时间
 * @property string $inspect_at 检查时间
 */
class IpList extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ip_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_use', 'is_app_use', 'is_app_pass', 'version', 'deleted', 'create_at', 'update_at', 'past_at', 'inspect_at'], 'integer'],
            [['ip', 'port', 'account', 'password'], 'string', 'max' => 64],
            [['area'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'Ip',
            'port' => 'Port',
            'account' => 'Account',
            'password' => 'Password',
            'area' => 'Area',
            'is_use' => 'Is Use',
            'is_app_use' => 'Is App Use',
            'is_app_pass' => 'Is App Pass',
            'version' => 'Version',
            'deleted' => 'Deleted',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'past_at' => 'Past At',
            'inspect_at' => 'Inspect At',
        ];
    }

    /**
     * 随机查询limit条IP数据
     * @param array $condition 条件
     * @param int $limit 查询条数
     * @param string $fields 查询字段
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function randGetIpList($condition = [], $limit = 50, $fields = '*'){
        $limit = empty($limit) ? 1 : $limit;

        $query = self::find()->where(['deleted' => 0]);

        if ($fields != '*'){
            $query->select($fields);
        }

        if (!empty($condition)){
            $query->andWhere($condition);
        }

        return $query->orderBy(new Expression('rand()'))->limit($limit)->all();
    }

    /**
     * PC端。获取代理列表，并更新数据状态
     * @param int $limit 获取条数
     * @return array
     */
    public static function pcGetProxyArr($limit){
        $listArr = self::find()
            ->select('id,ip,port')
            ->where([
                'is_use' => 0,
                'deleted' => 0
            ])
            ->orderBy(new Expression('rand()'))
            ->limit($limit)
            ->asArray()
            ->all();
        if (empty($listArr)){
            return [];
        }

        $proxyList = [];
        $idArr = [];
        foreach($listArr as $ipList){
            $idArr[] = $ipList['id'];
            $proxyList[] =  implode(':', $ipList);
        }

        // 更新为“已使用”
//        IpList::updateAll(['is_use' => 1], ['id' => $idArr]);
        return $proxyList;
    }

    /**
     * APP端。获取代理列表，并更新数据状态
     * @param int $limit 获取条数
     * @return array
     */
    public static function appGetProxyArr($limit){
//        $listArr = self::find()
//            ->select('id,ip,port')
//            ->where([
//                'is_app_pass' => 1,
////                'is_app_use' => 0
//            ])
//            ->orderBy(new Expression('rand()'))
//            ->limit($limit)
//            ->all();
        if($listArr  = Yii::$app->jdrqds->createCommand("SELECT id,ip,port FROM ip_list WHERE is_app_pass=1 order by rand() limit $limit")->queryAll()){}else{
        }
        if (empty($listArr)){
            return [];
        }
        $proxyList = [];
//        $idArr = [];
        foreach($listArr as $ipList){
            /* @var $ipList self*/
//            $idArr[] = $ipList['id'];
            $proxyList[] =  $ipList['ip'] . ':' . $ipList['port'];
        }

        // 更新为“已使用”
//        IpList::updateAll(['is_app_use' => 1], ['id' => $idArr]);
        return implode(',', $proxyList);
        //return $listArr;
    }

}
