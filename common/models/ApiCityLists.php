<?php

namespace common\models;

use linslin\yii2\curl\Curl;
use Yii;

/**
 * This is the model class for table "api_city_lists".
 *
 * @property int $id
 * @property string $name 名称
 * @property int $province_id 省份编号 如果本条数据是省级别的  则为0
 * @property int $district_level 编号等级 2表示省级  3表示市级 4表示县级
 * @property int $is_used 0 不使用 1在使用
 * @property int $creat_time 创建时间
 * @property int $deleted
 */
class ApiCityLists extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'api_city_lists';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'province_id', 'district_level', 'creat_time'], 'required'],
            [['province_id', 'district_level', 'is_used', 'creat_time', 'deleted'], 'integer'],
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


    //获取城市一级信息
    public static function getCatFirst()
    {
        $list = [
            1=>'北京',
            2=>'上海',
            3=>'天津',
            4=>'重庆',
            5=>'河北',
            6=>'山西',
            7=>'河南',
            8=>'辽宁',
            9=>'吉林',
            10=>'黑龙江',
            11=>'内蒙古',
            12=>'江苏',
            13=>'山东',
            14=>'安徽',
            15=>'浙江',
            16=>'福建',
            17=>'湖北',
            18=>'湖南',
            19=>'广东',
            20=>'广西',
            21=>'江西',
            22=>'四川',
            23=>'海南',
            24=>'贵州',
            25=>'云南',
            26=>'西藏',
            27=>'陕西',
            28=>'甘肃',
            29=>'青海',
            30=>'宁夏',
            31=>'新疆',
            32=>'台湾',
            52993=>'港澳',
        ];
        $catFields=['id','name','province_id','district_level','is_used','creat_time','deleted'];
        $catValues = [];
        foreach($list as $k => $y){
            $catArray =[
                $k,
                $y,
                0,
                2,
                1,
                time(),
                0
            ];
            array_push($catValues,$catArray);
        }
        $res= Yii::$app->db->createCommand()->batchInsert(ApiCityLists::tableName(), $catFields, $catValues)->execute();
        echo "一级更新完成" . date('Y-m-d H:i',time()) . "\n";
        return $list;
    }


    //获取二级
    public static function getCatSecond($parent_id,$type='')
    {
        try{
            $catFields = ['id','name','province_id','district_level','is_used','creat_time','deleted'];
            $catValues = [];
            $url = 'https://fts.jd.com/area/get?fid='.$parent_id;
            $curl = new Curl();
            $collectString = $curl->get($url);

            if (strstr($collectString,"502 Bad Gateway")){
                echo "采集:".$parent_id."失败\n";
                return ['result'=>0,'msg'=>'采集超时,请稍后重新查询.'];
            }
            $result = json_decode($collectString,true);
            if (empty($result)){
                echo "采集:".$parent_id."失败\n";
                return ['result'=>0,'msg'=>'采集失败'];
            }
            $district_level = $type== 3 ?  3 : 4;
            foreach($result as $k => $y){
                $catArray =[
                    $y["id"],
                    $y["name"],
                    $parent_id,
                    $district_level,
                    1,
                    time(),
                    0
                ];
                array_push($catValues,$catArray);
            }
            Yii::$app->db->createCommand()->batchInsert(ApiCityLists::tableName(), $catFields, $catValues)->execute();
            echo "完成：".$parent_id.'--------' . date('Y-m-d H:i',time()) . "\n";
        }catch (\Exception $e){
            return ['result'=>0,'msg'=>$e->getMessage()];
        }
    }

    public static function Joint($city){
        $city_joint = [];
        $city = explode(',',$city);

        foreach($city as $k => $y){
            if($k == 1){
                if($y == 0){
                    $oneCity =  ApiCityLists::find()->where(['province_id'=>$city_joint[0]])->orderBy('id asc')->one();
                    $city_joint[] = $oneCity['id'];

                    $oneCity =  ApiCityLists::find()->where(['province_id'=>$city_joint[1]])->orderBy('id asc')->one();
                    $city_joint[2] = $oneCity['id'];
                }else{
                    $city_joint[] = (int)$y;
                }
            }elseif($k == 2){
                $oneCity =  ApiCityLists::find()->where(['province_id'=>$city_joint[1]])->orderBy('id asc')->one();
                $city_joint[2] = $oneCity['id'];
            }elseif($k == 0){
                if((int)$y == 0){
                    $y = 1;
                }
                $city_joint[] = (int)$y;
            }
        }
        $city = implode(',',$city_joint);
        return $city;
    }
}
