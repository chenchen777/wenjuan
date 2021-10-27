<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_level".
 *
 * @property int $id
 * @property string $name 等级名称
 * @property string $price 价格
 * @property string $promotion_price 优惠价格
 * @property int $validity_month 有效期 单位：月
 * @property string $subhead 副标题，用户前天显示优惠信息等文案
 * @property string $memo 管理后台备注
 * @property int $deleted
 * @property int $version
 * @property int $create_at
 * @property int $update_at
 * @property int $update_number 一键更新次数
 */
class UserLevel extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_level';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'validity_month'], 'required'],
            [['price', 'promotion_price'], 'number'],
            [['validity_month', 'deleted', 'version', 'create_at', 'update_at'], 'integer'],
            [['name'], 'string', 'max' => 30],
            [['subhead', 'memo'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '用户等级名称',
            'price' => 'Price',
            'promotion_price' => 'Promotion Price',
            'validity_month' => 'Validity Month',
            'subhead' => 'Subhead',
            'memo' => 'Memo',
            'deleted' => 'Deleted',
            'version' => 'Version',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    public function getService(){
        return $this->hasMany(UserLevelService::className(),['user_level_id'=>'id']);
    }


}
