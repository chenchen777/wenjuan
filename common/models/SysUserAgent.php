<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "sys_user_agent".
 *
 * @property int $id
 * @property int $user_id
 * @property string $sku
 * @property int $type 评价类型 0全部 1差评 2中评 3好评 4晒图
 * @property int $sort 5 推荐排序 6时间排序
 * @property int $only 1只看当前商品评价 2查看全部
 * @property int $page_start 起始页码
 * @property int $page_end 结束页码
 * @property int $create_at
 * @property int $update_at
 * @property int $version
 * @property int $deleted
 */
class SysUserAgent extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sys_user_agent';
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
        ];
    }

    /**
     * 随机获取n条数据
     * @param int $limit
     * @param array $condition
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function randGet($limit = 1, $condition = []){
        return self::find()
            ->where($condition)
            ->orderBy(new Expression('rand()'))
            ->limit($limit)
            ->all();
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
            ->column();
        return implode('*', $Arr);
    }
}
