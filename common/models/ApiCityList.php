<?php

namespace common\models;

use common\models\Base;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "api_city_list".
 *
 * @property int $id
 * @property string $name 名称
 * @property int $province_id 省份编号 如果本条数据是省级别的  则为0
 * @property int $district_level 编号等级 2表示省级  3表示市级
 * @property int $is_used 0 不使用 1在使用
 * @property int $creat_time 创建时间
 * @property int $deleted
 */
class ApiCityList extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'api_city_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'province_id', 'district_level', 'creat_time'], 'required'],
            [['province_id', 'district_level', 'is_used', 'creat_time','deleted'], 'integer'],
            [['name'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'province_id' => 'Province ID',
            'district_level' => 'District Level',
            'is_used' => 'Is Used',
            'creat_time' => 'Creat Time',
            'deleted' => 'Deleted',
        ];
    }

    public static function getCityIp()
    {
        try{
            $citys = ApiCityList::find()->where(['deleted' => 0,'is_used'=>1,'province_id' => 0])->select('id,id')->asArray()->all();
            $citys = ArrayHelper::map($citys, 'id', 'id');
            $citys = array_merge($citys);
            $index = rand(0,count($citys) - 1);
            return $citys[$index];

        }catch (\Exception $e){
            return rand(0,21);
        }


    }
}
