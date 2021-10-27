<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "lecture_fund".
 *
 * @property int $id
 * @property int $lecture_id 讲师id
 * @property int $lecture_level 邀请层级
 * @property int $up_id  机构id
 * @property int $user_id 用户id
 * @property int $user_type 用户类型0 默认魔盒
 * @property int $level_log_id user_level_log中的id
 * @property string $balance 充值金额
 * @property string $amount 分成金额
 * @property string $division 分成比例
 * @property int $create_at 创建时间
 * @property int $update_at
 * @property int $deleted
 * @property int $version
 */
class LectureFund extends Base
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'lecture_fund';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lecture_id', 'lecture_level', 'user_id','up_id', 'user_type', 'level_log_id', 'create_at', 'update_at', 'deleted', 'version'], 'integer'],
            [['balance', 'amount'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lecture_id' => 'Lecture ID',
            'lecture_level' => 'Lecture Level',
            'up_id' => 'Up Id',
            'user_id' => 'User ID',
            'user_type' => 'User Type',
            'level_log_id' => 'Level Log ID',
            'balance' => 'Balance',
            'amount' => 'Amount',
            'division' => 'Division',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
            'deleted' => 'Deleted',
            'version' => 'Version',
        ];
    }

    /**
     * @param $level_log_id
     * @param $balance
     * @param $user_id
     * @return int|string
     * 用户充值讲师分成记录
     */
    public static function lectureFundSave($level_log_id,$balance,$user_id)
    {
        try {
            $user = User::findOne(['id'=>$user_id]);
            $lecture = AdminLecture::findOne(['id'=>$user->lecture_id]);
            $model = new LectureFund();
            $model->lecture_level = $user->lecture_level;
            $model->lecture_id = $user->lecture_id;
            $model->level_log_id = $level_log_id;
            $model->user_id = $user_id;
            $model->user_type = 0;   //默认0，后期添加其他类型
            $model->balance = $balance;

            $division = 0;
            $typeDiv = 0;  //机构下讲师计算后的分成比例
            //计算分成比例及金额
            if ($lecture->type==0 || $lecture->type==1){ //个人讲师及机构，直接计算分成
                if ($user->lecture_level==1){
                    $division = $lecture->direct_into;
                }else {
                    $division = $lecture->indirect_into;
                }
                $model->amount = round($balance * $division / 100,1);
            }else {   //type==2时为机构下讲师，要先计算讲师所属机构分成
                $lectureUp = AdminLecture::findOne(['id' => $lecture->up_id]);
                if ($user->lecture_level==1){
                    $division = $lectureUp->direct_into;
                }else {
                    $division = $lectureUp->indirect_into;
                }
                $model->lecture_id = $lectureUp->id;
                $typeDiv = round($lecture->direct_into * $division / 100,2); //机构下讲师只有直接分成，没有间接分成
            }
            $model->amount = round($balance * $division / 100);
            $model->division = $division;
            $model->save();

            if ($lecture->type==2) { //机构下讲师分成要先进行换算
                $query = new LectureFund();
                $query->lecture_level = $user->lecture_level;
                $query->lecture_id = $user->lecture_id;
                $query->level_log_id = $level_log_id;
                $query->balance = $balance;
                $query->user_id = $user_id;
                $query->user_type = 0;
                $query->up_id = $lecture->up_id;
                $query->amount = round($balance * $typeDiv / 100);
                $query->division = $typeDiv;
                $query->save();
            }
        }catch (\Exception $e){
            return $e->getMessage();
        }

        return 1;

    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

    public function getLecture()
    {
        return $this->hasOne(AdminLecture::className(),['id'=>'lecture_id']);
    }
}
